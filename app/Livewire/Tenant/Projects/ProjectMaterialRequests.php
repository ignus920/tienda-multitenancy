<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMaterial;
use App\Models\Tenant\Projects\ProjectMaterialRequest;
use App\Models\Tenant\Projects\ProjectMaterialRequestItem;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

class ProjectMaterialRequests extends Component
{
    public $projectId;

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
     * Solo perfil Laboratorio (son quienes saben qué tienen de sobra antes
     * de pedirle a Bodega) o Super Administrador/Administrador.
     */
    private function canManage(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (in_array((int) $user->profile_id, Project::FULL_ACCESS_PROFILES, true)) {
            return true;
        }

        return strcasecmp($user->profile->name ?? '', 'Laboratorio') === 0;
    }

    private function checkCanManage(): bool
    {
        if (!$this->canManage()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para gestionar solicitudes de materiales.']);
            return false;
        }
        return true;
    }

    /**
     * Crea una nueva solicitud a partir de los materiales activos de
     * origen ERP, redondeados hacia arriba. Si ya existe una solicitud sin
     * convertir en salida (pendiente/revisada), no crea otra.
     */
    public function generateMaterialRequest()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        $existing = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->whereIn('status', ['pendiente', 'revisada'])
            ->exists();

        if ($existing) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ya hay una solicitud de materiales en curso para este proyecto.']);
            return;
        }

        $erpMaterials = ProjectMaterial::where('project_id', $this->projectId)
            ->where('origin', 'erp')
            ->where('is_active', true)
            ->get();

        if ($erpMaterials->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No hay materiales del ERP activos para generar la solicitud.']);
            return;
        }

        $request = ProjectMaterialRequest::create([
            'project_id' => $this->projectId,
            'status' => 'pendiente',
            'requested_by' => Auth::id(),
        ]);

        foreach ($erpMaterials as $material) {
            ProjectMaterialRequestItem::create([
                'request_id' => $request->id,
                'project_material_id' => $material->id,
                'item_id' => $material->item_id,
                'description' => $material->description,
                'quantity_requested' => (int) ceil($material->quantity),
                'is_removed' => false,
            ]);
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud de materiales generada']);
    }

    public function updateRequestItemQuantity($itemId, $quantity)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $item = ProjectMaterialRequestItem::whereHas('request', function ($q) {
            $q->where('project_id', $this->projectId)->where('status', 'pendiente');
        })->find($itemId);

        if (!$item) return;

        $quantity = max(1, (int) $quantity);
        $item->update(['quantity_requested' => $quantity]);
    }

    public function toggleRequestItemRemoved($itemId)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $item = ProjectMaterialRequestItem::whereHas('request', function ($q) {
            $q->where('project_id', $this->projectId)->where('status', 'pendiente');
        })->find($itemId);

        if (!$item) return;

        $item->update(['is_removed' => !$item->is_removed]);
    }

    /**
     * Laboratorio ya terminó de ajustar la solicitud — queda visible para
     * que Importaciones la revise y genere la Salida de Mercancía.
     */
    public function markRequestReviewed()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if (!$request) return;

        if ($request->items()->where('is_removed', false)->doesntExist()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La solicitud no puede quedar vacía — debe tener al menos un producto sin quitar.']);
            return;
        }

        $request->update(['status' => 'revisada']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud enviada a Importaciones']);
    }

    public function cancelMaterialRequest()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if ($request) {
            $request->items()->delete();
            $request->delete();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud cancelada']);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $canManage = $this->canManage();

        if (!$canManage) {
            return view('livewire.tenant.projects.project-material-requests', [
                'canManage' => false,
                'materialRequest' => null,
                'hasActiveErpMaterials' => false,
                'isClosed' => false,
            ]);
        }

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;

        $materialRequest = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->whereIn('status', ['pendiente', 'revisada'])
            ->with('items')
            ->latest('id')
            ->first();

        $hasActiveErpMaterials = ProjectMaterial::where('project_id', $this->projectId)
            ->where('origin', 'erp')
            ->where('is_active', true)
            ->exists();

        return view('livewire.tenant.projects.project-material-requests', [
            'canManage' => true,
            'materialRequest' => $materialRequest,
            'hasActiveErpMaterials' => $hasActiveErpMaterials,
            'isClosed' => $isClosed,
        ]);
    }
}
