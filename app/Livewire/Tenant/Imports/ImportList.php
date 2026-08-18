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
use App\Models\Tenant\Items\InvStore;
use App\Models\Tenant\Imports\InvUnconfirmedQty;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasCompanyConfiguration;

class ImportList extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $search = '';
    public $perPage = 20;
    public $selectedSupplierId = null;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    //public $storeId = 1; // Warehouse/Store ID configurable

    // Array para almacenar las cantidades seleccionadas por item
    public $selectedQuantities = [];
    public $selectedItems = [];

    // Array para almacenar los labels
    public $allLabels = [];

    // Property to track selected label for filtering
    public $selectedLabelId = null; // null = show all, number = filter by label
    public $selectedLabelName = 'Programación'; // Nombre a mostrar en el dropdown
    public $filterCritical = 'ninguno'; // Filtrar por productos críticos

    public function updatingFilterCritical()
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $listeners = [
        'label-selected' => 'onLabelSelected',
        'labelSelected' => 'onLabelSelected',  // Add this line to handle both formats
        'testEvent' => 'testEvent',
        'update-item-quantity' => 'onUpdateItemQuantity',
        'supplier-selected' => 'onSupplierSelected',
    ];

    public function mount()
    {
        if (Auth::user()?->profile_id == 17) {
            $this->selectedSupplierId = Auth::id();
        }
    }

    #[On('supplier-selected')]
    public function onSupplierSelected($supplierId)
    {
        $this->selectedSupplierId = $supplierId ?: null;
        $this->resetPage();
    }


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

        // Handle different selection options
        if ($labelId == -1) {
            // "Programación" option - show all items without filter
            $this->selectedLabelId = null;
            $this->selectedLabelName = 'Programación';
            Log::info("Mostrando todos los items sin filtro (Programación seleccionado)");
        } elseif ($labelId == 0) {
            // "Con etiqueta" option - show all items with any label
            $this->selectedLabelId = null;
            $this->selectedLabelName = 'Con etiqueta';
            Log::info("Mostrando todos los items (Con etiqueta seleccionado)");
        } else {
            // Specific label selected
            $this->selectedLabelId = $labelId;
            // Remover el contador de items del nombre para mostrarlo limpio
            $cleanName = preg_replace('/\s*\(\d+\s*items?\)$/', '', $labelName);
            $this->selectedLabelName = $cleanName ?: $labelName;
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
        Log::info("selectedLabelName final: " . $this->selectedLabelName);
        Log::info("=== FIN LABEL SELECTED EVENT ===");
    }

    /**
     * Listener para actualizar la cantidad de un item en el input
     */
    #[On('update-item-quantity')]
    public function onUpdateItemQuantity($data)
    {
        try {
            Log::info('=== INICIO onUpdateItemQuantity ===');
            Log::info('Data recibida: ' . json_encode($data));

            $itemId = $data['itemId'];
            $quantity = $data['quantity'];

            Log::info('Item ID: ' . $itemId);
            Log::info('Nueva cantidad: ' . $quantity);

            // Actualizar el array local
            $this->selectedQuantities[$itemId] = $quantity;

            // Refrescar la lista para que se actualice el input
            $this->dispatch('$refresh');

            Log::info('Cantidad actualizada en el array local');
            Log::info('=== FIN onUpdateItemQuantity ===');
        } catch (\Exception $e) {
            Log::error('Error en onUpdateItemQuantity: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
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
        // No resetear página para mantener el usuario en la página actual mientras busca
        // $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        // No resetear página para mantener el usuario en la página actual al ordenar
        // $this->resetPage();
    }

    public function getOccupiedPrioritiesProperty()
    {
        if (empty($this->selectedItems)) {
            return [];
        }

        $this->ensureTenantConnection();

        return ImpImports::whereIn('item_id', $this->selectedItems)
            ->where('status', '<', 8)
            ->whereNotNull('priority')
            ->whereNull('deleted_at')
            ->pluck('priority')
            ->map(fn($p) => strtolower($p))
            ->unique()
            ->toArray();
    }

    public function getItemsProperty()
    {
        $this->ensureTenantConnection();
        $principalStore = $this->getPrincipalStore();

        Log::info("=== GET ITEMS - selectedLabelId: " . ($this->selectedLabelId ?? 'null') . " ===");

        // Debug: Si hay label seleccionado, verificar qué items existen en imp_imports
        if ($this->selectedLabelId) {
            $importsCheck = DB::connection('tenant')
                ->table('imp_imports')
                ->where('label_id', $this->selectedLabelId)
                ->where('status', '<', 8) // Filtrar status < 8
                ->whereNull('deleted_at')
                ->get(['id', 'item_id', 'qty_requested', 'label_id', 'status']);

            Log::info('DEBUG - Items en imp_imports para label ' . $this->selectedLabelId . ': ' . $importsCheck->count());
            if ($importsCheck->count() > 0) {
                Log::info('DEBUG - Primer registro imp_imports: ' . json_encode($importsCheck->first(), JSON_PRETTY_PRINT));
                Log::info('DEBUG - Item IDs en imp_imports: ' . $importsCheck->pluck('item_id')->implode(', '));
            }
        }

        $query = Items::query()
            ->select([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                DB::raw('COALESCE(inv_items_store.stock_items_store, 0) AS stock_items_store'),
                 DB::raw(
                     $this->selectedLabelId
                         ? 'COALESCE(imp_imports.qty_requested, 0) AS quantity'
                         : 'COALESCE(MAX(imp_unconfirmed_qty.qty), 0) AS quantity'
                 ),
                DB::raw('
                    CASE 
                        WHEN (COALESCE(inv_items_store.stock_items_store, 0) + COALESCE(s7m.salidas_7_meses, 0)) > 0 
                        THEN ROUND((COALESCE(inv_items_store.stock_items_store, 0) * 100) / (COALESCE(inv_items_store.stock_items_store, 0) + COALESCE(s7m.salidas_7_meses, 0)))
                        ELSE 0 
                    END AS percentage
                '),
                DB::raw('SUM(CASE WHEN inv_inventory_adjustments.type = "entrada" THEN COALESCE(inv_detail_inv_adjustments.quantity, 0) ELSE 0 END) AS insideMovement'),
                DB::raw('COALESCE(s7m.salidas_7_meses, 0) AS outsideMovement'),
                DB::raw('COALESCE(imp_items_setup.exw, 0) AS exw'),
                DB::raw('(SELECT priority FROM imp_imports WHERE item_id = inv_items.id AND status < 8 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1) AS priority'),
                DB::raw('(SELECT priority_assigned_at FROM imp_imports WHERE item_id = inv_items.id AND status < 8 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1) AS priority_assigned_at'),
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(il.name, ': ', ii.qty_requested, ' uds') SEPARATOR ' \n ') FROM imp_imports ii JOIN imp_labels il ON ii.label_id = il.id WHERE ii.item_id = inv_items.id AND ii.status < 8 AND ii.deleted_at IS NULL) AS label_assignments")
            ])
            ->leftJoin('inv_items_store', function ($join) use ($principalStore) {
                $join->on('inv_items_store.itemId', '=', 'inv_items.id')
                     ->where('inv_items_store.storeId', '=', $principalStore->id);
            })
            ->leftJoin('imp_items_setup', 'imp_items_setup.item_id', '=', 'inv_items.id')
            ->leftJoin('inv_detail_inv_adjustments', 'inv_detail_inv_adjustments.itemId', '=', 'inv_items.id')
            ->leftJoin('inv_inventory_adjustments', 'inv_inventory_adjustments.id', '=', 'inv_detail_inv_adjustments.inventoryAdjustmentId')
            ->leftJoin('imp_unconfirmed_qty', function ($join) {
                $join->on('imp_unconfirmed_qty.item_id', '=', 'inv_items.id')
                     ->whereNull('imp_unconfirmed_qty.deleted_at');
            })
            ->leftJoin(DB::raw('
                (
                    SELECT sub.itemId, SUM(sub.qty) as salidas_7_meses
                    FROM (
                        SELECT idr.itemId, idr.quantity as qty
                        FROM inv_detail_remissions idr
                        INNER JOIN inv_remissions ir ON ir.id = idr.remissionId
                        WHERE ir.status != \'ANULADO\'
                        AND COALESCE(ir.created_at, ir.updated_at) >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), \'%Y-%m-01\')
                        AND COALESCE(ir.created_at, ir.updated_at) >= \'2026-06-01\'

                        UNION ALL

                        SELECT item_sub.id as itemId, lsh.quantity as qty
                        FROM legacy_sales_history lsh
                        INNER JOIN inv_items item_sub ON item_sub.sku = lsh.sku
                        WHERE DATE(CONCAT(lsh.year, \'-\', lsh.month, \'-01\')) >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), \'%Y-%m-01\')
                    ) sub
                    GROUP BY sub.itemId
                ) s7m
            '), 's7m.itemId', '=', 'inv_items.id')
            ->when($this->selectedLabelId, function ($query) {
                // INNER JOIN imp_imports to filter only items with this label
                $query->join('imp_imports', function ($join) {
                    $join->on('imp_imports.item_id', '=', 'inv_items.id')
                        ->where('imp_imports.label_id', '=', $this->selectedLabelId)
                        ->where('imp_imports.status', '<', 8) // Filtrar status < 8
                        ->whereNull('imp_imports.deleted_at');
                });
                // INNER JOIN imp_labels (optional, for additional label data if needed)
                $query->join('imp_labels', function ($join) {
                    $join->on('imp_labels.id', '=', 'imp_imports.label_id')
                        ->where('imp_labels.status', 1); // Solo etiquetas con status = 1
                });
            })
            ->where('inv_items.status', 1)
            // ->where('inv_items.type', '!=', 'DESCONTINUADOS')
            ->when($this->selectedSupplierId, function ($query) {
                return $query->where('imp_items_setup.supplier_id', $this->selectedSupplierId);
            })
            ->when($this->search, function ($query) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('inv_items.name', 'like', '%' . $word . '%')
                            ->orWhere('inv_items.sku', 'like', '%' . $word . '%')
                            ->orWhere('inv_items.internal_code', 'like', '%' . $word . '%');
                    });
                }
            })
            ->when($this->filterCritical !== 'ninguno', function ($query) {
                if ($this->filterCritical === 'importados') {
                    $query->where('inv_items.type', 'IMPORTADO');
                } elseif ($this->filterCritical === 'compra_nacional') {
                    $query->where('inv_items.type', 'COMPRA NACIONAL');
                } else {
                    $query->whereIn('inv_items.type', ['IMPORTADO', 'COMPRA NACIONAL']);
                }

                $query->where(DB::raw('
                        CASE 
                            WHEN (COALESCE(inv_items_store.stock_items_store, 0) + COALESCE(s7m.salidas_7_meses, 0)) > 0 
                            THEN (COALESCE(inv_items_store.stock_items_store, 0) * 100) / (COALESCE(inv_items_store.stock_items_store, 0) + COALESCE(s7m.salidas_7_meses, 0))
                            ELSE 0 
                        END
                    '), '<', 50)
                    ->whereNotExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('imp_imports as iim')
                            ->whereColumn('iim.item_id', 'inv_items.id')
                            ->where('iim.status', '<', 8)
                            ->whereNull('iim.deleted_at')
                            ->whereIn('iim.priority', ['ASAP', 'Second', 'Third']);
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
                's7m.salidas_7_meses',
                $this->selectedLabelId ? 'imp_imports.qty_requested' : null,
            ]))
            ->orderBy($this->sortField, $this->sortDirection);

        // Log del SQL generado
        Log::info('=== IMPORT ITEMS QUERY ===');
        Log::info('SQL: ' . $query->toSql());
        Log::info('Bindings: ' . json_encode($query->getBindings()));

        $results = $query->paginate($this->perPage);

        // Obtener programaciones y embarques agrupados de manera eficiente para los items de la página actual
        try {
            $itemIds = $results->pluck('id')->toArray();
            $importData = DB::connection('tenant')
                ->table('imp_imports as ii')
                ->select([
                    'ii.item_id',
                    'ii.qty_requested',
                    'ii.priority',
                    'ii.status as status_id',
                    'ist.translated_name as status_name',
                    'il.name as label_name',
                    's.operation_number as shipment_number',
                    'ii.priority_assigned_at as due_date'
                ])
                ->leftJoin('imp_status as ist', 'ii.status', '=', 'ist.id')
                ->leftJoin('imp_labels as il', 'ii.label_id', '=', 'il.id')
                ->leftJoin('imp_packing as pk', 'ii.packing_id', '=', 'pk.id')
                ->leftJoin('imp_shippments as s', 'pk.shipping_id', '=', 's.id')
                ->whereIn('ii.item_id', $itemIds)
                ->where('ii.status', '<', 8)
                ->whereNull('ii.deleted_at')
                ->get()
                ->groupBy('item_id');

            foreach ($results as $item) {
                $item->programaciones = $importData->get($item->id, collect());
            }
        } catch (\Exception $e) {
            Log::error('Error consultando programaciones en ImportList: ' . $e->getMessage());
            foreach ($results as $item) {
                $item->programaciones = collect();
            }
        }

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
        Log::info('=== REFRESH IMPORT LIST ===');

        // Limpiar el cache de items
        unset($this->items);

        // No resetear paginación cuando se actualiza la cantidad para mantener el usuario en la página actual
        // $this->resetPage();

        // Limpiar cantidades seleccionadas
        $this->selectedQuantities = [];

        // Resetear filtro de etiquetas al estado inicial
        $this->selectedLabelId = null;
        $this->selectedLabelName = 'Programación';
        $this->selectedLabel = [
            'id' => '',
            'name' => ''
        ];

        // Forzar re-render
        $this->dispatch('$refresh');

        Log::info('Lista refrescada exitosamente - Volviendo al estado inicial');
        Log::info('=== FIN REFRESH ===');
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

            $unconfirmedQty = InvUnconfirmedQty::withTrashed()->where('item_id', $itemId)->first();

            if ($unconfirmedQty) {
                $unconfirmedQty->restore();
                $unconfirmedQty->update([
                    'qty' => $quantity,
                    'status' => true
                ]);
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Cantidad actualizada'
                ]);
            } else {
                InvUnconfirmedQty::create([
                    'item_id' => $itemId,
                    'qty' => $quantity,
                    'status' => true
                ]);
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Cantidad registrada'
                ]);
            }

            // Actualizar el array local
            $this->selectedQuantities[$itemId] = $quantity;

            // Seleccionar automáticamente el checkbox de la fila si la cantidad es mayor a 0
            if ($quantity > 0) {
                if (!in_array($itemId, $this->selectedItems)) {
                    $this->selectedItems[] = $itemId;
                }
            } else {
                // Si la cantidad es 0, deseleccionar el checkbox
                $this->selectedItems = array_values(array_filter($this->selectedItems, fn($id) => $id != $itemId));
            }

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
            $principalStore = $this->getPrincipalStore();

            // Obtener información completa del item incluyendo el stock y prioridades
            $item = Items::query()
                ->select([
                    'inv_items.id',
                    'inv_items.sku',
                    'inv_items.name',
                    'inv_items.description',
                    DB::raw('COALESCE(inv_items_store.stock_items_store, 0) AS stock_items_store'),
                    DB::raw('(SELECT priority FROM imp_imports WHERE item_id = inv_items.id AND status < 8 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1) AS priority'),
                    DB::raw('(SELECT priority_assigned_at FROM imp_imports WHERE item_id = inv_items.id AND status < 8 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1) AS priority_assigned_at')
                ])
                ->leftJoin('inv_items_store', function ($join) use ($principalStore) {
                    $join->on('inv_items_store.itemId', '=', 'inv_items.id')
                         ->where('inv_items_store.storeId', '=', $principalStore->id);
                })
                ->where('inv_items.id', $itemId)
                ->first();

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
                'stock' => $item->stock_items_store ?? 0,
                'priority' => $item->priority,
                'priority_assigned_at' => $item->priority_assigned_at
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

            // Calcular la fecha de un año desde hoy
            $oneYearFromNow = now()->addYear();

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
                ->leftJoin('imp_imports', function ($join) {
                    $join->on('imp_labels.id', '=', 'imp_imports.label_id')
                        ->where('imp_imports.status', '<', 8) // Filtrar status < 8
                        ->whereNull('imp_imports.deleted_at');
                })
                ->where('imp_labels.status', 1) // Filtrar solo etiquetas con estado = 1
                ->where(function ($query) use ($oneYearFromNow) {
                    // Filtrar etiquetas con fecha estimada dentro del próximo año o sin fecha (asap)
                    $query->where('imp_labels.estimated_date', '<=', $oneYearFromNow)
                        ->orWhereNull('imp_labels.estimated_date')
                        ->orWhere('imp_labels.asap', 1);
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
                ->having('total_qty_requested', '>', 0) // Solo etiquetas con cantidad mayor a 0
                ->get();

            Log::info('Total de labels encontrados: ' . $labels->count());

            // Formatear los nombres de los labels con la cantidad
            $labels = $labels->map(function ($label) {
                $qtyFormatted = number_format($label->total_qty_requested, 0, ',', '.');
                $label->name = $label->name . " ({$qtyFormatted} items)";
                return $label;
            });

            // Check if there are any items with labels assigned
            $hasItemsWithLabels = ImpImports::whereNull('deleted_at')->exists();

            if ($hasItemsWithLabels) {
                // Create a "Programación" option as the first item
                $programacionOption = [
                    'id' => -1, // ID especial para "Programación"
                    'name' => 'Programación',
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

                // Create a "Con etiqueta" option as second item
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
                $labelsArray = $labels->map(function ($label) {
                    return is_object($label) && method_exists($label, 'toArray')
                        ? $label->toArray()
                        : (array) $label;
                })->toArray();

                // Prepend both options
                array_unshift($labelsArray, $allOption);
                array_unshift($labelsArray, $programacionOption);

                $labels = collect($labelsArray);
            } else {
                // Convert all labels to arrays even if no options
                $labels = $labels->map(function ($label) {
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
        $principalStore = $this->getPrincipalStore();

        // Debug info para mostrar en la vista
        $debugInfo = [
            'total' => $items->total(),
            'per_page' => $items->perPage(),
            'current_page' => $items->currentPage(),
            'count' => $items->count(),
            'search' => $this->search,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'store_id' => $principalStore->id ?? 'No store found',
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

    public function assignPriorityToSelected($priority)
    {
        try {
            if (empty($this->selectedItems)) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No hay ítems seleccionados'
                ]);
                return;
            }

            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () use ($priority) {
                foreach ($this->selectedItems as $itemId) {
                    $import = ImpImports::where('item_id', $itemId)
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->first();

                    $unconfirmed = DB::connection('tenant')
                        ->table('imp_unconfirmed_qty')
                        ->where('item_id', $itemId)
                        ->whereNull('deleted_at')
                        ->first();
                    $unconfirmedQty = $unconfirmed ? $unconfirmed->qty : 0;

                    // Si hay una cantidad sin confirmar (> 0), en la vista de Movimiento siempre insertamos un nuevo pedido
                    if ($unconfirmedQty > 0 && $priority !== null) {
                        ImpImports::create([
                            'item_id' => $itemId,
                            'priority' => $priority,
                            'priority_assigned_at' => now(),
                            'qty_requested' => $unconfirmedQty,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            'status' => 1
                        ]);

                        DB::connection('tenant')
                            ->table('imp_unconfirmed_qty')
                            ->where('item_id', $itemId)
                            ->update([
                                'qty' => 0,
                                'updated_at' => now()
                            ]);
                    } else {
                        // Si no hay cantidad nueva, o es para quitar la prioridad, actualizamos el registro existente
                        if ($import) {
                            $import->update([
                                'priority' => $priority,
                                'priority_assigned_at' => $priority ? now() : null,
                                'user_id' => \Illuminate\Support\Facades\Auth::id()
                            ]);
                        }
                    }

                    // Limpiar localmente la cantidad de este ítem
                    unset($this->selectedQuantities[$itemId]);
                    $this->dispatch('update-item-quantity', [
                        'itemId' => $itemId,
                        'quantity' => 0
                    ]);
                }
            });

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Prioridades asignadas en lote exitosamente'
            ]);

            $this->selectedItems = [];
            
            // Notificar al componente padre para que actualice la información del item seleccionado
            $this->dispatch('refresh-selected-item');
            
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error('Error al asignar prioridades en lote: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al asignar las prioridades: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener el store principal de la empresa
     */
    private function getPrincipalStore(): ?InvStore
    {
        try {
            $this->ensureTenantConnection();

            // Buscar el store principal (puede ser por status = 1 y el primero, o por algún campo específico)
            $principalStore = InvStore::where('status', 1)
                ->orderBy('id', 'asc')
                ->first();

            if (!$principalStore) {
                Log::warning('No se encontró store principal para la empresa', [
                    'company_id' => $this->currentCompanyId
                ]);
                return null;
            }

            Log::info('Store principal encontrado', [
                'store_id' => $principalStore->id,
                'store_name' => $principalStore->name,
                'company_id' => $this->currentCompanyId
            ]);

            return $principalStore;
        } catch (\Exception $e) {
            Log::error('Error obteniendo store principal', [
                'error' => $e->getMessage(),
                'company_id' => $this->currentCompanyId
            ]);
            return null;
        }
    }
}
