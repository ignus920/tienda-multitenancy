<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Tenant\Projects\ManageProjects;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Events\Tenant\Projects\NewProjectNotification;

class ProjectParticipants extends Component
{
    public $projectId;
    public $selectedUserId = '';

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    private function checkNotClosed()
    {
        $project = Project::find($this->projectId);
        if ($project && in_array($project->status, ['terminado', 'cerrado_entregado'])) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El proyecto está finalizado. No se permiten más modificaciones.']);
            return true;
        }
        return false;
    }

    /**
     * Igual que checkNotClosed(), pero solo bloquea en "cerrado_entregado".
     * Mientras el proyecto esté "terminado" (a la espera de que el
     * solicitante lo verifique y finalice), el creador todavía puede
     * agregar participantes — por ejemplo, para sumar a alguien que deba
     * revisar el trabajo antes del cierre definitivo.
     */
    private function checkFullyClosed()
    {
        $project = Project::find($this->projectId);
        if ($project && $project->status === 'cerrado_entregado') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El proyecto está finalizado. No se permiten más modificaciones.']);
            return true;
        }
        return false;
    }

    private function isProjectCreator(): bool
    {
        $project = Project::find($this->projectId);
        return $project && (int) $project->created_by === (int) Auth::id();
    }

    /**
     * ¿Puede este usuario agregar/quitar participantes de este proyecto?
     * El creador puede gestionar participantes, SALVO que él mismo sea
     * Vendedor POS — a ese perfil los participantes se le asignan
     * automáticamente al crear el proyecto (ver ManageProjects::createProject()),
     * no los edita manualmente. Super Administrador/Administrador siempre
     * conservan el control para poder corregir si hace falta.
     */
    private function canManageParticipants(): bool
    {
        if (in_array((int) Auth::user()?->profile_id, Project::FULL_ACCESS_PROFILES, true)) {
            return true;
        }

        return $this->isProjectCreator()
            && (int) Auth::user()?->profile_id !== ManageProjects::SALESPERSON_PROFILE_ID;
    }

    private function checkCanManageParticipants(): bool
    {
        if (!$this->canManageParticipants()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para gestionar los participantes de este proyecto']);
            return false;
        }
        return true;
    }

    public function addParticipant()
    {
        $this->ensureTenantConnection();
        if ($this->checkFullyClosed()) return;
        if (!$this->checkCanManageParticipants()) return;

        if (!$this->selectedUserId) {
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user) {
            return;
        }

        $participant = ProjectParticipant::firstOrCreate(
            ['project_id' => $this->projectId, 'user_id' => $this->selectedUserId],
            ['role' => $user->profile->name ?? 'Sin área']
        );

        if ($participant->wasRecentlyCreated) {
            $notification = ProjectNotification::create([
                'user_id' => $this->selectedUserId,
                'project_id' => $this->projectId,
                'message_id' => null,
                'sender_id' => Auth::id(),
                'type' => 'nuevo_participante', // Cambiado para que se marque como leído al hacer clic
            ]);

            $project = Project::find($this->projectId);
            broadcast(new NewProjectNotification(
                $this->selectedUserId,
                $this->projectId,
                $project->title ?? 'Proyecto',
                Auth::user()->name,
                'Te han agregado como participante de este proyecto.',
                'nuevo_participante',
                $notification->id
            ));
        }

        $this->selectedUserId = '';
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Participante agregado']);
    }

    public function removeParticipant($participantId)
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;
        if (!$this->checkCanManageParticipants()) return;

        $participant = ProjectParticipant::findOrFail($participantId);
        $project = Project::findOrFail($this->projectId);

        if ((int) $participant->user_id === (int) $project->created_by) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No puedes quitar al creador del proyecto']);
            return;
        }

        if (ProjectParticipant::where('project_id', $this->projectId)->count() <= 1) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El proyecto debe tener al menos un participante']);
            return;
        }

        $userIdToRemove = $participant->user_id;
        $participant->delete();
        
        $project = Project::find($this->projectId);
        
        // Broadcast silencioso para actualizar la grilla del usuario eliminado
        broadcast(new NewProjectNotification(
            $userIdToRemove,
            $this->projectId,
            $project->title ?? 'Proyecto',
            Auth::user()->name,
            '', // Sin mensaje
            'actualizacion_silenciosa',
            0
        ));

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Participante eliminado']);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $participants = ProjectParticipant::where('project_id', $this->projectId)
            ->with('user.profile')
            ->orderBy('created_at', 'asc')
            ->get();

        $participantIds = $participants->pluck('user_id')->toArray();
        $sessionTenant = session('tenant_id');

        $availableUsers = User::whereHas('tenants', function ($q) use ($sessionTenant) {
                $q->where('tenants.id', $sessionTenant);
            })
            ->whereNotIn('id', $participantIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;
        $isFullyClosed = $project ? $project->status === 'cerrado_entregado' : false;
        $createdBySalesperson = $project && $project->creator
            && (int) $project->creator->profile_id === ManageProjects::SALESPERSON_PROFILE_ID;

        return view('livewire.tenant.projects.project-participants', [
            'participants' => $participants,
            'availableUsers' => $availableUsers,
            'isClosed' => $isClosed,
            'isFullyClosed' => $isFullyClosed,
            'canManageParticipants' => $this->canManageParticipants(),
            'createdBySalesperson' => $createdBySalesperson,
        ]);
    }
}
