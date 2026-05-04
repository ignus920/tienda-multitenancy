<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Inventory\InventoryConfirmation;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryConfirmationModal extends Component
{
    use WithPagination;

    // Propiedades del Modal
    public $isOpen = false;
    public $productId = null;
    // Eliminamos public $product para evitar errores de hidratación
    
    // Campos del Formulario
    public $requested_quantity;
    public $observations;

    // Filtros del Historial
    public $perPage = 5;

    public function boot()
    {
        if (!tenancy()->initialized) {
            $this->ensureTenantConnection();
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = \App\Models\Auth\Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openConfirmationModal')]
    public function open($productId = null)
    {
        $this->productId = $productId;
        
        $this->resetValidation();
        $this->reset(['requested_quantity', 'observations']);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'requested_quantity' => 'required|numeric|min:1',
            'observations' => 'nullable|string|max:500',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $product = Items::with('invItemsStore')->find($this->productId);
            $stock = 0;
            if ($product && $product->invItemsStore) {
                $stock = $product->invItemsStore->sum('stock_items_store');
            }

            InventoryConfirmation::create([
                'item_id' => $this->productId,
                'requested_quantity' => $this->requested_quantity,
                'requester_id' => auth()->id(),
                'status' => 1, // PENDIENTE
                'requested_at' => now(),
                'system_stock' => $stock,
                'observations' => $this->observations,
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Solicitud de confirmación enviada correctamente'
            ]);

            $this->reset(['requested_quantity', 'observations']);
            $this->close();
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al guardar confirmación: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $confirmations = collect();
        $product = null;

        if ($this->productId) {
            $this->ensureTenantConnection();
            
            $product = Items::with('invItemsStore')->find($this->productId);
            
            $confirmations = InventoryConfirmation::with(['requester', 'confirmer'])
                ->where('item_id', $this->productId)
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.tenant.components.inventory-confirmation-modal', [
            'confirmations' => $confirmations,
            'product' => $product
        ]);
    }
}
