<?php

namespace App\Livewire\Tenant\TransferRequests;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Transfers\InvTransferRequest;
use App\Models\Tenant\Transfers\InvDetailTransferRequests;
use App\Models\Auth\User;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransferRequests extends Component
{
    use WithPagination;

    // Propiedades para la tabla Items
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $selectedItems = [];
    public $quantities = [];
    public $showAddItem = false;
    public $showConfirm = false;
    public $observations = '';


    public $selectedItemsPerPage = 5; // User requested 5 items
    #[Url(keep: true, as: 'selectedItemsPage')]
    public $selectedItemsPage = 1; // Para paginación personalizada de items seleccionados


    //#[Computed]
    public function getPaginatedSelectedItemsProperty()
    {
        $page = $this->selectedItemsPage;
        $items = collect($this->selectedItems);
        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $this->selectedItemsPerPage),
            $items->count(),
            $this->selectedItemsPerPage,
            $page,
            ['pageName' => 'selectedItemsPage']
        );
        return $paginated;
    }

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
        $store = $this->getStore(); // Obtener el storeId y warehouseId
        $storeId = $store['storeId'];
        $warehouseId = $store['warehouseId'];
        $this->ensureTenantConnection();

        if (!isset($this->quantities[$itemId]) || $this->quantities[$itemId] < 1) {
            session()->flash('warning', 'La cantidad debe ser al menos 1.');
            return;
        }
        // Resetear la página de la paginación personalizada al agregar
        $this->reset('selectedItemsPage');

        $itemToCheckStock = Items::query()
            ->where('id', $itemId)
            ->withSum([
                'invItemsStore as total_stock_by_warehouse' => function ($query) use ($warehouseId, $storeId) {
                    $query->whereHas('store', function ($q) use ($warehouseId, $storeId) {
                        $q->where('warehouseId', $warehouseId)
                            ->whereNot('storeId', $storeId); // Excluir la tienda actual
                    });
                }
            ], 'stock_items_store')
            ->first(); // Usar first() para obtener el modelo con el sumado
        Log::info('🔬 Item a verificar: ' . $itemToCheckStock);

        $availableStock = $itemToCheckStock->total_stock_by_warehouse / 2;
        // Aquí puedes usar $itemToCheckStock->total_stock_by_warehouse para tus validaciones
        // Por ejemplo:
        if ($itemToCheckStock && $this->quantities[$itemId] > $availableStock) {
            session()->flash('warning', 'La cantidad solicitada excede el 50% del stock disponible en el origen para transferir.');
            return;
        }

        $store = $this->getStore();
        $storeId = $store['storeId'];

        $item = Items::with(['invItemsStore' => function ($query) use ($storeId) {
            $query->where('storeId', $storeId);
        }])->find($itemId);

        if ($item) {
            // Check if item already exists in selectedItems
            $foundIndex = -1;
            foreach ($this->selectedItems as $key => $selectedItem) {
                if ($selectedItem['id'] == $itemId) {
                    $foundIndex = $key;
                    break;
                }
            }

            if ($foundIndex === -1) {
                // Item not found, add it
                $this->selectedItems[] = $item->toArray();
                // If item is new, ensure its quantity is set from the input
                if (!isset($this->quantities[$itemId])) {
                    $this->quantities[$itemId] = 1; // Default to 1 if somehow not set by wire:model
                }
            }
            // Quantity is already updated by wire:model.live="quantities.{{ $item->id }}"
            // No explicit assignment needed here unless there's a default/fallback
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
        // Resetear la página de la paginación personalizada al eliminar
        $this->reset('selectedItemsPage');

        // Adjust selectedItemsPage if necessary
        if ($this->selectedItemsPage > $this->paginatedSelectedItems->lastPage() && $this->paginatedSelectedItems->lastPage() > 0) {
            $this->selectedItemsPage = $this->paginatedSelectedItems->lastPage();
        } elseif ($this->paginatedSelectedItems->count() === 0) {
            $this->selectedItemsPage = 1;
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
        Log::info('Obteniendo tienda: ' . $storeId . '- Sucursal:' . $warehouse);
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
                'invItemsStore as total_stock_by_warehouse' => function ($query) use ($warehouseId, $storeId) {
                    $query->whereHas('store', function ($q) use ($warehouseId, $storeId) {
                        $q->where('warehouseId', $warehouseId)
                            ->whereNot('storeId', $storeId); // Excluir la tienda actual
                    });
                }
            ], 'stock_items_store')
            ->where('status', 1)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function createRequest()
    {
        DB::beginTransaction();
        try {
            $store = $this->getStore();
            $storeId = $store['storeId'];
            $this->ensureTenantConnection();

            $newTransferRequest = InvTransferRequest::create([
                'status' => 'REGISTRADO',
                'date' => Carbon::now(),
                'warehouseId' => $storeId,
                'observations' => $this->observations,
            ]);

            $this->saveDetailsTransferRequest($newTransferRequest->id);

            DB::commit();

            session()->flash('message', 'La solicitud de transferencia ha sido creada con éxito.');
            Log::info('Solicitud de Transferencia Creada: ' . $newTransferRequest->id);

            // Reset state after successful creation
            $this->reset(['selectedItems', 'quantities', 'observations', 'showAddItem']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear la solicitud de transferencia: " . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al crear la solicitud de transferencia. Por favor, intente de nuevo.');
        }
        $this->showConfirm = false;
    }

    public function saveDetailsTransferRequest($idTransferRequest)
    {
        try {
            foreach ($this->selectedItems as $item) {
                if (!isset($this->quantities[$item['id']]) || $this->quantities[$item['id']] <= 0) {
                    throw new \Exception("La cantidad para el item {$item['name']} no es válida.");
                }

                InvDetailTransferRequests::create([
                    'transferRequestId' => $idTransferRequest,
                    'itemId' => $item['id'],
                    'quantity' => $this->quantities[$item['id']],
                    'quantitySend' => 0,
                ]);
            }
        } catch (\Exception $e) {
            // Log the specific error and re-throw it to be caught by the transaction block
            Log::error('Error al registrar el detalle de la transferencia: ' . $e->getMessage());
            throw $e;
        }
    }

    public function clearSelection()
    {
        $this->selectedItems = [];
        $this->quantities = [];
        $this->observations = '';
        $this->showAddItem = false;
        $this->reset('selectedItemsPage'); // Resetear la página de la paginación personalizada
    }

    public function cancel()
    {
        $this->showConfirm = false;
    }

    public function render()
    {
        return view('livewire.tenant.transfer-requests.transfer-requests', [
            'items' => $this->getItems(),
            'paginatedSelectedItems' => $this->paginatedSelectedItems,
        ])->layout('layouts.app', ['header' => 'Solicitud de Transferencias']);
    }

    public function confirmCreateRequest()
    {
        $this->showConfirm = true;
        // dd([
        //     'selectedItems' => $this->selectedItems,
        //     'quantities' => $this->quantities,
        //     'observations' => $this->observations,
        //     'showConfirm' => $this->showConfirm
        // ]);
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
