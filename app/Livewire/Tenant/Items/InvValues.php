<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
//Modelos
use App\Models\Tenant\Items\InvValues as InvValuesModel;
use App\Models\Auth\Tenant;
//Servicios
use App\Services\Tenant\TenantManager;

class InvValues extends Component
{
    public $ItemId;
    
    public function getValuesItems(){
        return InvValues::where('itemId', $this->ItemId)->get();
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $values = $this->getValuesItems();
        return view('livewire.tenant.items.inv-values', [
            'values' => $values
        ]);
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
