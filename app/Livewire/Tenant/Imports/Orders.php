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
    public $refreshCounter = 0;
    public $selectAll = false;

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
        'testEvent' => 'testEvent',
    ];

    protected $rules = [
        'commentChangeQuantity' => 'required',
        'commentAccept' => 'required',
        'commentChangeQtyShip' => 'required',
        'commentJustifyPrice' => 'required'
    ];

    protected $messages = [
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
        // Primera consulta: Registros agrupados por estado
        $estados = DB::connection('tenant')
            ->table('imp_imports as i')
            ->rightJoin('imp_status as s', 'i.status', '=', 's.id')
            ->select('s.name as nombre_estado', 's.translated_name', DB::raw('COUNT(i.id) as cantidad'), 's.id as id')
            ->groupBy('s.id', 's.name', 's.translated_name');

        // Segunda consulta: Solo novedades
        $novedades = DB::connection('tenant')
            ->table('imp_imports as i')
            ->select(
                DB::raw("'Novedades' as nombre_estado"),
                DB::raw("'Novedades' as translated_name"),
                DB::raw('COUNT(i.id) as cantidad'),
                DB::raw("10 as id")
            )
            ->where('i.news', 1)
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
                'pk.number_packing as packing_number',
                's.operation_number',
                's.etd',
                DB::raw("CONCAT('#',s.consecutive,' ', s.way) AS way"),
                DB::raw("(SELECT comment 
                        FROM imp_comments 
                        WHERE import_id = i.id 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) AS ultimo_comentario")
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
    }

    public function openModalHistory($import_id)
    {
        $this->import_id = $import_id;
        $this->showModalHistory = true;
    }

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
        $this->showModalShipping = true;
    }

    public function saveShippingData()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'etd' => 'required',
            'operation_number' => 'required',
            'way' => 'required'
        ]);
        try {
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

            // Registro de la información de envio
            $newShipping = ImpShippments::create($shippingData);

            // Busqueda del packing seleccionado
            $packing = ImpPacking::findOrFail($this->filterPacking);

            // Modificación del campo shipping_id en la tabla imp_packing 
            $packing->update(['shipping_id' => $newShipping->id]);

            //Busqueda de las importaciones asociadas al packing seleccionado
            $imports = ImpImports::where('packing_id', $this->filterPacking);

            // Cambio de estado a "En transito"
            $imports = ImpImports::where('packing_id', $this->filterPacking)->get();

            foreach ($imports as $import) {
                $oldStatus = $import->status;

                $import->update(['status' => 7]);

                ImpStatusHistory::create([
                    'import_id' => $import->id,
                    'previous_state' => $oldStatus,
                    'new_state' => 7,
                    'user_id' => Auth::id()
                ]);
            }

            // Crear el NUEVO PACK
            $lastPacking = ImpPacking::orderBy('id', 'desc')->first();
            $nextNumber = 1;

            if ($lastPacking) {
                $lastNumber = (int) filter_var($lastPacking->number_packing, FILTER_SANITIZE_NUMBER_INT);
                $nextNumber = $lastNumber + 1;
            }

            $newPackingName = 'PACK' . $nextNumber;

            ImpPacking::create([
                'number_packing' => $newPackingName
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Shipping data assigned successfully. Packing processed: ' . $packing->number_packing . 'Consecutive: ' . $newConsecutive
            ]);
            $this->showModalShipping = false;
            $this->resetForm();
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error("Error al guardar la información: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Shipping information could not be saved'
            ]);
        }
    }

    #[Computed]
    public function shippments()
    {
        $this->ensureTenantConnection();
        return ImpShippments::select(['id', DB::raw("CONCAT('ID=',consecutive,' - ', way) AS way")])->get();
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
        try {
            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () {
                // 1. ASAP pasa a null
                ImpImports::where('priority', 'ASAP')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => null,
                        'priority_assigned_at' => null
                    ]);

                // 2. Second pasa a ASAP
                ImpImports::where('priority', 'Second')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => 'ASAP',
                        'priority_assigned_at' => now()
                    ]);

                // 3. Third pasa a Second
                ImpImports::where('priority', 'Third')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => 'Second',
                        'priority_assigned_at' => now()
                    ]);
            });

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Prioridades rotadas correctamente'
            ]);

            unset($this->orders);
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Log::error("Error al rotar prioridades: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudieron rotar las prioridades'
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
