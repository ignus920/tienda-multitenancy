<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\QuarantineMovement;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ProductQuarantineModal extends Component
{
    public $isOpen = false;
    public $productId = null;
    public $productName = null;
    public $productCode = null;

    // Campos del Formulario
    public $quantity;
    public $justification;
    public $action_type = 'add'; // 'add' o 'release'
    public $activeTab = 'quarantine'; // 'quarantine' o 'showroom'

    // Totales Informativos
    public $stock_bodega = 0;
    public $reserved_stock = 0;
    public $quarantine_stock = 0;
    public $showroom_stock = 0;
    public $stock_disponible = 0;

    // Propiedades de Solo Lectura
    public $isReadOnly = false;
    public $movements = [];

    protected $listeners = ['openQuarantineModal' => 'open'];

    public function boot()
    {
        if (!tenancy()->initialized) {
            $this->ensureTenantConnection();
        }
    }

    private function ensureTenantConnection()
    {
        $tenantManager = app(TenantManager::class);
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openQuarantineModal')]
    public function open($productId = null)
    {
        $this->productId = $productId;
        $this->resetValidation();
        $this->reset(['quantity', 'justification', 'action_type', 'isReadOnly', 'movements', 'activeTab']);
        $this->action_type = 'add';

        // Si no puede gestionar, se abre en modo solo lectura
        $this->isReadOnly = !$this->canUserManage();

        if ($productId) {
            $this->ensureTenantConnection();
            $product = Items::find($productId);
            if ($product) {
                $this->productName = $product->name;
                $this->productCode = $product->internal_code ?? $product->sku;
                
                // Cargar stocks actuales
                $this->stock_bodega = $product->stock_bodega;
                $this->reserved_stock = $product->reserved_stock;
                $this->quarantine_stock = $product->quarantine_stock;
                $this->showroom_stock = $product->showroom_stock;
                $this->stock_disponible = $product->stock_disponible_venta;
            }
        }

        $this->isOpen = true;
    }

    private function canUserManage(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if (\App\Helpers\PermissionHelper::isSuperAdmin()) {
            return true;
        }

        if ((int)$user->profile_id === 2) {
            return true;
        }

        $email = strtolower($user->email ?? '');
        $name = strtolower($user->name ?? '');
        if (str_contains($email, 'juanita') || str_contains($name, 'juanita')) return true;
        if (str_contains($email, 'camilo') || str_contains($name, 'camilo')) return true;

        $profileName = strtolower($user->profile?->name ?? '');
        if (str_contains($profileName, 'gerencia') || str_contains($profileName, 'director')) return true;

        return false;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'activeTab' => 'required|in:quarantine,showroom',
            'action_type' => 'required|in:add,release',
            'quantity' => 'required|integer|min:1',
            'justification' => 'required|string|min:5',
        ], [
            'activeTab.required' => 'La pestaña seleccionada es obligatoria.',
            'action_type.required' => 'El tipo de movimiento es obligatorio.',
            'quantity.required' => 'La cantidad de unidades es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser de al menos 1 unidad.',
            'justification.required' => 'La justificación del movimiento es obligatoria.',
            'justification.min' => 'La justificación debe tener al menos 5 caracteres.',
        ]);

        $userStoreId = session('warehouse_id') ?? 1;
        $qtyToRecord = (int) $this->quantity;

        if ($this->activeTab === 'quarantine') {
            if ($this->action_type === 'add') {
                if ($qtyToRecord > $this->stock_disponible) {
                    $this->addError('quantity', "Stock disponible insuficiente. Disponible para venta: {$this->stock_disponible} unidades.");
                    return;
                }
            } else {
                if ($qtyToRecord > $this->quarantine_stock) {
                    $this->addError('quantity', "No puedes liberar más unidades de las que hay actualmente en cuarentena ({$this->quarantine_stock} unidades).");
                    return;
                }
                $qtyToRecord = -$qtyToRecord;
            }
        } else {
            // Vitrina / Showroom
            if ($this->action_type === 'add') {
                if ($qtyToRecord > $this->stock_disponible) {
                    $this->addError('quantity', "Stock disponible insuficiente. Disponible para venta: {$this->stock_disponible} unidades.");
                    return;
                }
            } else {
                if ($qtyToRecord > $this->showroom_stock) {
                    $this->addError('quantity', "No puedes liberar más unidades de las que hay actualmente en vitrina ({$this->showroom_stock} unidades).");
                    return;
                }
                $qtyToRecord = -$qtyToRecord;
            }
        }

        try {
            if ($this->activeTab === 'quarantine') {
                QuarantineMovement::create([
                    'item_id' => $this->productId,
                    'store_id' => $userStoreId,
                    'quantity' => $qtyToRecord,
                    'justification' => $this->justification,
                    'user_id' => auth()->id() ?? 1,
                ]);

                $msg = $this->action_type === 'add' 
                    ? 'Producto enviado a cuarentena exitosamente.' 
                    : 'Producto retornado de cuarentena al inventario general.';
            } else {
                ShowroomMovement::create([
                    'item_id' => $this->productId,
                    'store_id' => $userStoreId,
                    'quantity' => $qtyToRecord,
                    'justification' => $this->justification,
                    'user_id' => auth()->id() ?? 1,
                ]);

                $msg = $this->action_type === 'add' 
                    ? 'Producto enviado a vitrina exitosamente.' 
                    : 'Producto retornado de vitrina al inventario general.';
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $msg
            ]);

            $this->dispatch('refreshProductList');
            $this->close();

        } catch (\Exception $e) {
            Log::error("Error guardando movimiento especial: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar el movimiento: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.components.product-quarantine-modal');
    }
}
