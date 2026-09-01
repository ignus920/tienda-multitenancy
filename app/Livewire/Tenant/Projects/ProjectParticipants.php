<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

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

    public function addParticipant()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;

        if (!$this->selectedUserId) {
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user) {
            return;
        }

        ProjectParticipant::firstOrCreate(
            ['project_id' => $this->projectId, 'user_id' => $this->selectedUserId],
            ['role' => $user->profile->name ?? 'Sin área']
        );

        $this->selectedUserId = '';
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Participante agregado']);
    }

    public function removeParticipant($participantId)
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;

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

        $participant->delete();
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

        return view('livewire.tenant.projects.project-participants', [
            'participants' => $participants,
            'availableUsers' => $availableUsers,
            'isClosed' => $isClosed
        ]);
    }
}
