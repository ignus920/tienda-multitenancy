<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\ProjectFile;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ProjectFiles extends Component
{
    public $projectId;
    public $typeFilter = 'todos'; // todos, imagenes, documentos

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

    public function render()
    {
        $this->ensureTenantConnection();

        $query = ProjectFile::where('project_id', $this->projectId)
            ->with(['user', 'message'])
            ->orderBy('created_at', 'desc');

        if ($this->typeFilter === 'imagenes') {
            $query->whereIn('file_type', ['jpg', 'jpeg', 'png', 'webp']);
        } elseif ($this->typeFilter === 'documentos') {
            $query->whereNotIn('file_type', ['jpg', 'jpeg', 'png', 'webp']);
        }

        return view('livewire.tenant.projects.project-files', [
            'files' => $query->get()
        ]);
    }
}
