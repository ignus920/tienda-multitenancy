<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Imports\ImpLabels;
use App\Models\Tenant\Imports\ImpImports;
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
    
    // Property to track selected label for filtering
    public $selectedLabelId = null; // null = show all, number = filter by label

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

     protected $listeners = [
        'label-selected' => 'onLabelSelected',
        'labelSelected' => 'onLabelSelected',  // Add this line to handle both formats
        'testEvent' => 'testEvent',
    ];

    #[On('labelSelected')]
    #[On('label-selected')]
    public function onLabelSelected($labelId)
    {
        Log::info("=== LABEL SELECTED EVENT ===");
        Log::info("Label ID recibido: {$labelId}");
        Log::info("Tipo de dato: " . gettype($labelId));
        
        // Find the label name from the labels collection
        $labelName = '';
        $labelsCollection = $this->labels;
        
        if ($labelsCollection && $labelsCollection->count() > 0) {
            $selectedLabel = $labelsCollection->firstWhere('id', $labelId);
            if ($selectedLabel) {
                $labelName = is_array($selectedLabel) ? $selectedLabel['name'] : $selectedLabel->name;
            }
        }
        
        Log::info("Label encontrado: {$labelName}");
        
        // If "Con etiqueta" option (id = 0), show all items
        if ($labelId == 0) {
            $this->selectedLabelId = null;
            Log::info("Mostrando todos los items (Con etiqueta seleccionado)");
        } else {
            $this->selectedLabelId = $labelId;
            Log::info("Filtrando por label ID: {$labelId}");
        }
        
        $this->selectedLabel = [
            'id' => $labelId,
            'name' => $labelName
        ];
        
        $this->resetPage(); // Reset pagination when filter changes
        
        // Clear the computed property cache to force re-evaluation
        unset($this->items);
        
        // Force Livewire to re-render
        $this->dispatch('$refresh');
        
        Log::info("selectedLabelId final: " . ($this->selectedLabelId ?? 'null'));
        Log::info("=== FIN LABEL SELECTED EVENT ===");
    }

    public function testEvent()
    {
        Log::info("TEST EVENT RECEIVED!");
        $this->selectedLabelId = 999;
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
        
        Log::info("=== GET ITEMS - selectedLabelId: " . ($this->selectedLabelId ?? 'null') . " ===");
        
        $query = Items::query()
            ->select([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                DB::raw($this->selectedLabelId 
                    ? 'COALESCE(imp_imports.qty_requested, 0) AS quantity'
                    : 'COALESCE(MAX(inv_unconfirmed_qty.qty), 0) AS quantity'
                ),
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
            ->when($this->selectedLabelId, function($query) {
                // INNER JOIN imp_imports to filter only items with this label
                $query->join('imp_imports', function($join) {
                    $join->on('imp_imports.item_id', '=', 'inv_items.id')
                         ->where('imp_imports.label_id', '=', $this->selectedLabelId)
                         ->whereNull('imp_imports.deleted_at');
                });
                // INNER JOIN imp_labels (optional, for additional label data if needed)
                $query->join('imp_labels', 'imp_labels.id', '=', 'imp_imports.label_id');
            })
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
            ->groupBy(array_filter([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                'imp_items_setup.exw',
                $this->selectedLabelId ? 'imp_imports.qty_requested' : null,
            ]))
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
            Log::info('Intentando obtener labels de ImpLabels con cantidad total');
            
            $labels = ImpLabels::select([
                'imp_labels.id',
                'imp_labels.name',
                'imp_labels.asap',
                'imp_labels.estimated_date',
                'imp_labels.description',
                'imp_labels.status',
                'imp_labels.user_id',
                'imp_labels.created_at',
                'imp_labels.updated_at',
                'imp_labels.deleted_at',
                DB::raw('COALESCE(SUM(imp_imports.qty_requested), 0) as total_qty_requested')
            ])
            ->leftJoin('imp_imports', function($join) {
                $join->on('imp_labels.id', '=', 'imp_imports.label_id')
                     ->whereNull('imp_imports.deleted_at');
            })
            ->groupBy([
                'imp_labels.id',
                'imp_labels.name',
                'imp_labels.asap',
                'imp_labels.estimated_date',
                'imp_labels.description',
                'imp_labels.status',
                'imp_labels.user_id',
                'imp_labels.created_at',
                'imp_labels.updated_at',
                'imp_labels.deleted_at'
            ])
            ->get();
            
            Log::info('Total de labels encontrados: ' . $labels->count());
            
            // Formatear los nombres de los labels con la cantidad
            $labels = $labels->map(function($label) {
                $qtyFormatted = number_format($label->total_qty_requested, 0, ',', '.');
                $label->name = $label->name . " ({$qtyFormatted} items)";
                return $label;
            });
            
            // Check if there are any items with labels assigned
            $hasItemsWithLabels = ImpImports::whereNull('deleted_at')->exists();

            if ($hasItemsWithLabels) {
                // Create a "Con etiqueta" option as array
                $allOption = [
                    'id' => 0,
                    'name' => 'Con etiqueta',
                    'asap' => null,
                    'estimated_date' => null,
                    'description' => null,
                    'status' => null,
                    'user_id' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'deleted_at' => null,
                    'total_qty_requested' => 0,
                ];
                
                // Convert all labels to arrays to prevent Livewire hydration issues
                $labelsArray = $labels->map(function($label) {
                    return is_object($label) && method_exists($label, 'toArray') 
                        ? $label->toArray() 
                        : (array) $label;
                })->toArray();
                
                // Prepend the "Con etiqueta" option
                array_unshift($labelsArray, $allOption);
                
                $labels = collect($labelsArray);
            } else {
                // Convert all labels to arrays even if no "Con etiqueta" option
                $labels = $labels->map(function($label) {
                    return is_object($label) && method_exists($label, 'toArray') 
                        ? $label->toArray() 
                        : (array) $label;
                });
            }
            
            if ($labels->count() > 0) {
                Log::info('Primer label de ejemplo:');
                $firstLabel = $labels->first();
                // Already an array now
                $labelArray = is_array($firstLabel) ? $firstLabel : (array) $firstLabel;
                Log::info(json_encode($labelArray, JSON_PRETTY_PRINT));
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