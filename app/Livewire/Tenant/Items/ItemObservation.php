<?php

namespace App\Livewire\Tenant\Items;

use App\Models\Tenant\Items\ItemObservation as ObservationModel;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Livewire\Component;
use Livewire\Attributes\On;

class ItemObservation extends Component
{
    public $itemId;
    public $productName;
    public $productCode;
    public $observations;
    public $technical_specifications;
    public $commercial_observations;
    public $status = 1;
    public $isOpen = false;

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

    public function mount($itemId = null)
    {
        if ($itemId) {
            $this->loadData($itemId);
        }
    }

    #[On('openObservationsModal')]
    public function loadData($itemId)
    {
        $this->ensureTenantConnection();
        $this->itemId = $itemId;
        
        // Diagnóstico de base de datos
        \Log::info("ItemObservation::loadData - DB Tenant: " . config('database.connections.tenant.database'));
        \Log::info("ItemObservation::loadData - itemId: " . $this->itemId);

        // Cargar info del producto forzando conexión tenant
        $product = \App\Models\Tenant\Items\Items::on('tenant')->find($this->itemId);
        if ($product) {
            $this->productName = $product->name;
            $this->productCode = $product->sku ?: $product->internal_code;
        }

        $observation = ObservationModel::on('tenant')->where('item_id', $this->itemId)->first();

        if ($observation) {
            $this->observations = $observation->observations;
            $this->technical_specifications = $observation->technical_specifications;
            $this->commercial_observations = $observation->commercial_observations;
            $this->status = $observation->status;
        } else {
            $this->reset(['observations', 'technical_specifications', 'commercial_observations']);
            $this->status = 1;
        }

        $this->isOpen = true;
    }

    #[On('closeObservationsModal')]
    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['itemId', 'productName', 'productCode', 'observations', 'technical_specifications', 'commercial_observations']);
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'itemId' => 'required|integer',
            'status' => 'required|integer',
        ]);

        // Forzar conexión en guardado
        ObservationModel::on('tenant')->updateOrCreate(
            ['item_id' => $this->itemId],
            [
                'observations' => $this->observations,
                'technical_specifications' => $this->technical_specifications,
                'commercial_observations' => $this->commercial_observations,
                'status' => $this->status,
            ]
        );

        $this->dispatch('show-toast', [
            'type' => 'success', 
            'message' => 'Observaciones guardadas correctamente'
        ]);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        return view('livewire.tenant.components.item-observation');
    }
}
