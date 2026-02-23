<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpStatus;
//use App\Models\Tenant\Imports\
//Servicios
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class Orders extends Component
{
    public function getStatusProperty()
    {
        $this->ensureTenantConnection();
        return ImpStatus::all();
    }

    public function render()
    {
        $status = $this->status;
        return view('livewire.tenant.imports.orders', [
            'status' => $status
        ])->layout('layouts.app', ['header' => 'Gestión de Ordenes']);
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
