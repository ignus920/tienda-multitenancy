<?php

namespace App\Livewire\Tenant\TransferRequests;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Transfers\TransferRequest;
use App\Models\Auth\User;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransferRequests extends Component
{
    // Propiedades para la tabla Items
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $selectedItems = [];
    public $quantities = [];
    public $showAddItem = false;
    //public $showAddItem = false;

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function agregarItem($itemId)
    {
        $this->ensureTenantConnection();
        $storeId = $this->getStore(); // Obtener el ID de la tienda

        $item = Items::with(['invItemsStore' => function ($query) use ($storeId) {
            $query->where('storeId', $storeId);
        }])->find($itemId);

        if ($item) {
            $found = false;
            foreach ($this->selectedItems as $key => $selectedItem) {
                if ($selectedItem['id'] == $itemId) {
                    $this->quantities[$itemId]++;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->selectedItems[] = $item->toArray();
                $this->quantities[$itemId] = 1;
            }

            $this->showAddItem = true;
        }
    }

    public function removeItem($itemId)
    {
        $this->selectedItems = collect($this->selectedItems)->filter(function ($item) use ($itemId) {
            return $item['id'] != $itemId;
        })->values()->toArray();

        unset($this->quantities[$itemId]);

        if (empty($this->selectedItems)) {
            $this->showAddItem = false;
        }
    }

    private function getStore()
    {
        $user = User::with('contact')->find(Auth::id());
        $storeId = null;
        $warehouse = null;
        if ($user && $user->contact) {
            $storeId = $user->contact->store;
            $warehouse = $user->contact->warehouseId;
        }
        //Log::info('Obteniendo tienda: ' . $storeId . '- Sucursal:' . $warehouse);
        return [
            'storeId' => $storeId,
            'warehouseId' => $warehouse
        ];
    }


    public function getItems()
    {
        $store = $this->getStore(); // Obtener el storeId y warehouseId
        $storeId = $store['storeId'];
        $warehouseId = $store['warehouseId'];

        $this->ensureTenantConnection();
        return Items::query()
            ->with(['invItemsStore' => function ($query) use ($storeId) {
                $query->where('storeId', $storeId);
            }])
            ->whereHas('invItemsStore', function ($query) use ($storeId) {
                $query->where('storeId', $storeId);
            })
            ->withSum([
                'invItemsStore as total_stock_by_warehouse' => function ($query) use ($warehouseId) {
                    $query->whereHas('store', function ($q) use ($warehouseId) {
                        $q->where('warehouseId', $warehouseId);
                    });
                }
            ], 'stock_items_store')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.tenant.transfer-requests.transfer-requests', [
            'items' => $this->getItems()
        ])->layout('layouts.app', ['header' => 'Solicitud de Transferencias']);
    }

    // public function agregarItem($itemId)
    // {
    //     Log::info('👓 Id del Item: ' . $itemId);
    //     $this->showAddItem = true;
    // }

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
