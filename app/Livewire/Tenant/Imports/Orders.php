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
use App\Models\Tenant\Imports\ImpShipmentComments;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Orders extends Component
{
    use WithPagination;
    use \Livewire\WithFileUploads;

    // Propiedades para Producto Nuevo (New Product)
    public $showModalCreateNewProduct = false;
    public $newProductCode;
    public $newProductDescription;
    public $newProductPorcentaje = 0;
    public $newProductMinQty = 1;
    public $newProductFactor = 0;
    public $newProductSupplierId;
    public $newProductFactoryRef;
    public $newProductImage; // Para cargar la foto
    
    // Factores de precio y descuento
    public $newProductExw = 0;
    public $newProductIncrFletes = 0;
    public $newProductPvp1 = 0;
    public $newProductPvpMin = 0;
    
    // Parámetros WordPress
    public $newProductStockWordpress;
    public $newProductMinQtyWordpress;

    // Propiedades para conversión de Producto Nuevo a Real (Camilo)
    public $showModalConvertNewProduct = false;
    public $selectedNewProductId;
    public $finalInternalCode;
    public $finalSku;
    public $finalCategoryId;
    public $finalType;
    public $finalTaxId;
    public $finalBrandId;
    public $finalHouseId;
    public $finalPurchasingUnit;
    public $finalConsumptionUnit;
    public $finalManageSerial;
    public $finalInventoriable;
    public $finalDescription;
    public $finalStockWordpress;
    public $finalMinQtyWordpress;
    public $finalSupplierId;
    public $tempValues = [];

    // Propiedades para ordenar productos convertidos en lote
    public $selectedConvertedIds = [];
    public $orderQuantities = [];

    public $filterStatus = '';
    public $filterNews = '';
    public $filterPacking = '';
    public $filterPriority = '';
    public $search = '';
    public $perPage = 10;
    public $selectedOrders = [];
    public $selectedPackingIds = [];
    public $selectedLabelId = null;
    public $selectedLabelName = 'Programming';
    public $selectedShipp = 0;
    public $allLabels = [];
    public $showModalHistory = false;
    public $shipmentComment = '';
    public $showModalShipmentHistory = false;
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
    public $tempEtd = '';
    public $tempConveyor = '';

    // Propiedades para recibir envío y editar fechas
    public $showModalMarkReceived = false;
    public $receivedEta = '';
    public $receivedFervicomArrival = '';
    public $shipmentEta = '';
    public $shipmentFervicomArrival = '';

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
        // Primera consulta: Registros agrupados por estado, filtrados por proveedor si corresponde (excluyendo el estado 13)
        $estados = DB::connection('tenant')
            ->table('imp_imports as i')
            ->leftJoin('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->rightJoin('imp_status as s', 'i.status', '=', 's.id')
            ->where('s.id', '!=', 6)
            ->where('s.id', '!=', 13)
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
                DB::raw(Auth::user()->profile_id == 17 ? "'Issues' as translated_name" : "'Novedades' as translated_name"),
                DB::raw('COUNT(i.id) as cantidad'),
                DB::raw("10 as id")
            )
            ->where('i.news', 1)
            ->when(Auth::user()->profile_id == 17, function ($query) {
                return $query->where('iis.supplier_id', Auth::id());
            })
            ->groupBy('i.news');

        // Unir las consultas, ejecutar y ordenar de forma personalizada en PHP
        $statuses = $estados
            ->unionAll($novedades)
            ->get();

        // Agregar de forma dinámica el estado 13 (New Product) contando la tabla imp_new_products
        $status13 = DB::connection('tenant')->table('imp_status')->where('id', 13)->first();
        if ($status13) {
            $newProductsCount = DB::connection('tenant')
                ->table('imp_new_products')
                ->whereNull('deleted_at')
                ->where('status', '=', 'PENDING')
                ->when(Auth::user()->profile_id == 17, function ($query) {
                    return $query->where('supplier_id', Auth::id());
                })
                ->count();

            $statuses->push((object)[
                'nombre_estado' => $status13->name,
                'translated_name' => $status13->translated_name,
                'cantidad' => $newProductsCount,
                'id' => 13
            ]);
        }

        // Agregar de forma dinámica el estado 14 (Converted Products) para Camilo/Fervicom
        if (Auth::user()->profile_id != 17) {
            $convertedProductsCount = DB::connection('tenant')
                ->table('imp_new_products')
                ->whereNull('deleted_at')
                ->where('status', '=', 'CONVERTED')
                ->count();

            $statuses->push((object)[
                'nombre_estado' => 'Producto Nuevo',
                'translated_name' => 'Converted',
                'cantidad' => $convertedProductsCount,
                'id' => 14
            ]);
        }

        $customOrder = [
            1 => 1,   // Solicitado
            2 => 2,   // Cotizado
            4 => 3,   // Aprobado
            5 => 4,   // Produccion
            12 => 5,  // Terminados
            7 => 6,   // En transito
            8 => 7,   // Recibido
            9 => 8,   // Retrasado
            10 => 9,  // Novedades
            11 => 10, // Eliminado
            13 => 11  // New Product
        ];

        return $statuses->sortBy(function ($item) use ($customOrder) {
            return $customOrder[$item->id] ?? 999;
        })->values();
    }

    #[Computed]
    public function taxes()
    {
        return DB::connection('tenant')->table('cnf_taxes')->where('status', 1)->get();
    }

    #[Computed]
    public function brands()
    {
        return DB::connection('tenant')->table('inv_values')->where('type', 'brands')->get();
    }

    #[Computed]
    public function houses()
    {
        return DB::connection('tenant')->table('inv_values')->where('type', 'houses')->get();
    }

    #[Computed]
    public function units()
    {
        return DB::connection('tenant')->table('inv_values')->where('type', 'units')->get();
    }

    public function putFilter($statusId)
    {
        $this->filterPriority = ''; // Limpiar filtro de prioridad al cambiar de pestaña superior
        // No resetear el envío seleccionado si se navega entre "En tránsito" (7) y "Recibido" (8)
        if (!in_array($statusId, [7, 8]) || !in_array($this->filterStatus, [7, 8])) {
            $this->selectedShipp = 0;
            $this->shipmentEta = '';
            $this->shipmentFervicomArrival = '';
        }

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
    public function productsToReceiveCount()
    {
        $this->ensureTenantConnection();
        if ($this->selectedShipp > 0) {
            return \App\Models\Tenant\Imports\ImpImports::where('status', 7)
                ->whereNull('deleted_at')
                ->whereHas('packing', function ($q) {
                    $q->where('shipping_id', $this->selectedShipp);
                })
                ->count();
        }
        return 0;
    }

    #[Computed]
    public function orders()
    {
        $centralDbName = config('database.connections.central.database');

        if ($this->filterStatus == 13) {
            return DB::connection('tenant')
                ->table('imp_new_products as inp')
                ->select([
                    'inp.id',
                    DB::raw('NULL as item_id'),
                    DB::raw("CONCAT(inp.code, ' - ', inp.description) AS item"),
                    'inp.factory_ref',
                    'inp.exw',
                    'inp.min_qty_supplier as qty_requested',
                    DB::raw("'N/A' AS label"),
                    DB::raw("'New Product' AS translated_name"),
                    DB::raw('13 AS status'),
                    DB::raw('NULL AS priority'),
                    DB::raw('NULL as priority_assigned_at'),
                    DB::raw('0 as qty_shipped'),
                    DB::raw('0 as news'),
                    'inp.exw as price',
                    DB::raw('NULL as delete_justification'),
                    DB::raw('NULL as packing_number'),
                    DB::raw('NULL as operation_number'),
                    DB::raw('NULL as etd'),
                    DB::raw('NULL as way'),
                    DB::raw("(SELECT comment 
                            FROM imp_comments 
                            WHERE new_product_id = inp.id 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        ) AS ultimo_comentario"),
                    DB::raw('NULL as received_at'),
                    DB::raw('NULL as deleted_by_user'),
                    'inp.image_path'
                ])
                ->whereNull('inp.deleted_at')
                ->where('inp.status', '=', 'PENDING')
                ->when(Auth::user()->profile_id == 17, function ($query) {
                    return $query->where('inp.supplier_id', Auth::id());
                })
                ->when($this->search, function ($query) {
                    $words = array_filter(explode(' ', trim($this->search)));
                    foreach ($words as $word) {
                        $query->where(function ($q) use ($word) {
                            $q->where('inp.description', 'like', '%' . $word . '%')
                              ->orWhere('inp.code', 'like', '%' . $word . '%')
                              ->orWhere('inp.factory_ref', 'like', '%' . $word . '%');
                        });
                    }
                    return $query;
                })
                ->paginate($this->perPage);
        }

        if ($this->filterStatus == 14) {
            return DB::connection('tenant')
                ->table('imp_new_products as inp')
                ->select([
                    'inp.id',
                    'inp.real_item_id as item_id',
                    DB::raw("CONCAT(iv.internal_code, ' - ', iv.name) AS item"),
                    'inp.factory_ref',
                    'inp.exw',
                    'inp.min_qty_supplier as qty_requested',
                    DB::raw("'N/A' AS label"),
                    DB::raw("'Converted' AS translated_name"),
                    DB::raw('14 AS status'),
                    DB::raw('NULL AS priority'),
                    DB::raw('NULL as priority_assigned_at'),
                    DB::raw('0 as qty_shipped'),
                    DB::raw('0 as news'),
                    'inp.exw as price',
                    DB::raw('NULL as delete_justification'),
                    DB::raw('NULL as packing_number'),
                    DB::raw('NULL as operation_number'),
                    DB::raw('NULL as etd'),
                    DB::raw('NULL as way'),
                    DB::raw("(SELECT comment 
                            FROM imp_comments 
                            WHERE new_product_id = inp.id 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        ) AS ultimo_comentario"),
                    DB::raw('NULL as received_at'),
                    DB::raw('NULL as deleted_by_user'),
                    'inp.image_path'
                ])
                ->leftJoin('inv_items as iv', 'inp.real_item_id', '=', 'iv.id')
                ->whereNull('inp.deleted_at')
                ->where('inp.status', '=', 'CONVERTED')
                ->when($this->search, function ($query) {
                    $words = array_filter(explode(' ', trim($this->search)));
                    foreach ($words as $word) {
                        $query->where(function ($q) use ($word) {
                            $q->where('inp.description', 'like', '%' . $word . '%')
                              ->orWhere('inp.code', 'like', '%' . $word . '%')
                              ->orWhere('inp.factory_ref', 'like', '%' . $word . '%');
                        });
                    }
                    return $query;
                })
                ->paginate($this->perPage);
        }


        return DB::connection('tenant')
            ->table('imp_imports as i')
            ->select([
                'i.id',
                'i.item_id',
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
            ->when($this->filterPriority, function ($query) {
                return $query->where('i.priority', $this->filterPriority);
            })
            ->when($this->filterPacking, function ($query) {
                return $query->where('i.packing_id', $this->filterPacking);
            })
            ->when($this->selectedShipp > 0, function ($query) {
                return $query->where('pk.shipping_id', $this->selectedShipp);
            })
            ->when($this->search, function ($query) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('iv.name', 'like', '%' . $word . '%')
                          ->orWhere('iv.sku', 'like', '%' . $word . '%')
                          ->orWhere('iv.internal_code', 'like', '%' . $word . '%')
                          ->orWhere('iis.factory_ref', 'like', '%' . $word . '%')
                          ->orWhere('pk.number_packing', 'like', '%' . $word . '%')
                          ->orWhere('s.operation_number', 'like', '%' . $word . '%')
                          ->orWhere('i.priority', 'like', '%' . $word . '%')
                          ->orWhere('il.name', 'like', '%' . $word . '%');
                    });
                }
                return $query;
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

        if ($this->filterStatus == 13) {
            try {
                $query = ImpComments::where('new_product_id', $idImport)->where('initiator', 1)->first();
                $initiatorExists = !is_null($query);
                
                ImpComments::create([
                    'new_product_id' => $idImport,
                    'comment' => $comment,
                    'user_id' => Auth::id(),
                    'initiator' => $initiatorExists ? 0 : 1
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => $toastMessage
                ]);
                $this->dispatch('$refresh');
            } catch (\Exception $e) {
                Log::error('❌ Error al guardar comentario de producto nuevo: ' . $e->getMessage());
            }
            return;
        }

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

    private function translateText($text, $from, $to)
    {
        try {
            $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . $from . "&tl=" . $to . "&dt=t&q=" . urlencode($text);
            $response = \Illuminate\Support\Facades\Http::get($url);
            
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result[0]) && is_array($result[0])) {
                    $translatedText = '';
                    foreach ($result[0] as $segment) {
                        if (isset($segment[0]) && is_string($segment[0])) {
                            $translatedText .= $segment[0];
                        }
                    }
                    return $translatedText;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al traducir comentario automático: " . $e->getMessage());
        }
        return null;
    }

    public function saveShipmentComment()
    {
        $this->ensureTenantConnection();
        if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('imp_shipment_comments')) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'La tabla de comentarios del envío no existe en la BD.'
            ]);
            return;
        }

        $targetShipmentId = $this->selectedShipp > 0 ? $this->selectedShipp : ($this->filterPacking ?: ($this->selectedShippmentData->id ?? null));
        if (!$targetShipmentId || trim($this->shipmentComment) === '') {
            return;
        }

        try {
            $originalComment = trim($this->shipmentComment);
            $finalComment = $originalComment;

            $profileId = Auth::user()?->profile_id;
            if ($profileId == 17) {
                // Riyi: Traducir de inglés a español
                $translated = $this->translateText($originalComment, 'en', 'es');
                if ($translated) {
                    $finalComment = $originalComment . "[TRANSLATED]" . $translated;
                }
            } else {
                // Fervicom: Traducir de español a inglés
                $translated = $this->translateText($originalComment, 'es', 'en');
                if ($translated) {
                    $finalComment = $originalComment . "[TRANSLATED]" . $translated;
                }
            }

            ImpShipmentComments::create([
                'shipment_id' => $targetShipmentId,
                'comment' => $finalComment,
                'user_id' => Auth::id(),
            ]);

            $this->shipmentComment = '';
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Comentario del envío registrado'
            ]);
            $this->dispatch('$refresh');
            $this->dispatch('scroll-to-bottom');
        } catch (\Exception $e) {
            Log::error('Error al guardar comentario del envío: ' . $e->getMessage());
        }
    }

    public function openModalShipmentHistory()
    {
        $this->showModalShipmentHistory = true;
        $this->dispatch('scroll-to-bottom');
    }

    #[Computed]
    public function shipmentComments()
    {
        $targetShipmentId = $this->selectedShipp > 0 ? $this->selectedShipp : ($this->filterPacking ?: ($this->selectedShippmentData->id ?? null));
        if (!$targetShipmentId) {
            return collect();
        }

        $this->ensureTenantConnection();
        if (!\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('imp_shipment_comments')) {
            return collect();
        }

        $centralDbName = config('database.connections.central.database');

        return ImpShipmentComments::query()
            ->select('imp_shipment_comments.created_at', 'imp_shipment_comments.comment', 'u.name')
            ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_shipment_comments.user_id')
            ->where('imp_shipment_comments.shipment_id', $targetShipmentId)
            ->orderBy('imp_shipment_comments.created_at', 'ASC')
            ->get();
    }

    #[Computed]
    public function timelineEvents()
    {
        if (!$this->import_id) {
            return collect();
        }

        $centralDbName = config('database.connections.central.database');

        if ($this->filterStatus == 13) {
            $comments = ImpComments::query()
                ->select('imp_comments.created_at', 'imp_comments.comment', 'u.name')
                ->join("{$centralDbName}.users as u", 'u.id', '=', 'imp_comments.user_id')
                ->where('imp_comments.new_product_id', $this->import_id)
                ->get()
                ->map(function ($item) {
                    $item->event_type = 'comment';
                    return $item;
                });

            return $comments->sortBy('created_at');
        }

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
        return $comments->concat($statuses)->sortBy('created_at');
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

        if ($this->selectedShipp > 0) {
            $sh = \App\Models\Tenant\Imports\ImpShippments::find($this->selectedShipp);
            if ($sh) {
                $this->shipmentEta = $sh->eta;
                $this->shipmentFervicomArrival = $sh->fervicom_arrival_date;
                $this->tempEtd = $sh->etd;
                $this->tempConveyor = $sh->conveyor;
            }
        } else {
            $this->shipmentEta = '';
            $this->shipmentFervicomArrival = '';
            $this->tempEtd = '';
            $this->tempConveyor = '';
        }

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

    #[Computed]
    public function selectedShippmentWeight()
    {
        if ($this->selectedShipp > 0) {
            $this->ensureTenantConnection();
            
            $packingIds = \App\Models\Tenant\Imports\ImpPacking::where('shipping_id', $this->selectedShipp)->pluck('id');
            
            if ($packingIds->isNotEmpty()) {
                $imports = \App\Models\Tenant\Imports\ImpImports::whereIn('packing_id', $packingIds)
                    ->whereNull('deleted_at')
                    ->get();
                
                $totalWeight = 0;
                foreach ($imports as $import) {
                    $qty = (int)$import->qty_requested;
                    $item = \App\Models\Tenant\Items\Items::with('dimensions')->find($import->item_id);
                    if ($item && $item->dimensions) {
                        $unitWeight = (float)$item->dimensions->weight;
                        $totalWeight += ($unitWeight * $qty);
                    }
                }
                return $totalWeight;
            }
        }
        return 0;
    }

    public function updatedSelectedShipp($value)
    {
        if ($value > 0) {
            $this->ensureTenantConnection();
            $sh = \App\Models\Tenant\Imports\ImpShippments::find($value);
            if ($sh) {
                $this->shipmentEta = $sh->eta;
                $this->shipmentFervicomArrival = $sh->fervicom_arrival_date;
                $this->tempEtd = $sh->etd;
                $this->tempConveyor = $sh->conveyor;
            }
        } else {
            $this->shipmentEta = '';
            $this->shipmentFervicomArrival = '';
            $this->tempEtd = '';
            $this->tempConveyor = '';
        }
    }

    public function openMarkReceivedModal()
    {
        if ($this->selectedShipp > 0 && $this->selectedShippmentData) {
            $this->receivedEta = $this->selectedShippmentData->eta ?: '';
            $this->receivedFervicomArrival = $this->selectedShippmentData->fervicom_arrival_date ?: '';
            $this->showModalMarkReceived = true;
        } else {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Por favor selecciona un envío primero.'
            ]);
        }
    }

    public function markShipmentAsReceived()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'receivedEta' => 'required|date',
            'receivedFervicomArrival' => 'required|date',
        ]);

        try {
            $importsToSync = [];
            DB::connection('tenant')->transaction(function () use (&$importsToSync) {
                $shipmentId = $this->selectedShipp;
                $sh = \App\Models\Tenant\Imports\ImpShippments::findOrFail($shipmentId);
                $sh->update([
                    'eta' => $this->receivedEta,
                    'fervicom_arrival_date' => $this->receivedFervicomArrival,
                ]);

                // Obtener imports de este envío a través del packing_id
                $importIds = DB::connection('tenant')
                    ->table('imp_imports as i')
                    ->join('imp_packing as pk', 'i.packing_id', '=', 'pk.id')
                    ->where('pk.shipping_id', $shipmentId)
                    ->pluck('i.id');

                if (count($importIds) > 0) {
                    // Registrar el cambio en la historia de cada uno
                    foreach ($importIds as $importId) {
                        $import = ImpImports::find($importId);
                        if ($import) {
                            $oldStatus = $import->status;
                            ImpStatusHistory::create([
                                'import_id' => $importId,
                                'previous_state' => $oldStatus,
                                'new_state' => 8, // Recibido
                                'user_id' => Auth::id()
                            ]);
                            $import->update(['status' => 8]);
                            $importsToSync[] = $import;

                            // Afectación automática del stock
                            $principalStore = \App\Models\Tenant\Items\InvStore::where('status', 1)
                                ->orderBy('id', 'asc')
                                ->first();

                            if ($principalStore) {
                                $itemStore = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $import->item_id)
                                    ->where('storeId', $principalStore->id)
                                    ->first();

                                $qtyToAdd = $import->qty_shipped;

                                if ($itemStore) {
                                    $itemStore->stock_items_store += $qtyToAdd;
                                    $itemStore->save();
                                } else {
                                    \App\Models\Tenant\Items\InvItemsStore::create([
                                        'itemId' => $import->item_id,
                                        'storeId' => $principalStore->id,
                                        'stock_items_store' => $qtyToAdd,
                                        'initial_stock' => 0.00,
                                        'wp_stock_percentage' => 100,
                                    ]);
                                }
                            }
                        }
                    }
                }
            });

            // Sincronizar con Alegra fuera de la transacción
            $this->syncReceivedItemsToAlegra($importsToSync);

            $this->filterStatus = 8;
            $this->shipmentEta = $this->receivedEta;
            $this->shipmentFervicomArrival = $this->receivedFervicomArrival;
            $this->showModalMarkReceived = false;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'El envío y todos sus productos han sido marcados como Recibidos.'
            ]);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error("Error al marcar el envío como recibido: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Ocurrió un error al procesar la recepción.'
            ]);
        }
    }

    public function updateShipmentDates()
    {
        if ($this->profileUser == '17') {
            return;
        }

        if ($this->selectedShipp > 0) {
            $this->ensureTenantConnection();
            try {
                $sh = \App\Models\Tenant\Imports\ImpShippments::findOrFail($this->selectedShipp);
                $sh->update([
                    'eta' => $this->shipmentEta ?: null,
                    'fervicom_arrival_date' => $this->shipmentFervicomArrival ?: null,
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Fechas actualizadas correctamente'
                ]);
            } catch (\Exception $e) {
                Log::error("Error al actualizar fechas de envío: " . $e->getMessage());
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No se pudieron actualizar las fechas.'
                ]);
            }
        }
    }

    public function confirmEditField($field)
    {
        $this->ensureTenantConnection();
        $newValue = $field === 'etd' ? $this->tempEtd : $this->tempConveyor;
        
        $isSupplier = $this->profileUser == '17';
        
        if ($field === 'etd') {
            $title = $isSupplier ? 'Change ETD (Departure)' : 'Cambiar ETD (Salida)';
            $text = $isSupplier ? 'A justification is required to save this change:' : 'Se requiere una justificación para guardar este cambio:';
            $placeholder = $isSupplier ? 'Enter justification to change departure date...' : 'Ingrese la justificación para cambiar la fecha de salida...';
        } else {
            $title = $isSupplier ? 'Change Conveyor / Transport' : 'Cambiar Transportador';
            $text = $isSupplier ? 'A justification is required to save this change:' : 'Se requiere una justificación para guardar este cambio:';
            $placeholder = $isSupplier ? 'Enter justification to change conveyor...' : 'Ingrese la justificación para cambiar el transportador...';
        }
        
        $this->dispatch('confirm-shipment-edit', [
            'field' => $field,
            'newValue' => $newValue,
            'title' => $title,
            'text' => $text,
            'placeholder' => $placeholder,
            'isSupplier' => $isSupplier
        ]);
    }

    public function updateShipmentField($field, $newValue, $justification)
    {
        if ($this->selectedShipp > 0) {
            $this->ensureTenantConnection();
            try {
                $sh = \App\Models\Tenant\Imports\ImpShippments::findOrFail($this->selectedShipp);
                
                $oldValue = $field === 'etd' ? $sh->etd : $sh->conveyor;
                
                if ($field === 'etd') {
                    $sh->update(['etd' => $newValue ?: null]);
                    $this->tempEtd = $newValue;
                    $fieldName = 'ETD (Salida)';
                    $formattedOld = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('d/m/Y') : 'N/A';
                    $formattedNew = $newValue ? \Carbon\Carbon::parse($newValue)->format('d/m/Y') : 'N/A';
                } else {
                    $sh->update(['conveyor' => $newValue ?: null]);
                    $this->tempConveyor = $newValue;
                    $fieldName = 'Transportador';
                    $formattedOld = $oldValue ?: 'N/A';
                    $formattedNew = $newValue ?: 'N/A';
                }

                // Guardar la justificación en imp_shipment_comments
                $commentText = "Se modificó el campo [{$fieldName}] de '{$formattedOld}' a '{$formattedNew}'. Justificación: {$justification}";
                
                ImpShipmentComments::create([
                    'shipment_id' => $this->selectedShipp,
                    'comment' => $commentText,
                    'user_id' => Auth::id()
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Campo actualizado y justificación registrada con éxito.'
                ]);
                $this->dispatch('$refresh');
            } catch (\Exception $e) {
                Log::error("Error al actualizar campo del envío: " . $e->getMessage());
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No se pudo actualizar el campo.'
                ]);
            }
        }
    }

    public function cancelShipmentEdit()
    {
        if ($this->selectedShipp > 0) {
            $sh = \App\Models\Tenant\Imports\ImpShippments::find($this->selectedShipp);
            if ($sh) {
                $this->tempEtd = $sh->etd;
                $this->tempConveyor = $sh->conveyor;
            }
        }
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
        $this->filterPriority = '';
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

    public function changeShipmentQuantity($importId, $newQty)
    {
        $newQty = (int)$newQty;
        if ($newQty < 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'La cantidad no puede ser menor a 0'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->beginTransaction();

            $import = ImpImports::findOrFail($importId);
            $qtyRequested = (int)$import->qty_requested;
            $oldStatus = $import->status;

            if ($newQty === 0) {
                // Caso A: Cantidad ingresada es cero - regresa el ítem completo a Producción (status 5)
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

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Producto sacado del envío y regresado a Producción.'
                ]);

            } elseif ($newQty < $qtyRequested) {
                // Caso B: Cantidad inferior - actualiza el actual y devuelve el excedente a Producción
                $remainingQty = $qtyRequested - $newQty;

                $import->update([
                    'qty_requested' => $newQty
                ]);

                // Buscar si el producto ya tiene un registro en Producción (status 5 sin packing asignado)
                $existingImport = ImpImports::where('item_id', $import->item_id)
                    ->where('status', 5)
                    ->whereNull('packing_id')
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingImport) {
                    $existingImport->increment('qty_requested', $remainingQty);

                    ImpStatusHistory::create([
                        'import_id' => $existingImport->id,
                        'previous_state' => null,
                        'new_state' => 5,
                        'user_id' => Auth::id()
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => "Cantidad reducida a {$newQty} en tránsito. Se sumaron {$remainingQty} unidades a la solicitud existente en Producción."
                    ]);
                } else {
                    $newImport = ImpImports::create([
                        'item_id' => $import->item_id,
                        'user_id' => $import->user_id,
                        'label_id' => $import->label_id,
                        'qty_requested' => $remainingQty,
                        'price' => $import->price,
                        'status' => 5, // Producción
                        'packing_id' => null,
                        'priority' => $import->priority,
                        'priority_assigned_at' => $import->priority_assigned_at,
                        'news' => $import->news
                    ]);

                    ImpStatusHistory::create([
                        'import_id' => $newImport->id,
                        'previous_state' => null,
                        'new_state' => 5,
                        'user_id' => Auth::id()
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => "Cantidad reducida a {$newQty} en tránsito. Se creó una nueva solicitud de {$remainingQty} en Producción."
                    ]);
                }

            } elseif ($newQty > $qtyRequested) {
                // Caso C: Cantidad superior - solo actualiza cantidad
                $import->update([
                    'qty_requested' => $newQty
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => "Cantidad de envío actualizada a {$newQty} con éxito."
                ]);
            } else {
                // Caso D: Cantidad igual - no se realizan cambios
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => 'La cantidad ingresada es igual a la actual, no se realizaron cambios.'
                ]);
            }

            DB::connection('tenant')->commit();

            unset($this->orders);
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al cambiar cantidades de envío: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar la cantidad.'
            ]);
        }
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
        if ($this->profileUser == '17') {
            return;
        }
        $this->selectedOrderIdForDelete = $importId;
        $this->deleteJustification = '';
        $this->showModalDelete = true;
    }

    public function deleteOrderWithJustification()
    {
        if ($this->profileUser == '17') {
            return;
        }
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

        if ($this->filterStatus == 13) {
            try {
                DB::connection('tenant')->table('imp_new_products')
                    ->where('id', $importId)
                    ->update([
                        'exw' => (float)$price,
                        'status' => 'QUOTED',
                        'updated_at' => now()
                    ]);

                $this->saveComment($importId, "Proveedor actualizó precio cotizado a $" . number_format($price, 2) . " USD.", "Precio cotizado actualizado correctamente.");
            } catch (\Exception $e) {
                Log::error('❌ Error al actualizar precio cotizado del producto nuevo: ' . $e->getMessage());
            }
            return;
        }

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

            $import->update([
                'status' => $newStatus,
                'news' => 0
            ]);

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

    public function saveSendProduction($importId = null)
    {
        $targetId = $importId ?? $this->import_id;
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->transaction(function () use ($targetId) {
                $import = ImpImports::findOrFail($targetId);

                $oldStatus = $import->status;

                $newStatus = 5;

                $dataStatus = [
                    'import_id' => $targetId,
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

    public function saveSendFinished($importId)
    {
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->transaction(function () use ($importId) {
                $import = ImpImports::findOrFail($importId);

                $oldStatus = $import->status;
                $newStatus = 12; // Terminados

                $dataStatus = [
                    'import_id' => $importId,
                    'previous_state' => $oldStatus,
                    'new_state' => $newStatus,
                    'user_id' => Auth::id()
                ];

                ImpStatusHistory::create($dataStatus);

                $import->update(['status' => $newStatus]);
            });
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => Auth::user()?->profile_id == 17 ? 'Update finished successfully' : 'Actualizado a terminado con éxito'
            ]);
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error("Error al enviar a terminados: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo actualizar el estado'
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
            $this->lockWay = false;
        } elseif ($hasAir) {
            $this->way = 'Aerea';
            $this->lockWay = false;
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

                if (!empty(trim($this->observations))) {
                    $originalComment = trim($this->observations);
                    $finalComment = $originalComment;
                    $profileId = Auth::user()?->profile_id;
                    try {
                        if ($profileId == 17) {
                            $translated = $this->translateText($originalComment, 'en', 'es');
                            if ($translated) {
                                $finalComment = $originalComment . "[TRANSLATED]" . $translated;
                            }
                        } else {
                            $translated = $this->translateText($originalComment, 'es', 'en');
                            if ($translated) {
                                $finalComment = $originalComment . "[TRANSLATED]" . $translated;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Error al traducir la observación inicial del envío: " . $e->getMessage());
                    }

                    ImpShipmentComments::create([
                        'shipment_id' => $newShipping->id,
                        'comment' => $finalComment,
                        'user_id' => Auth::id()
                    ]);
                }

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
        $query = ImpShippments::query();

        if ($this->filterStatus == 7) {
            $query->whereHas('packings.imports', function ($q) {
                $q->where('status', 7);
            });
        } elseif ($this->filterStatus == 8) {
            $query->whereHas('packings.imports', function ($q) {
                $q->where('status', 8);
            });
        } else {
            // Mostrar solo envíos vacíos o que tengan al menos un producto pendiente de recibir (status < 8)
            $query->where(function ($q) {
                $q->whereDoesntHave('packings.imports')
                  ->orWhereHas('packings.imports', function ($sub) {
                      $sub->where('status', '<', 8);
                  });
            });
        }

        return $query->get()
            ->map(function ($sh) {
                $dateStr = $sh->etd ? \Carbon\Carbon::parse($sh->etd)->format('d/m/Y') : '';
                $opStr = $sh->operation_number ? $sh->operation_number . " " : '';
                $conveyorStr = $sh->conveyor ? " " . $sh->conveyor : '';
                $etdStr = $dateStr ? " ETD: " . $dateStr : '';
                
                $wayStr = $sh->way;
                if ($wayStr === 'Aerea') $wayStr = 'Aérea';
                elseif ($wayStr === 'Maritima') $wayStr = 'Marítima';
                elseif ($wayStr === 'Express') $wayStr = 'Express';

                // Agregar ETA y Llega a Fervicom
                $etaStr = $sh->eta ? " ETA: " . \Carbon\Carbon::parse($sh->eta)->format('d/m/Y') : '';
                $fervicomStr = $sh->fervicom_arrival_date ? " Llega a Fervicom: " . \Carbon\Carbon::parse($sh->fervicom_arrival_date)->format('d/m/Y') : '';

                return [
                    'id' => $sh->id,
                    'way' => "{$opStr}{$wayStr}{$conveyorStr}{$etdStr}{$etaStr}{$fervicomStr}"
                ];
            })
            ->toArray();
    }

    public function render()
    {
        $labels = $this->labels;
        
        $suppliers = [];
        if (Auth::user()?->profile_id != 17) {
            $suppliers = \App\Models\Auth\User::select('users.id', 'users.name')
                ->join('vnt_contacts', 'users.contact_id', '=', 'vnt_contacts.id')
                ->whereHas('tenants', function ($query) {
                    $query->where('tenants.id', session('tenant_id'));
                })
                ->where('users.profile_id', 17)
                ->where('vnt_contacts.status', 1)
                ->whereNull('vnt_contacts.deleted_at')
                ->distinct()
                ->get();
        }

        $categories = [];
        if ($this->showModalConvertNewProduct) {
            $categories = \App\Models\Tenant\Items\Category::where('status', 1)->get();
        }

        return view(
            'livewire.tenant.imports.orders',
            [
                'labels' => $labels,
                'profileUser' => $this->getProfileUserProperty(),
                'suppliers' => $suppliers,
                'categories' => $categories
            ]
        )
            ->layout('layouts.app', ['header' => Auth::user()?->profile_id == 17 ? 'Order Management' : 'Gestión de Ordenes']);
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

        try {
            $query = ImpImports::where('status', 7)->whereNull('deleted_at');
            
            if ($this->selectedShipp > 0) {
                $query->whereHas('packing', function ($q) {
                    $q->where('shipping_id', $this->selectedShipp);
                });
            } else {
                if (empty($this->selectedOrders)) {
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => 'Please select a shipment or the products you wish to receive.'
                    ]);
                    return;
                }
                $query->whereIn('id', $this->selectedOrders);
            }

            $selectedImports = $query->get();

            if ($selectedImports->isEmpty()) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No products found in transit for this shipment.'
                ]);
                return;
            }
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

            $importsToSync = [];
            DB::connection('tenant')->transaction(function () use ($selectedImports, $hasMaritime, $hasAir, &$importsToSync) {
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

                    $importsToSync[] = $import;

                    // Afectación automática del stock
                    $principalStore = \App\Models\Tenant\Items\InvStore::where('status', 1)
                        ->orderBy('id', 'asc')
                        ->first();

                    if ($principalStore) {
                        $itemStore = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $import->item_id)
                            ->where('storeId', $principalStore->id)
                            ->first();

                        $qtyToAdd = $import->qty_shipped;

                        if ($itemStore) {
                            $itemStore->stock_items_store += $qtyToAdd;
                            $itemStore->save();
                        } else {
                            \App\Models\Tenant\Items\InvItemsStore::create([
                                'itemId' => $import->item_id,
                                'storeId' => $principalStore->id,
                                'stock_items_store' => $qtyToAdd,
                                'initial_stock' => 0.00,
                                'wp_stock_percentage' => 100,
                            ]);
                        }
                    }
                }

                // 2. Rotar prioridades de los productos restantes (status < 8) de forma independiente por item_id
                $itemIds = $selectedImports->pluck('item_id')->unique()->toArray();

                if (!empty($itemIds)) {
                    if ($hasMaritime) {
                        // - Second pasa a ASAP
                        ImpImports::whereIn('item_id', $itemIds)
                            ->where('priority', 'Second')
                            ->where('status', '<', 8)
                            ->whereNull('deleted_at')
                            ->update([
                                'priority' => 'ASAP',
                                'priority_assigned_at' => now()
                            ]);

                        // - Third pasa a Second
                        ImpImports::whereIn('item_id', $itemIds)
                            ->where('priority', 'Third')
                            ->where('status', '<', 8)
                            ->whereNull('deleted_at')
                            ->update([
                                'priority' => 'Second',
                                'priority_assigned_at' => now()
                            ]);
                    }

                    if ($hasAir) {
                        // - Express 2 pasa a Express
                        ImpImports::whereIn('item_id', $itemIds)
                            ->where('priority', 'Express 2')
                            ->where('status', '<', 8)
                            ->whereNull('deleted_at')
                            ->update([
                                'priority' => 'Express',
                                'priority_assigned_at' => now()
                            ]);

                        // - Express 3 pasa a Express 2
                        ImpImports::whereIn('item_id', $itemIds)
                            ->where('priority', 'Express 3')
                            ->where('status', '<', 8)
                            ->whereNull('deleted_at')
                            ->update([
                                'priority' => 'Express 2',
                                'priority_assigned_at' => now()
                            ]);
                    }
                }
            });

            // Sincronizar con Alegra fuera de la transacción
            $this->syncReceivedItemsToAlegra($importsToSync);

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

    public function exportExcel()
    {
        return $this->downloadCsvFile('orders_export_' . now()->format('Y-m-d') . '.csv');
    }

    public function exportPdf()
    {
        $this->ensureTenantConnection();
        $centralDbName = config('database.connections.central.database');
        
        // Obtener todos los registros sin paginación, usando los filtros actuales
        $orders = DB::connection('tenant')
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
                'i.qty_shipped',
                'i.price',
                'i.delete_justification',
                's.operation_number',
                's.etd',
                DB::raw("CONCAT('#', s.consecutive, ' ', s.way) AS way"),
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
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('iv.name', 'like', '%' . $word . '%')
                          ->orWhere('iv.sku', 'like', '%' . $word . '%')
                          ->orWhere('iv.internal_code', 'like', '%' . $word . '%')
                          ->orWhere('iis.factory_ref', 'like', '%' . $word . '%');
                    });
                }
                return $query;
            })
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.orders-pdf', compact('orders'));
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'reporte_ordenes_' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function exportCsv()
    {
        return $this->downloadCsvFile('orders_export_' . now()->format('Y-m-d') . '.csv');
    }

    private function downloadCsvFile($fileName)
    {
        $this->ensureTenantConnection();
        $centralDbName = config('database.connections.central.database');
        
        // Obtener todos los registros sin paginación, usando los filtros actuales
        $orders = DB::connection('tenant')
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
                'i.qty_shipped',
                'i.price',
                'i.delete_justification',
                's.operation_number',
                's.etd',
                DB::raw("CONCAT('#', s.consecutive, ' ', s.way) AS way"),
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
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('iv.name', 'like', '%' . $word . '%')
                          ->orWhere('iv.sku', 'like', '%' . $word . '%')
                          ->orWhere('iv.internal_code', 'like', '%' . $word . '%')
                          ->orWhere('iis.factory_ref', 'like', '%' . $word . '%');
                    });
                }
                return $query;
            })
            ->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            // Agregar el BOM UTF-8 para que Excel lo abra con los caracteres y tildes bien decodificados
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados del CSV
            fputcsv($file, [
                'ID', 
                'ITEM / PRODUCTO', 
                'FACTORY REF', 
                'LAST PRICE ($)', 
                'QTY ORDERED', 
                'LABEL', 
                'QUOTED PRICE', 
                'QTY SHIPPED', 
                'LAST COMMENT', 
                'SHIPPING INFO / DELETE JUSTIFICATION',
                'STATUS'
            ], ';');

            foreach ($orders as $row) {
                // Formatear información de envío
                $shipInfo = '';
                if ($row->status == 11) {
                    $shipInfo = "Eliminado por: " . ($row->deleted_by_user ?? 'N/A') . " | Justificación: " . ($row->delete_justification ?? '');
                } elseif ($row->operation_number || $row->way || $row->etd) {
                    $recDate = $row->received_at ? \Carbon\Carbon::parse($row->received_at)->format('d/m/Y') : '—';
                    $etdDate = $row->etd ? \Carbon\Carbon::parse($row->etd)->format('d/m/Y') : '—';
                    $shipInfo = "O.N: " . ($row->operation_number ?? '—') . " | ETD: " . $etdDate . " | Vía: " . ($row->way ?? '—') . " | Rec: " . $recDate;
                } else {
                    $shipInfo = '—';
                }

                fputcsv($file, [
                    $row->id,
                    $row->item,
                    $row->factory_ref ?? 'N/A',
                    $row->exw ?? 0,
                    $row->qty_requested ?? 0,
                    $row->label ?? $row->priority ?? 'N/A',
                    $row->price ?? 0,
                    $row->qty_shipped ?? 0,
                    $row->ultimo_comentario ?? '',
                    $shipInfo,
                    $row->translated_name ?? 'N/A'
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    private function syncReceivedItemsToAlegra($imports)
    {
        try {
            $itemsAlegra = [];
            foreach ($imports as $import) {
                $item = \App\Models\Tenant\Items\Items::find($import->item_id);
                if ($item && $item->api_data_id) {
                    $invValue = \App\Models\Tenant\Items\InvValues::where('itemId', $import->item_id)->first();
                    $unitCost = $invValue ? floatval($invValue->values) : 0;

                    $itemsAlegra[] = [
                        'type'     => 'in',
                        'id'       => (string) $item->api_data_id,
                        'unitCost' => $unitCost,
                        'quantity' => floatval($import->qty_shipped),
                    ];
                }
            }

            if (!empty($itemsAlegra)) {
                $shipmentName = '';
                $shipmentId = $this->selectedShipp;
                if (!$shipmentId && !empty($imports)) {
                    $firstImport = $imports[0] ?? null;
                    if ($firstImport && $firstImport->packing_id) {
                        $packing = \App\Models\Tenant\Imports\ImpPacking::find($firstImport->packing_id);
                        if ($packing) {
                            $shipmentId = $packing->shipping_id;
                        }
                    }
                }

                if ($shipmentId) {
                    $shipment = \App\Models\Tenant\Imports\ImpShippments::find($shipmentId);
                    if ($shipment) {
                        $etdDate = $shipment->etd ? \Carbon\Carbon::parse($shipment->etd)->format('d/m/Y') : '—';
                        $shipmentName = trim(
                            ($shipment->operation_number ?? '') . ' ' .
                            ($shipment->way ?? '') . ' ' .
                            ($shipment->conveyor ?? '') . ' ETD: ' . $etdDate
                        );
                    }
                }

                if (empty($shipmentName)) {
                    $shipmentName = 'Ingreso por ajuste';
                }

                $alegraData = [
                    'date'         => date('Y-m-d'),
                    'observations' => $shipmentName,
                    'warehouse'    => ['id' => '1'],
                    'items'        => $itemsAlegra,
                ];

                Log::info('📦 [Orders] Payload de ajuste para Alegra preparado:', $alegraData);
                $movementsService = new \App\Services\Tenant\Movements\MovementsService();
                $alegraResult = $movementsService->syncAdjustmentToApi($alegraData);

                if ($alegraResult['success']) {
                    Log::info('✅ [Orders] Ajuste de inventario sincronizado con Alegra exitosamente.');
                } else {
                    Log::error('❌ [Orders] Error al sincronizar ajuste con Alegra: ' . ($alegraResult['message'] ?? 'Error desconocido'));
                    $this->dispatch('show-toast', [
                        'type' => 'warning',
                        'message' => 'Advertencia: No se pudo sincronizar el ajuste con Alegra. Verifique los logs.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ [Orders] Excepción al sincronizar ajuste con Alegra: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal de creación para Producto Nuevo y autogenerar el siguiente código
     */
    public function openCreateNewProductModal()
    {
        $this->ensureTenantConnection();
        $this->reset([
            'newProductDescription', 
            'newProductPorcentaje', 
            'newProductMinQty', 
            'newProductFactor', 
            'newProductSupplierId', 
            'newProductFactoryRef', 
            'newProductImage', 
            'newProductExw', 
            'newProductIncrFletes', 
            'newProductPvp1', 
            'newProductPvpMin'
        ]);
        
        // Obtener el último código secuencial NEW_PRODUCTXX
        $lastProduct = DB::connection('tenant')
            ->table('imp_new_products')
            ->where('code', 'like', 'NEW_PRODUCT%')
            ->orderByRaw('CAST(SUBSTRING(code, 12) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->code, 11);
            $nextNumber = $lastNumber + 1;
        }

        // Formato con dos dígitos mínimo (NEW_PRODUCT01, NEW_PRODUCT02...)
        $this->newProductCode = 'NEW_PRODUCT' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        $this->showModalCreateNewProduct = true;
    }

    /**
     * Guardar el Producto Nuevo temporal a cotizar
     */
    public function saveNewProduct()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'newProductCode' => 'required|unique:tenant.imp_new_products,code',
            'newProductDescription' => 'required|min:3',
            'newProductSupplierId' => 'required|integer',
            'newProductImage' => 'nullable|image|max:2048',
            'newProductStockWordpress' => 'required|numeric|min:0',
            'newProductMinQtyWordpress' => 'required|numeric|min:0',
        ], [
            'newProductDescription.required' => 'La descripción es obligatoria',
            'newProductSupplierId.required' => 'Debe seleccionar un proveedor',
            'newProductStockWordpress.required' => 'El % de Stock es obligatorio',
            'newProductMinQtyWordpress.required' => 'La Cantidad Mínima es obligatoria',
        ]);

        $imagePath = null;
        if ($this->newProductImage) {
            $tenantId = session('tenant_id', 'default');
            $imagePath = $this->newProductImage->store("new_products/{$tenantId}", 'public');
        }

        try {
            DB::connection('tenant')->table('imp_new_products')->insert([
                'code' => $this->newProductCode,
                'description' => $this->newProductDescription,
                'porcentaje' => (float)($this->newProductPorcentaje ?: 0),
                'min_qty_supplier' => (int)($this->newProductMinQty ?: 1),
                'factor' => (float)($this->newProductFactor ?: 0),
                'supplier_id' => (int)$this->newProductSupplierId,
                'factory_ref' => $this->newProductFactoryRef ?: null,
                'image_path' => $imagePath,
                'exw' => (float)($this->newProductExw ?: 0),
                'incr_fletes' => (float)($this->newProductIncrFletes ?: 0),
                'factor_pvp1' => (float)($this->newProductPvp1 ?: 0),
                'factor_pvp_min' => (float)($this->newProductPvpMin ?: 0),
                'stock_wordpress' => (float)$this->newProductStockWordpress,
                'min_qty_wordpress' => (float)$this->newProductMinQtyWordpress,
                'status' => 'PENDING',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->showModalCreateNewProduct = false;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Producto Nuevo creado con éxito.'
            ]);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('❌ Error al guardar producto nuevo: ' . $e->getMessage());
            $this->addError('newProductCode', 'Error al registrar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Abrir modal para la conversión del Producto Nuevo a Producto Real
     */
    public function openConvertModal($newProductId)
    {
        $this->ensureTenantConnection();
        $this->selectedNewProductId = $newProductId;
        $this->finalInternalCode = '';
        $this->finalSku = '';
        $this->finalCategoryId = '';
        $this->finalType = '';
        $this->finalTaxId = '';
        $this->finalBrandId = '';
        $this->finalHouseId = '';
        $this->finalPurchasingUnit = '';
        $this->finalConsumptionUnit = '';
        $this->finalManageSerial = '0';
        $this->finalInventoriable = '1';
        $this->finalDescription = '';
        $this->finalStockWordpress = null;
        $this->finalMinQtyWordpress = null;
        $this->finalSupplierId = '';
        $this->tempValues = [];
        
        $newProduct = DB::connection('tenant')
            ->table('imp_new_products')
            ->where('id', $newProductId)
            ->first();

        if ($newProduct) {
            $this->finalDescription = $newProduct->description;
            $this->finalSupplierId = $newProduct->supplier_id;
            $this->finalStockWordpress = $newProduct->stock_wordpress;
            $this->finalMinQtyWordpress = $newProduct->min_qty_wordpress;
            $this->showModalConvertNewProduct = true;
        }
    }

    /**
     * Ejecutar la conversión del Producto Nuevo a Producto Real en el ERP
     */
    public function convertNewProductToReal()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'finalInternalCode' => 'required|unique:tenant.inv_items,internal_code',
            'finalSku' => 'required',
            'finalCategoryId' => 'required|integer',
            'finalType' => 'required',
            'finalTaxId' => 'required|integer',
            'finalBrandId' => 'required|integer',
            'finalHouseId' => 'required|integer',
            'finalPurchasingUnit' => 'required|integer',
            'finalConsumptionUnit' => 'required|integer',
            'finalManageSerial' => 'required|boolean',
            'finalInventoriable' => 'required|boolean',
            'finalDescription' => 'required|min:3',
            'finalStockWordpress' => 'required|numeric|min:0',
            'finalMinQtyWordpress' => 'required|numeric|min:0',
            'finalSupplierId' => 'required|integer'
        ], [
            'finalInternalCode.required' => 'El código interno definitivo es obligatorio.',
            'finalInternalCode.unique' => 'Este código interno ya existe en el inventario real.',
            'finalSku.required' => 'El SKU es obligatorio.',
            'finalCategoryId.required' => 'Debe seleccionar una categoría de inventario.',
            'finalType.required' => 'Debe seleccionar el tipo.',
            'finalTaxId.required' => 'Debe seleccionar el impuesto.',
            'finalBrandId.required' => 'Debe seleccionar la marca.',
            'finalHouseId.required' => 'Debe seleccionar la casa.',
            'finalPurchasingUnit.required' => 'Debe seleccionar la unidad de compra.',
            'finalConsumptionUnit.required' => 'Debe seleccionar la unidad de consumo.',
            'finalDescription.required' => 'La descripción o nombre es obligatorio.',
            'finalDescription.min' => 'La descripción debe tener al menos 3 caracteres.',
            'finalStockWordpress.required' => 'El % Stock WordPress es obligatorio.',
            'finalStockWordpress.numeric' => 'El % Stock WordPress debe ser un valor numérico.',
            'finalStockWordpress.min' => 'El % Stock WordPress no puede ser menor a 0.',
            'finalMinQtyWordpress.required' => 'La Cantidad Mínima WordPress es obligatoria.',
            'finalMinQtyWordpress.numeric' => 'La Cantidad Mínima WordPress debe ser un valor numérico.',
            'finalMinQtyWordpress.min' => 'La Cantidad Mínima WordPress no puede ser menor a 0.',
            'finalSupplierId.required' => 'Debe seleccionar un proveedor para este producto.'
        ]);

        $newProduct = DB::connection('tenant')
            ->table('imp_new_products')
            ->where('id', $this->selectedNewProductId)
            ->first();

        if (!$newProduct) {
            $this->addError('finalInternalCode', 'El producto nuevo temporal no existe.');
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // 1. Crear el ítem en inv_items (ERP Real)
            $itemId = DB::connection('tenant')->table('inv_items')->insertGetId([
                'categoryId' => $this->finalCategoryId,
                'name' => $this->finalDescription,
                'internal_code' => $this->finalInternalCode,
                'sku' => $this->finalSku,
                'description' => $this->finalDescription,
                'type' => $this->finalType,
                'brandId' => $this->finalBrandId,
                'houseId' => $this->finalHouseId,
                'inventoriable' => $this->finalInventoriable ? 1 : 0,
                'manage_serial' => $this->finalManageSerial ? 1 : 0,
                'purchasing_unit' => $this->finalPurchasingUnit,
                'consumption_unit' => $this->finalConsumptionUnit,
                'status' => 1,
                'taxId' => $this->finalTaxId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Si tiene imagen, guardarla en inv_image_gallery
            if ($newProduct->image_path) {
                DB::connection('tenant')->table('inv_image_gallery')->insert([
                    'itemId' => $itemId,
                    'img_path' => $newProduct->image_path,
                    'type' => 'PRINCIPAL',
                    'type_show' => 'COMERCIAL',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 2.5 Crear el registro de bodega en inv_items_store
            $principalStore = DB::connection('tenant')->table('inv_store')
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->first();

            if ($principalStore) {
                DB::connection('tenant')->table('inv_items_store')->insert([
                    'itemId'              => $itemId,
                    'storeId'             => $principalStore->id,
                    'initial_stock'       => 0,
                    'stock_items_store'   => 0,
                    'stock_min'           => 0,
                    'stock_max'           => 0,
                    'wp_stock_percentage' => (float)$this->finalStockWordpress,
                    'wp_min_stock'        => (float)$this->finalMinQtyWordpress,
                ]);
            }

            // 3. Guardar precios (Valores)
            $typeMap = [
                'Costo Inicial' => 'costo',
                'Costo' => 'costo',
                'Precio Base' => 'precio',
                'Precio Regular' => 'precio',
                'Precio Crédito' => 'precio',
                'Precio unitario x caja' => 'precio',
                'Precio Minimo' => 'precio',
            ];

            foreach ($this->tempValues as $label => $value) {
                if ($value !== null && $value !== '' && $value > 0) {
                    DB::connection('tenant')->table('inv_values')->insert([
                        'itemId' => $itemId,
                        'label' => $label,
                        'type' => $typeMap[$label] ?? 'costo',
                        'values' => (float)$value,
                        'date' => now(),
                        'warehouseId' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // 4. Crear setup de importación del item en imp_items_setup
            DB::connection('tenant')->table('imp_items_setup')->insert([
                'item_id' => $itemId,
                'supplier_id' => $this->finalSupplierId,
                'factory_ref' => $newProduct->factory_ref ?: 'N/A',
                'exw' => $newProduct->exw,
                'percentage' => $newProduct->porcentaje,
                'freight_increase' => $newProduct->incr_fletes,
                'pvp_factor' => $newProduct->factor_pvp1,
                'pvp_min_factor' => $newProduct->factor_pvp_min,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. Actualizar el estado de imp_new_products a CONVERTED
            DB::connection('tenant')->table('imp_new_products')
                ->where('id', $this->selectedNewProductId)
                ->update([
                    'status' => 'CONVERTED',
                    'real_item_id' => $itemId,
                    'updated_at' => now()
                ]);

            DB::connection('tenant')->commit();

            $this->showModalConvertNewProduct = false;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => '¡Producto convertido y orden de importación creada con éxito!'
            ]);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ Error al convertir producto nuevo: ' . $e->getMessage());
            $this->addError('finalInternalCode', 'Error durante la conversión: ' . $e->getMessage());
        }
    }

    /**
     * Asignar prioridad y registrar pedido en lote de productos convertidos
     */
    public function updatedOrderQuantities($value, $key)
    {
        $productId = (int)$key;
        $qty = (int)$value;

        if ($qty > 0) {
            if (!in_array($productId, $this->selectedConvertedIds)) {
                $this->selectedConvertedIds[] = $productId;
            }
        } else {
            $this->selectedConvertedIds = array_values(array_filter($this->selectedConvertedIds, fn($id) => $id != $productId));
        }
    }

    public function assignPriorityToNewProducts($priority)
    {
        try {
            if (empty($this->selectedConvertedIds)) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No hay productos convertidos seleccionados'
                ]);
                return;
            }

            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () use ($priority) {
                foreach ($this->selectedConvertedIds as $productId) {
                    $newProduct = DB::connection('tenant')
                        ->table('imp_new_products')
                        ->where('id', $productId)
                        ->first();

                    if ($newProduct && $newProduct->real_item_id) {
                        $qty = isset($this->orderQuantities[$productId]) && (int)$this->orderQuantities[$productId] > 0
                            ? (int)$this->orderQuantities[$productId]
                            : (int)$newProduct->min_qty_supplier;

                        // Insertar el pedido en imp_imports
                        DB::connection('tenant')->table('imp_imports')->insert([
                            'item_id' => $newProduct->real_item_id,
                            'priority' => $priority,
                            'priority_assigned_at' => now(),
                            'qty_requested' => $qty,
                            'user_id' => Auth::id(),
                            'status' => 1, // Solicitado
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // Cambiar estado en imp_new_products a ORDERED
                        DB::connection('tenant')
                            ->table('imp_new_products')
                            ->where('id', $productId)
                            ->update([
                                'status' => 'ORDERED',
                                'updated_at' => now()
                            ]);
                    }
                }
            });

            $this->selectedConvertedIds = [];
            $this->orderQuantities = [];

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Pedidos creados y asignados correctamente en lote.'
            ]);

            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('❌ Error al registrar pedidos en lote desde Producto Nuevo: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al crear pedidos: ' . $e->getMessage()
            ]);
        }
    }
}
