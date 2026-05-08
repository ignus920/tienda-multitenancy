<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemAccesorios;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ItemAccessoriesModal extends Component
{
    public $isOpen = false;
    public $itemId = null;
    public $itemName = '';
    public $assignedAccesorios = [];

    /**
     * Asegura la conexión con el tenant
     */
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
    }

    #[On('openAccessoriesModal')]
    public function open($itemId)
    {
        $this->ensureTenantConnection();
        $this->itemId = $itemId;
        
        $item = Items::find($itemId);
        if (!$item) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Producto no encontrado.']);
            return;
        }

        $this->itemName = $item->name;
        $this->loadAssigned();
        $this->isOpen = true;
    }

    public function loadAssigned()
    {
        try {
            $this->assignedAccesorios = InvItemAccesorios::with('insumo')
                ->where('item', $this->itemId)
                ->orderBy('id')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ItemAccessoriesModal - Error cargando accesorios: ' . $e->getMessage());
            $this->assignedAccesorios = [];
        }
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.tenant.components.item-accessories-modal');
    }
}
