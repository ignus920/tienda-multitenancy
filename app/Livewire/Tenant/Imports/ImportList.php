<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Imports\ImpLabels;
use App\Models\Tenant\Imports\InvUnconfirmedQty;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $storeId = 1; // Warehouse/Store ID configurable
    
    // Array para almacenar las cantidades seleccionadas por item
    public $selectedQuantities = [];
  
    // Array para almacenar los labels
    public $allLabels = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

     protected $listeners = [
        'label-selected' => 'onLabelSelected',
    ];

     public function onLabelSelected($labelId, $labelName)
    {
        $this->selectedLabel = [
            'id' => $labelId,
            'name' => $labelName
        ];
        Log::info("Label seleccionado: ID={$labelId}, Name={$labelName}");
    }

    public $selectedLabel = [
         'id' => '',
         'name' => ''
    ];

    public function updatingSearch()
    {
        $this->resetPage();
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

    public function getItemsProperty()
    {
        $this->ensureTenantConnection();
        
        $query = Items::query()
            ->select([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                DB::raw('COALESCE(MAX(inv_unconfirmed_qty.qty), 0) AS quantity'),
                DB::raw('0 AS percentage'),
                DB::raw('SUM(CASE WHEN inv_inventory_adjustments.type = "entrada" THEN COALESCE(inv_detail_inv_adjustments.quantity, 0) ELSE 0 END) AS insideMovement'),
                DB::raw('SUM(CASE WHEN inv_inventory_adjustments.type = "salida" THEN COALESCE(inv_detail_inv_adjustments.quantity, 0) ELSE 0 END) AS outsideMovement'),
                'imp_items_setup.exw'
            ])
            ->join('inv_items_store', 'inv_items_store.itemId', '=', 'inv_items.id')
            ->join('imp_items_setup', 'imp_items_setup.item_id', '=', 'inv_items.id')
            ->join('inv_store', 'inv_store.id', '=', 'inv_items_store.storeId')
            ->leftJoin('inv_detail_inv_adjustments', 'inv_detail_inv_adjustments.itemId', '=', 'inv_items.id')
            ->leftJoin('inv_inventory_adjustments', 'inv_inventory_adjustments.id', '=', 'inv_detail_inv_adjustments.inventoryAdjustmentId')
            ->leftJoin('inv_unconfirmed_qty', 'inv_unconfirmed_qty.item_id', '=', 'inv_items.id')
            ->where('inv_items.status', 1)
            ->where('inv_store.id', $this->storeId)
            ->where('inv_items.type', 'IMPORTADO')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('inv_items.name', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.internal_code', 'like', '%' . $this->search . '%');
                });
            })
            ->groupBy([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                'imp_items_setup.exw'
            ])
            ->orderBy($this->sortField, $this->sortDirection);

        // Log del SQL generado
        Log::info('=== IMPORT ITEMS QUERY ===');
        Log::info('SQL: ' . $query->toSql());
        Log::info('Bindings: ' . json_encode($query->getBindings()));
        
        $results = $query->paginate($this->perPage);
        
        // Log de los resultados
        Log::info('Total items encontrados: ' . $results->total());
        Log::info('Items en página actual: ' . $results->count());
        
        if ($results->count() > 0) {
            Log::info('Primer item de ejemplo:');
            Log::info(json_encode($results->first()->toArray(), JSON_PRETTY_PRINT));
        }
        
        Log::info('=== FIN IMPORT ITEMS QUERY ===');
        
        return $results;
    }

    #[On('refresh-import-list')]
    public function refreshList()
    {
        $this->resetPage();
    }

    /**
     * Método que se ejecuta cuando cambia la cantidad seleccionada
     */
    public function updateQuantity($itemId, $quantity)
    {
        try {
            $this->ensureTenantConnection();
            
            // Convertir a entero y asegurar que no sea negativo
            $quantity = max(0, (int) $quantity);
            
            // Buscar o crear el registro
            InvUnconfirmedQty::updateOrCreate(
                ['item_id' => $itemId],
                [
                    'qty' => $quantity,
                    'status' => true
                ]
            );
            
            // Actualizar el array local
            $this->selectedQuantities[$itemId] = $quantity;
            $this->dispatch('quantity-updated', itemId: $itemId, quantity: $quantity);
            $this->dispatch('refresh-import-list');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar cantidad: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            session()->flash('error', 'Error al actualizar la cantidad: ' . $e->getMessage());
        }
    }

    /**
     * Método que se ejecuta cuando se hace clic en un item
     */
    public function selectItem($itemId, $quantity)
    {
        try {
            $this->ensureTenantConnection();
            
            // Obtener información completa del item
            $item = Items::find($itemId);
            
            if (!$item) {
                Log::warning("Item no encontrado: {$itemId}");
                return;
            }
            
            // Log para debug
            Log::info("Item seleccionado - ID: {$itemId}, Cantidad: {$quantity}");
            Log::info("Item completo: " . json_encode($item->toArray()));
            
            // Emitir evento al componente padre con los datos del item
            $this->dispatch('item-selected', [
                'itemId' => $itemId,
                'quantity' => $quantity,
                'sku' => $item->sku,
                'name' => $item->name,
                'description' => $item->description,
                'stock' => $item->stock_items_store ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al seleccionar item: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Listener para trigger de selección desde otro componente
     */
    #[On('trigger-item-selection')]
    public function triggerItemSelection($itemId)
    {
        try {
            $this->ensureTenantConnection();
            
            // Obtener la cantidad del item
            $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::where('item_id', $itemId)->first();
            $quantity = $unconfirmedQty ? $unconfirmedQty->qty : 0;
            
            Log::info('=== TRIGGER ITEM SELECTION ===');
            Log::info('Item ID: ' . $itemId);
            Log::info('Quantity: ' . $quantity);
            
            // Llamar al método selectItem
            $this->selectItem($itemId, $quantity);
            
        } catch (\Exception $e) {
            Log::error('Error en triggerItemSelection: ' . $e->getMessage());
        }
    }

    #[Computed]
    public function labels()
    {
        Log::info('=== LABELS COMPUTED PROPERTY CALLED ===');
        
        try {
            $this->ensureTenantConnection();
            Log::info('Conexión tenant establecida correctamente');
            
            // Verificar si hay conexión a la base de datos
            Log::info('Intentando obtener labels de ImpLabels');
            
            $labels = ImpLabels::all();
            
            Log::info('Total de labels encontrados: ' . $labels->count());
            
            if ($labels->count() > 0) {
                Log::info('Primer label de ejemplo:');
                Log::info(json_encode($labels->first()->toArray(), JSON_PRETTY_PRINT));
            } else {
                Log::warning('No se encontraron labels en la tabla imp_labels');
            }
            
            Log::info('=== FIN LABELS COMPUTED PROPERTY ===');
            
            return $labels;
            
        } catch (\Exception $e) {
            Log::error('Error al obtener labels: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect(); // Retornar colección vacía en caso de error
        }
    }

    public function render()
    {
        $items = $this->items;
        $labels = $this->labels; 
        
        // Debug info para mostrar en la vista
        $debugInfo = [
            'total' => $items->total(),
            'per_page' => $items->perPage(),
            'current_page' => $items->currentPage(),
            'count' => $items->count(),
            'search' => $this->search,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'store_id' => $this->storeId,
             'labels_count' => $labels->count(), // Agregar contador de labels
            'has_labels' => $labels->isNotEmpty(),
        ];
        Log::info('=== RENDER METHOD ===');
        Log::info('Items encontrados: ' . $items->count());
        Log::info('Labels encontrados: ' . $labels->count());
        Log::info('Debug info: ' . json_encode($debugInfo));
        Log::info('=== FIN RENDER METHOD ===');

        return view('livewire.tenant.imports.components.import-list', [
            'items' => $items,
            'labels' => $labels,
            'debugInfo' => $debugInfo
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