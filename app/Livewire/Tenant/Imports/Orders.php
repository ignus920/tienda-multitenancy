<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpStatus;
use App\Models\Tenant\Imports\ImpImports;
use App\Models\Tenant\Imports\ImpComments;
use App\Models\Tenant\Imports\ImpStatusHistory;
use App\Models\Tenant\Imports\ImpPacking;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Imports\ImpLabels;
use App\Models\Tenant\Imports\ImpShippments;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Orders extends Component
{
    use WithPagination;

    public $filterStatus = '';
    public $filterNews = '';
    public $filterPacking = '';
    public $search = '';
    public $perPage = 10;
    public $selectedOrders = [];
    public $selectedPackingIds = [];
    public $selectedLabelId = null;
    public $selectedLabelName = 'Programming';
    public $selectedShipp = 0;
    public $allLabels = [];
    public $showModalHistory = false;
    public $showModalChangeQuantity = false;
    public $showModalAcceptNew = false;
    public $showModalConfirmPrice = false;
    public $showModalJustifyChangePrice = false;
    public $showModalConfirmProduction = false;
    public $showButtonShipping = false;
    public $showModalChangeQtyShip = false;
    public $showModalShipping = false;
    public $lockWay = false;
    public $refreshCounter = 0;
    public $selectAll = false;

    // Propiedades para envío existente y eliminación justificada
    public $isExistingShipping = false;
    public $selectedExistingShippingId = null;
    public $deleteJustification = '';
    public $selectedOrderIdForDelete = null;
    public $showModalDelete = false;

    public $import_id;
    public $oldQty;
    public $newQty;
    public $commentChangeQuantity;
    public $commentAccept;
    public $price;
    public $commentJustifyPrice;
    public $commentChangeQtyShip;
    public $etd;
    public $operation_number;
    public $way;
    public $conveyor;
    public $observations;

    protected $listeners = [
        'labelSelected' => 'onLabelSelected',  // Add this line to handle both formats
        'shippmentSelected' => 'onShippmentSelected',
        'testEvent' => 'testEvent',
    ];

    protected $rules = [
        'commentChangeQuantity' => 'required',
        'commentAccept' => 'required',
        'commentChangeQtyShip' => 'required',
        'commentJustifyPrice' => 'required',
        'deleteJustification' => 'required'
    ];

    protected $messages = [
        'deleteJustification.required' => 'Debe ingresar una justificación para eliminar el producto',
        'commentChangeQuantity.required' => 'Debe ingresar un comentario',
        'commentAccept.required' => 'Debe ingresar un comentario',
        'commentChangeQtyShip.required' => 'You must enter a comment',
        'commentJustifyPrice.required' => 'Debes escribir un comentario.',
        'etd.required' => 'Please complete all required fields (*)',
        'operation_number.required' => 'Please complete all required fields (*)',
        'way.required' => 'Please complete all required fields (*)'
    ];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    #[Computed]
    public function status()
    {
        // Primera consulta: Registros agrupados por estado, filtrados por proveedor si corresponde
        $estados = DB::connection('tenant')
            ->table('imp_imports as i')
            ->leftJoin('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->rightJoin('imp_status as s', 'i.status', '=', 's.id')
            ->where('s.id', '!=', 6)
            ->select('s.name as nombre_estado', 's.translated_name', DB::raw('COUNT(i.id) as cantidad'), 's.id as id')
            ->when(Auth::user()->profile_id == 17, function ($query) {
                return $query->where(function ($q) {
                    $q->where('iis.supplier_id', Auth::id())
                      ->orWhereNull('i.id');
                });
            })
            ->groupBy('s.id', 's.name', 's.translated_name');

        // Segunda consulta: Solo novedades, filtradas por proveedor si corresponde
        $novedades = DB::connection('tenant')
            ->table('imp_imports as i')
            ->leftJoin('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->select(
                DB::raw("'Novedades' as nombre_estado"),
                DB::raw("'Novedades' as translated_name"),
                DB::raw('COUNT(i.id) as cantidad'),
                DB::raw("10 as id")
            )
            ->where('i.news', 1)
            ->when(Auth::user()->profile_id == 17, function ($query) {
                return $query->where('iis.supplier_id', Auth::id());
            })
            ->groupBy('i.news');

        // Unir las consultas y ordenar
        return $estados
            ->unionAll($novedades)
            ->orderBy('id', 'asc')
            ->orderBy('nombre_estado', 'asc')
            ->get();
    }

    public function putFilter($statusId)
    {
        if ($statusId == 10) {
            // Si ya estamos filtrando por novedades, lo limpiamos
            if ($this->filterNews == 1) {
                $this->filterNews = '';
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => 'Filtro de novedades limpiado'
                ]);
            } else {
                // Si no, activamos novedades y limpiamos el filtro de estado
                $this->filterNews = 1;
                $this->filterStatus = '';
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Filtro de novedades aplicado'
                ]);
            }
        } else {
            // Si se hace clic en el mismo estado, se limpia el filtro
            if ($this->filterStatus == $statusId) {
                $this->filterStatus = '';
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => 'Filtro de estado limpiado'
                ]);
            } else {
                // Aplicamos el nuevo estado y limpiamos novedades
                $this->filterStatus = $statusId;
                $this->filterNews = '';
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Filtro de estado aplicado'
                ]);
            }
        }
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        $centralDbName = config('database.connections.central.database');
        return DB::connection('tenant')
            ->table('imp_imports as i')
            ->select([
                'i.id',
                DB::raw("CONCAT(iv.internal_code, ' - ', iv.name) AS item"),
                'iis.factory_ref',
                'iis.exw',
                'i.qty_requested',
                'il.name AS label',
                'ist.translated_name',
                'i.status',
                'i.priority',
                'i.priority_assigned_at',
                'i.qty_shipped',
                'i.news',
                'i.price',
                'i.delete_justification',
                'pk.number_packing as packing_number',
                's.operation_number',
                's.etd',
                DB::raw("CONCAT('#',s.consecutive,' ', s.way) AS way"),
                DB::raw("(SELECT comment 
                        FROM imp_comments 
                        WHERE import_id = i.id 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) AS ultimo_comentario"),
                DB::raw("(SELECT created_at 
                        FROM imp_status_history 
                        WHERE import_id = i.id AND new_state = 8 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) AS received_at"),
                DB::raw("(SELECT u.name 
                         FROM imp_status_history sh 
                         JOIN {$centralDbName}.users u ON sh.user_id = u.id 
                         WHERE sh.import_id = i.id AND sh.new_state = 11 
                         ORDER BY sh.created_at DESC 
                         LIMIT 1
                    ) AS deleted_by_user")
            ])
            ->leftJoin('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->join('inv_items as iv', 'i.item_id', '=', 'iv.id')
            ->leftJoin('imp_labels as il', 'i.label_id', '=', 'il.id')
            ->join('imp_status as ist', 'i.status', '=', 'ist.id')
            ->leftJoin('imp_packing as pk', 'i.packing_id', '=', 'pk.id')
            ->leftJoin('imp_shippments as s', 'pk.shipping_id', '=', 's.id')
            ->when(Auth::user()->profile_id == 17, function ($query) {
                return $query->where('iis.supplier_id', Auth::id());
            })
            ->when($this->filterStatus, function ($query) {
                return $query->where('i.status', $this->filterStatus);
            })
            ->when($this->filterNews, function ($query) {
                return $query->where('i.news', $this->filterNews);
            })
            ->when($this->selectedLabelId, function ($query) {
                return $query->where('i.label_id', $this->selectedLabelId);
            })
            ->when($this->filterPacking, function ($query) {
                return $query->where('i.packing_id', $this->filterPacking);
            })
            ->when($this->selectedShipp > 0, function ($query) {
                return $query->where('pk.shipping_id', $this->selectedShipp);
            })
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('iv.name', 'like', '%' . $this->search . '%')
                      ->orWhere('iv.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('iv.internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('iis.factory_ref', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate($this->perPage);
    }

    #[Computed]
    public function labels()
    {
        Log::info('=== LABELS COMPUTED PROPERTY CALLED ===');

        try {
            Log::info('Intentando obtener labels de ImpLabels con cantidad total');

            $labels = ImpImports::select([
                'imp_imports.label_id as id',
                'imp_labels.name'
            ])
                ->join('imp_labels', function ($join) {
                    $join->on('imp_imports.label_id', '=', 'imp_labels.id')
                        ->whereNull('imp_imports.deleted_at');
                })
                ->groupBy([
                    'imp_imports.label_id',
                    'imp_labels.name'
                ])
                ->get()
                ->toArray();

            Log::info('Total de labels encontrados: ' . count($labels));


            Log::info('=== FIN LABELS COMPUTED PROPERTY ===');

            return $labels;
        } catch (\Exception $e) {
            Log::error('Error al obtener labels: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return []; // Retornar array vacío en caso de error
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function saveComment($idImport, $comment, $toastMessage = 'Comentario registrado')
    {
        $this->ensureTenantConnection();
        $query = ImpComments::where('import_id', $idImport)->where('initiator', 1)->first();
        $initiatorExists = !is_null($query);
        try {
            if ($initiatorExists) {
                ImpComments::create([
                    'import_id' => $idImport,
                    'comment' => $comment,
                    'user_id' => Auth::id(),
                    'initiator' => 0
                ]);
            } else {
                ImpComments::create([
                    'import_id' => $idImport,
                    'comment' => $comment,
                    'user_id' => Auth::id(),
                    'initiator' => 1
                ]);
                $import = ImpImports::findOrFail($idImport);
                $import->update(['news' => 1]);
            }
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $toastMessage
            ]);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('❌ Error al guardar el comentario: ' . $e->getMessage());
            return;
        }
    }

    public function updatedSelectAll($value)
    {
        $this->ensureTenantConnection();
        if ($value) {
            $this->selectedOrders = collect($this->orders->items())->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedOrders = [];
        }
    }

    public function updatedSelectedOrders($value)
    {
        /*
        $this->ensureTenantConnection();
        // Si no hay packs seleccionados, dejamos que el usuario marque los checkboxes libremente para otras acciones
        if (empty($this->selectedPackingIds)) {
            return;
        }

        if (empty($value)) return;

        $ids = is_array($value) ? $value : [$value];
        $packing = ImpPacking::find($this->selectedPackingIds[0]);
        $packingName =  $packing ? $packing->number_packing : 'PACK';

        try {
            DB::connection('tenant')->beginTransaction();
            foreach ($ids as $id) {
                $import = ImpImports::find($id);
                if ($import && $import->status != 6) {
                    $oldStatus = $import->status;
                    // Asignar al primer packing seleccionado
                    $packingId = $this->selectedPackingIds[0];
                    $import->update([
                        'packing_id' => $packingId,
                        'status' => 6
                    ]);
                    ImpStatusHistory::create([
                        'import_id' => $id,
                        'previous_state' => $oldStatus,
                        'new_state' => 6,
                        'user_id' => Auth::id()
                    ]);
                }
            }
            DB::connection('tenant')->commit();
            $this->selectedOrders = [];
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Added to ' . $packingName
            ]);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al asociar PACK: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo realizar la asociación'
            ]);
        }
        */
    }

    #[Computed]
    public function historyComments()
    {
        if (!$this->import_id) {
            return collect();
        }

        $centralDbName = config('database.connections.central.database');
        return ImpComments::query()
            ->select('imp_comments.created_at', 'imp_comments.comment', 'u.name')
            ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_comments.user_id')
            ->where('imp_comments.import_id', $this->import_id)
            ->orderBy('imp_comments.created_at', 'DESC')
            ->get();
    }

    #[Computed]
    public function timelineEvents()
    {
        if (!$this->import_id) {
            return collect();
        }

        $centralDbName = config('database.connections.central.database');

        // 1. Obtener comentarios
        $comments = ImpComments::query()
            ->select('imp_comments.created_at', 'imp_comments.comment', 'u.name')
            ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_comments.user_id')
            ->where('imp_comments.import_id', $this->import_id)
            ->get()
            ->map(function ($item) {
                $item->event_type = 'comment';
                return $item;
            });

        // 2. Obtener cambios de estado
        $statuses = ImpStatusHistory::query()->with(['previousStatus', 'newStatus'])
            ->select('imp_status_history.created_at', 'imp_status_history.previous_state', 'imp_status_history.new_state', 'u.name')
            ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_status_history.user_id')
            ->where('imp_status_history.import_id', $this->import_id)
            ->get()
            ->map(function ($item) {
                $item->event_type = 'status_change';
                return $item;
            });

        // 3. Unificar y ordenar cronológicamente
        return $comments->concat($statuses)->sortByDesc('created_at');
    }

    #[Computed]
    public function initiatorCanFinish()
    {
        if (!$this->import_id) {
            return false;
        }
        $initiator = ImpComments::with('import')->where('import_id', $this->import_id)->where('initiator', 1)->first();

        if (!$initiator || !$initiator->import) {
            return false;
        }
        return $initiator->import->news == 1 && $initiator->user_id == Auth::id();
    }

    #[On('shippmentSelected')]
    public function onShippmentSelected($shippmentId)
    {
        $this->ensureTenantConnection();
        $this->selectedShipp = (int) $shippmentId;
        $this->resetPage();
    }

    #[Computed]
    public function selectedShippmentData()
    {
        if ($this->selectedShipp > 0) {
            $this->ensureTenantConnection();
            return \App\Models\Tenant\Imports\ImpShippments::find($this->selectedShipp);
        }
        return null;
    }

    #[On('labelSelected')]
    public function onLabelSelected($labelId)
    {
        Log::info("=== LABEL SELECTED EVENT ===");
        Log::info("Label ID recibido: {$labelId}");
        Log::info("Tipo de dato: " . gettype($labelId));

        // Find the label name from the labels collection
        $labelName = '';
        $labelsCollection = $this->labels;

        if ($labelsCollection && count($labelsCollection) > 0) {
            $selectedLabel = collect($labelsCollection)->firstWhere('id', $labelId);
            if ($selectedLabel) {
                $labelName = is_array($selectedLabel) ? $selectedLabel['name'] : $selectedLabel->name;
            }
        }

        Log::info("Label encontrado: {$labelName}");

        // Handle different selection options
        if ($labelId == -1) {
            // "Programación" option - show all items without filter
            $this->selectedLabelId = null;
            $this->selectedLabelName = 'Programming';
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

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Filtro aplicado: ' . $this->selectedLabelName
        ]);

        $this->resetPage(); // Reset pagination when filter changes

        // Clear the computed property cache to force re-evaluation
        unset($this->orders);

        // Force Livewire to re-render
        $this->dispatch('$refresh');

        Log::info("selectedLabelId final: " . ($this->selectedLabelId ?? 'null'));
        Log::info("selectedLabelName final: " . $this->selectedLabelName);
        Log::info("=== FIN LABEL SELECTED EVENT ===");
    }

    public function clearFilters()
    {
        $this->ensureTenantConnection();
        $this->selectedLabelId = null;
        $this->selectedLabelName = 'Programming';
        $this->filterStatus = '';
        $this->filterNews = '';
        $this->filterPacking = '';
        $this->search = '';
        $this->selectedShipp = 0;
        $this->resetPage();
        
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Filtros restablecidos'
        ]);

        unset($this->orders);
        $this->dispatch('$refresh');
    }

    public function removeFromShipment($importId)
    {
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->beginTransaction();

            $import = ImpImports::findOrFail($importId);
            $oldStatus = $import->status;

            // Desvincular del pack y regresar a producción (status 5)
            $import->update([
                'packing_id' => null,
                'status' => 5
            ]);

            ImpStatusHistory::create([
                'import_id' => $import->id,
                'previous_state' => $oldStatus,
                'new_state' => 5,
                'user_id' => Auth::id()
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Producto sacado del envío y regresado a Producción'
            ]);

            unset($this->orders);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al sacar de envío: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo retirar el producto'
            ]);
        }
    }

    public function confirmDelete($importId)
    {
        $this->selectedOrderIdForDelete = $importId;
        $this->deleteJustification = '';
        $this->showModalDelete = true;
    }

    public function deleteOrderWithJustification()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'deleteJustification' => 'required'
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $import = ImpImports::findOrFail($this->selectedOrderIdForDelete);
            
            if ($import->news != 1) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Solo se pueden eliminar productos que tengan una novedad activa.'
                ]);
                return;
            }

            $oldStatus = $import->status;

            // Cambiar a status 11 (Eliminado) y guardar justificación
            $import->update([
                'status' => 11,
                'delete_justification' => $this->deleteJustification,
                'news' => 0
            ]);

            ImpStatusHistory::create([
                'import_id' => $import->id,
                'previous_state' => $oldStatus,
                'new_state' => 11,
                'user_id' => Auth::id()
            ]);

            DB::connection('tenant')->commit();

            $this->showModalDelete = false;
            $this->selectedOrderIdForDelete = null;
            $this->deleteJustification = '';

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Producto eliminado correctamente'
            ]);

            unset($this->orders);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al eliminar producto: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo eliminar el producto'
            ]);
        }
    }

    public $selectedLabel = [
        'id' => '',
        'name' => ''
    ];

    public function cancel()
    {
        $this->showModalHistory = false;
        $this->showModalChangeQuantity = false;
        $this->showModalAcceptNew = false;
        $this->showModalConfirmPrice = false;
        $this->showModalJustifyChangePrice = false;
        $this->showModalConfirmProduction = false;
        $this->showModalChangeQtyShip = false;
        $this->showModalShipping = false;
        $this->resetForm();
        $this->refreshCounter++;
    }

    public function getProfileUserProperty()
    {
        return Auth::user()?->profile_id;
    }

    public function updateQty($importId, $quantity)
    {
        $this->ensureTenantConnection();
        // 1. Buscamos el registro en la base de datos.
        $import = ImpImports::findOrFail($importId);

        // 2. La cantidad que tiene actualmente es la "anterior"
        $oldQty = $import->qty_requested;

        // 3. La cantidad que recibimos por parámetro es la "nueva" (la que se digitó)
        $newQty = $quantity;

        if ($oldQty == $newQty) {
            return;
        } else {
            $this->oldQty = $oldQty;
            $this->newQty = $newQty;
            $this->import_id = $importId;

            $this->showModalChangeQuantity = true;
        }
    }

    public function saveChangeQuantity()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'commentChangeQuantity' => 'required'
        ]);

        try {
            $import = ImpImports::findOrFail($this->import_id);

            $structuredData = json_encode([
                'type' => 'qty_change',
                'old' => $this->oldQty,
                'new' => $this->newQty,
                'note' => $this->commentChangeQuantity
            ]);

            $import->update([
                'qty_requested' => $this->newQty
            ]);

            // 3. Guardamos el comentario con un mensaje de éxito personalizado
            $customMessage = "¡Cantidad actualizada correctamente de {$this->oldQty} a {$this->newQty}!";
            $this->saveComment($this->import_id, $structuredData, $customMessage);

            $this->showModalChangeQuantity = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al actualizar cantidad: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar la cantidad'
            ]);
        }
    }

    public function updatePriceQ($importId, $price)
    {
        $this->ensureTenantConnection();
        try {
            $import = ImpImports::findOrFail($importId);

            $oldStatus = $import->status;

            if ($oldStatus == 2 || $oldStatus == 4 || $oldStatus == 6 || $oldStatus == 7) {
                $this->price = $price;
                $this->import_id = $importId;
                $this->showModalJustifyChangePrice = true;
            } else {
                $newStatus = 2;

                $dataStatus = [
                    'import_id' => $importId,
                    'previous_state' => $oldStatus,
                    'new_state' => $newStatus,
                    'user_id' => Auth::id()
                ];

                ImpStatusHistory::create($dataStatus);

                $import->update(['price' => $price, 'status' => 2]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Price update correctly'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error al actualizar precio: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar el precio'
            ]);
        }
    }

    public function openModalAcceptNew($import_id)
    {
        $this->import_id = $import_id;
        $this->showModalHistory = false;
        $this->showModalAcceptNew = true;
    }

    public function finishConversation()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'commentAccept' => 'required'
        ]);
        try {
            $import = ImpImports::findOrFail($this->import_id);
            $commentIniatiator = ImpComments::where('import_id', $this->import_id)->where('initiator', 1)->first();
            $dataFinish = [
                'import_id' => $this->import_id,
                'comment' => $this->commentAccept,
                'user_id' => Auth::id(),
                'initiator' => 0
            ];

            $commentIniatiator->update(['initiator' => 0]);
            ImpComments::create($dataFinish);

            $import->update(['news' => 0]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Novedad aceptada',
            ]);

            $this->showModalAcceptNew = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al finalizar la conversación: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo finalizar la conversación'
            ]);
        }
    }

    #[Computed]
    public function historyStatus()
    {
        if (!$this->import_id) {
            return collect();
        }

        $centralDbName = config('database.connections.central.database');
        return ImpStatusHistory::query()->with(['previousStatus', 'newStatus'])
            ->select('imp_status_history.created_at', 'imp_status_history.previous_state', 'imp_status_history.new_state', 'u.name')
            ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_status_history.user_id')
            ->where('imp_status_history.import_id', $this->import_id)
            ->orderBy('imp_status_history.created_at', 'DESC')
            ->get();
    }

    public function openModalConfirmPrice($importId)
    {
        $this->import_id = $importId;
        $this->showModalConfirmPrice = true;
    }

    #[Computed]
    public function infoPrice()
    {
        if (!$this->import_id) {
            return null;
        }

        $this->ensureTenantConnection();

        $import = DB::connection('tenant')
            ->table('imp_imports', 'i')
            ->join('inv_items as it', 'i.item_id', '=', 'it.id')
            ->select('it.internal_code', 'i.qty_requested', 'i.price')
            ->where('i.id', $this->import_id)
            ->first();

        return [
            'internal_code' => $import->internal_code,
            'qty_requested' => $import->qty_requested,
            'price' => $import->price
        ];
    }

    public function approvePrice($importId)
    {
        $this->ensureTenantConnection();
        try {
            $import = ImpImports::findOrFail($importId);

            $oldStatus = $import->status;

            $newStatus = 4;

            $dataStatus = [
                'import_id' => $importId,
                'previous_state' => $oldStatus,
                'new_state' => $newStatus,
                'user_id' => Auth::id()
            ];

            ImpStatusHistory::create($dataStatus);

            $import->update(['status' => $newStatus]);

            $this->showModalConfirmPrice = false;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Se actualizo el estado'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al aprobar el precio: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo aprobar el precio'
            ]);
        }
    }

    public function approvePricesInBatch()
    {
        $this->ensureTenantConnection();
        
        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Por favor, selecciona al menos una orden'
            ]);
            return;
        }

        try {
            DB::connection('tenant')->transaction(function () {
                $imports = ImpImports::whereIn('id', $this->selectedOrders)
                    ->whereIn('status', [1, 2]) // Solicitado o Cotizado
                    ->get();

                foreach ($imports as $import) {
                    $oldStatus = $import->status;
                    $newStatus = 4; // Aprobado / Producción

                    // Guardar historial de estado
                    ImpStatusHistory::create([
                        'import_id' => $import->id,
                        'previous_state' => $oldStatus,
                        'new_state' => $newStatus,
                        'user_id' => Auth::id()
                    ]);

                    $import->update(['status' => $newStatus]);
                }
            });

            $this->selectedOrders = []; // Limpiar selección
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Órdenes aprobadas en lote exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al aprobar en lote: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudieron aprobar las órdenes seleccionadas'
            ]);
        }
    }

    public function saveChangePrice()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'commentJustifyPrice' => 'required'
        ]);
        try {
            $import = ImpImports::findOrFail($this->import_id);

            $oldPrice = $import->price;

            $structuredData = json_encode([
                'type' => 'price_change',
                'old' => $oldPrice,
                'new' => $this->price,
                'note' => $this->commentJustifyPrice
            ]);

            $import->update([
                'price' => $this->price
            ]);

            $customMessage = "¡Precio actualizado y comentario registrado!";
            $this->saveComment($this->import_id, $structuredData, $customMessage);

            $this->showModalJustifyChangePrice = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al actualizar precio: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar el precio'
            ]);
        }
    }

    public function openModalConfirmProduction($importId)
    {
        $this->import_id = $importId;
        $this->showModalConfirmProduction = true;
    }

    public function saveSendProduction()
    {
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->transaction(function () {
                $import = ImpImports::findOrFail($this->import_id);

                $oldStatus = $import->status;

                $newStatus = 5;

                $dataStatus = [
                    'import_id' => $this->import_id,
                    'previous_state' => $oldStatus,
                    'new_state' => $newStatus,
                    'user_id' => Auth::id()
                ];

                ImpStatusHistory::create($dataStatus);

                $import->update(['status' => $newStatus, 'qty_shipped' => $import->qty_requested]);
            });
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Update production successfully'
            ]);
            $this->showModalConfirmProduction = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al enviar a producción: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo enviar a producción'
            ]);
        }
    }

    #[Computed]
    public function packings()
    {
        /*
        $this->ensureTenantConnection();
        return ImpPacking::select('id', 'number_packing')
            ->addSelect([
                'imports_count' => ImpImports::selectRaw('count(*)')
                    ->whereColumn('packing_id', 'imp_packing.id')
                    ->whereNull('deleted_at')
                    ->whereNull('shipping_id')
            ])
            ->where(function ($query) {
                $query->whereHas('imports', function ($q) {
                    $q->whereIn('status', [5, 6])->whereNull('deleted_at')
                        ->whereNull('shipping_id');
                })->orWhereDoesntHave('imports');
            })
            ->get();
        */
        return collect();
    }

    public function openModalHistory($import_id)
    {
        $this->import_id = $import_id;
        $this->showModalHistory = true;
    }

    /*
    public function togglePacking($packingId)
    {
        if (in_array($packingId, $this->selectedPackingIds)) {
            $this->selectedPackingIds = [];
            $this->dispatch('show-toast', [
                'type' => 'info',
                'message' => 'PACK deseleccionado'
            ]);
        } else {
            $this->selectedPackingIds = [$packingId];
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'PACK seleccionado correctamente'
            ]);
        }

        $this->resetPage();
    }

    public function putFilterPacking($packingId)
    {
        $this->filterPacking = $packingId;
        $this->showButtonShipping = true;
        $this->selectedPackingIds = [$packingId];
        Log::info('selectedPackingIds: ' . implode(', ', $this->selectedPackingIds));
    }

    #[Computed]
    public function infoPacking()
    {
        if (empty($this->selectedPackingIds)) {
            return collect();
        }

        return ImpPacking::whereIn('id', $this->selectedPackingIds)
            ->withCount('imports')
            ->get();
    }
    */

    public function updateQtyShip($importId, $qtyShip)
    {
        $this->ensureTenantConnection();

        // 1. Buscamos el registro en la base de datos.
        $import = ImpImports::findOrFail($importId);

        // 2. La cantidad que tiene actualmente es la "anterior"
        $oldQty = $import->qty_requested;

        // 3. La cantidad que recibimos por parámetro es la "nueva" (la que se digitó)
        $newQty = $qtyShip;

        if ($oldQty == $newQty) {
            return;
        } else {
            $this->oldQty = $oldQty;
            $this->newQty = $newQty;
            $this->import_id = $importId;

            $this->showModalChangeQtyShip = true;
        }
    }

    public function saveChangeQtyShip()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'commentChangeQtyShip' => 'required'
        ]);
        try {
            $import = ImpImports::findOrFail($this->import_id);

            $structuredData = json_encode([
                'type' => 'qty_change',
                'old' => $this->oldQty,
                'new' => $this->newQty,
                'note' => $this->commentChangeQtyShip
            ]);

            $import->update([
                'qty_shipped' => $this->newQty
            ]);

            ImpComments::create([
                'import_id' => $this->import_id,
                'comment' => $structuredData,
                'user_id' => Auth::id(),
                'initiator' => 0
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Success update quantity'
            ]);
            $this->showModalChangeQtyShip = false;
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al actualizar cantidad: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar la cantidad'
            ]);
        }
    }

    public function openModalShipping()
    {
        $this->ensureTenantConnection();
        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un producto'
            ]);
            return;
        }

        // Obtener productos seleccionados
        $imports = ImpImports::whereIn('id', $this->selectedOrders)->get();
        
        $hasMaritime = false;
        $hasAir = false;

        foreach ($imports as $import) {
            $prio = trim($import->priority);
            if (in_array($prio, ['ASAP', 'Second', 'Third'])) {
                $hasMaritime = true;
            } elseif (in_array($prio, ['Express', 'Express 2', 'Express 3'])) {
                $hasAir = true;
            }
        }

        if ($hasMaritime && $hasAir) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'You cannot mix Air (Express) and Maritime (ASAP/Second/Third) products in the same shipment.'
            ]);
            return;
        }

        if ($hasMaritime) {
            $this->way = 'Maritima';
            $this->lockWay = true;
        } elseif ($hasAir) {
            $this->way = 'Aerea';
            $this->lockWay = true;
        } else {
            $this->way = '';
            $this->lockWay = false;
        }

        $this->showModalShipping = true;
    }

    public function saveShippingData()
    {
        $this->ensureTenantConnection();
        
        if ($this->isExistingShipping) {
            $this->validate([
                'selectedExistingShippingId' => 'required'
            ]);
        } else {
            $this->validate([
                'etd' => 'required',
                'operation_number' => 'required',
                'way' => 'required'
            ]);
        }

        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un producto'
            ]);
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            if ($this->isExistingShipping) {
                // 1. Obtener el envío existente
                $existingShipping = ImpShippments::findOrFail($this->selectedExistingShippingId);
                
                // 2. Buscar si ya hay un packing asociado a este envío
                $packing = ImpPacking::where('shipping_id', $existingShipping->id)->first();
                
                // Si no hay, crear un pack asociado automáticamente
                if (!$packing) {
                    $lastPacking = ImpPacking::orderBy('id', 'desc')->first();
                    $nextNumber = 1;
                    if ($lastPacking) {
                        $lastNumber = (int) filter_var($lastPacking->number_packing, FILTER_SANITIZE_NUMBER_INT);
                        $nextNumber = $lastNumber + 1;
                    }
                    $packing = ImpPacking::create([
                        'number_packing' => 'PACK' . $nextNumber,
                        'shipping_id' => $existingShipping->id
                    ]);
                }
                
                $consecutiveText = $existingShipping->consecutive;
            } else {
                // 1. Crear el Shipment nuevo
                $lastConsecutive = ImpShippments::where('way', $this->way)->max('consecutive');
                $newConsecutive = $lastConsecutive ? $lastConsecutive + 1 : 1;
                $shippingData = [
                    'consecutive' => $newConsecutive,
                    'etd' => $this->etd,
                    'operation_number' => $this->operation_number,
                    'way' => $this->way,
                    'conveyor' => $this->conveyor,
                    'obs' => $this->observations
                ];
                $newShipping = ImpShippments::create($shippingData);

                // 2. Crear un PACK en segundo plano automáticamente para asociarle estos productos
                $lastPacking = ImpPacking::orderBy('id', 'desc')->first();
                $nextNumber = 1;
                if ($lastPacking) {
                    $lastNumber = (int) filter_var($lastPacking->number_packing, FILTER_SANITIZE_NUMBER_INT);
                    $nextNumber = $lastNumber + 1;
                }
                $newPackingName = 'PACK' . $nextNumber;
                
                $packing = ImpPacking::create([
                    'number_packing' => $newPackingName,
                    'shipping_id' => $newShipping->id // Asociar pack al envío
                ]);

                // 4. Crear un Pack disponible para futuros usos (como hacía el flujo original)
                $nextNumber++;
                ImpPacking::create([
                    'number_packing' => 'PACK' . $nextNumber
                ]);
                
                $consecutiveText = $newConsecutive;
            }

            // 3. Asociar los productos seleccionados al Pack y pasarlos a In Transit (status 7)
            $imports = ImpImports::whereIn('id', $this->selectedOrders)->get();
            foreach ($imports as $import) {
                $oldStatus = $import->status;

                $import->update([
                    'packing_id' => $packing->id,
                    'status' => 7
                ]);

                ImpStatusHistory::create([
                    'import_id' => $import->id,
                    'previous_state' => $oldStatus,
                    'new_state' => 7,
                    'user_id' => Auth::id()
                ]);
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Datos de envío asignados con éxito. Packing: ' . $packing->number_packing . ' Consecutivo: ' . $consecutiveText
            ]);

            $this->selectedOrders = [];
            $this->showModalShipping = false;
            $this->isExistingShipping = false;
            $this->selectedExistingShippingId = null;
            $this->resetForm();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al guardar la información: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo guardar la información del envío'
            ]);
        }
    }

    #[Computed]
    public function shippments()
    {
        $this->ensureTenantConnection();
        return ImpShippments::select(['id', DB::raw("CONCAT('ID=',consecutive,' - ', way) AS way")])
            ->get()
            ->toArray();
    }

    public function render()
    {
        $labels = $this->labels;
        return view(
            'livewire.tenant.imports.orders',
            [
                'labels' => $labels,
                'profileUser' => $this->getProfileUserProperty()
            ]
        )
            ->layout('layouts.app', ['header' => 'Gestión de Ordenes']);
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return $this->redirectRoute('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return $this->redirectRoute('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    public function rotatePriorities()
    {
        $this->ensureTenantConnection();

        if (empty($this->selectedOrders)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Please select the products you wish to receive.'
            ]);
            return;
        }

        try {
            // Detectar qué tipo de prioridades tienen los productos seleccionados
            $selectedImports = ImpImports::whereIn('id', $this->selectedOrders)->get();
            $hasMaritime = false;
            $hasAir = false;

            foreach ($selectedImports as $import) {
                $prio = trim($import->priority);
                if (in_array($prio, ['ASAP', 'Second', 'Third'])) {
                    $hasMaritime = true;
                } elseif (in_array($prio, ['Express', 'Express 2', 'Express 3'])) {
                    $hasAir = true;
                }
            }

            DB::connection('tenant')->transaction(function () use ($selectedImports, $hasMaritime, $hasAir) {
                // 1. Mover los productos seleccionados a Recibido (status 8)
                foreach ($selectedImports as $import) {
                    $oldStatus = $import->status;
                    $import->update(['status' => 8]);

                    ImpStatusHistory::create([
                        'import_id' => $import->id,
                        'previous_state' => $oldStatus,
                        'new_state' => 8,
                        'user_id' => Auth::id()
                    ]);
                }

                // 2. Rotar prioridades de los productos restantes (status < 8) de forma independiente
                if ($hasMaritime) {
                    // - ASAP pasa a null (por si queda alguno)
                    ImpImports::where('priority', 'ASAP')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => null,
                            'priority_assigned_at' => null
                        ]);

                    // - Second pasa a ASAP
                    ImpImports::where('priority', 'Second')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => 'ASAP',
                            'priority_assigned_at' => now()
                        ]);

                    // - Third pasa a Second
                    ImpImports::where('priority', 'Third')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => 'Second',
                            'priority_assigned_at' => now()
                        ]);
                }

                if ($hasAir) {
                    // - Express pasa a null (por si queda alguno)
                    ImpImports::where('priority', 'Express')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => null,
                            'priority_assigned_at' => null
                        ]);

                    // - Express 2 pasa a Express
                    ImpImports::where('priority', 'Express 2')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => 'Express',
                            'priority_assigned_at' => now()
                        ]);

                    // - Express 3 pasa a Express 2
                    ImpImports::where('priority', 'Express 3')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->update([
                            'priority' => 'Express 2',
                            'priority_assigned_at' => now()
                        ]);
                }
            });

            $this->selectedOrders = [];

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Products received and priorities rotated successfully.'
            ]);

            unset($this->orders);
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error("Error al recibir productos y rotar: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Could not process reception and rotation'
            ]);
        }
    }
    public function assignPriorityToSelectedOrders($priority)
    {
        try {
            if (empty($this->selectedOrders)) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No hay órdenes seleccionadas'
                ]);
                return;
            }

            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () use ($priority) {
                ImpImports::whereIn('id', $this->selectedOrders)
                    ->update([
                        'priority' => $priority,
                        'priority_assigned_at' => $priority ? now() : null,
                        'user_id' => Auth::id()
                    ]);
            });

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Prioridades actualizadas en lote exitosamente'
            ]);

            $this->selectedOrders = [];
            $this->selectAll = false;
            unset($this->orders);
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error('Error al asignar prioridades en lote a las órdenes: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al asignar las prioridades: ' . $e->getMessage()
            ]);
        }
    }

    private function resetForm()
    {
        $this->commentChangeQuantity = '';
        $this->oldQty = '';
        $this->newQty = '';
        $this->import_id = '';
        $this->price = '';
        $this->commentAccept = '';
        $this->commentChangeQtyShip = '';
        $this->etd = '';
        $this->operation_number = '';
        $this->way = '';
        $this->conveyor = '';
        $this->observations = '';
    }
}
