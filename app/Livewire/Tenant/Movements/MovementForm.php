<?php

namespace App\Livewire\Tenant\Movements;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Models\Tenant\Movements\InvReason;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Movements\MovementsService;
use App\Models\Auth\Tenant;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MovementForm extends Component
{
    // Modal state
    use HasCompanyConfiguration;
    public $showModal = false;
    public $showDetailsModal = false;
    public $movementDetails = []; // Store movement details as array instead of model
    public $movementType = 'entrada'; // Para el filtro de la lista
    public $warehouseId = null; // ID of the warehouse from central
    public $selectedStoreId = null; // ID of the selected store (bodega)
    public $showSelectStore = false;

    // Form data with warehouse form structure
    public $warehouseForm = [
        'movementType' => '', // ENTRADA o SALIDA
    ];

    public $movementForm = [
        'date' => '',
        'observations' => '',
        'reasonId' => '',
    ];
    // Details management
    public $details = [];
    public $detailForm = [
        'itemId' => '',
        'quantity' => '',
        'unitMeasurementId' => '',
        'cost' => 0,
        'supplierId' => ''
    ];

    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    public $isProcessing = false;

    protected $listeners = [
        'showMovementDetails',
        'storeSelected',
        'reasonSelected',
        'itemSelected',
        'unitMeasurementSelected',
        'supplierSelected'
    ];

    public function mount()
    {
        $this->warehouseForm['movementType'] = '';
        $this->movementForm['date'] = now()->format('Y-m-d');

        // Set default unit measurement to "UNIDAD"
        $this->setDefaultUnitMeasurement();
    }

    /**
     * Handle item selection from GenericSelect component
     * 
     * @param mixed $value The selected item ID
     * @return void
     */
    public function itemSelected($value)
    {
        $this->detailForm['itemId'] = $value ?? '';
    }

    /**
     * Handle unit measurement selection from GenericSelect component
     * 
     * @param mixed $value The selected unit measurement ID
     * @return void
     */
    public function unitMeasurementSelected($value)
    {
        $this->detailForm['unitMeasurementId'] = $value ?? '';
    }

    /**
     * Handle supplier selection from GenericSelect component
     * 
     * @param mixed $value The selected supplier ID
     * @return void
     */
    public function supplierSelected($value)
    {
        $this->detailForm['supplierId'] = $value ?? '';

        // Update supplier name for display in details table
        if (!empty($value)) {
            $supplier = collect($this->suppliers)->firstWhere('id', $value);
            $this->detailForm['supplierName'] = $supplier['firstName'] ?? 'N/A';
        } else {
            $this->detailForm['supplierName'] = '';
        }
    }

    /**
     * Set default unit measurement to UNIDAD
     */
    private function setDefaultUnitMeasurement()
    {
        $this->ensureTenantConnection();
        $unidad = UnitMeasurements::where('description', 'UNIDAD')->first();
        if ($unidad) {
            $this->detailForm['unitMeasurementId'] = $unidad->id;
        }
    }


    /**
     * Show movement details modal
     */
    public function showMovementDetails($movementId)
    {
        $this->ensureTenantConnection();
        $movement = InvInventoryAdjustment::with([
            'details.item',
            'details.unitMeasurement',
            'store',
            'reason',
            'supplierContact'
        ])->find($movementId);

        if ($movement) {
            // Convert model to array to avoid serialization issues
            $this->movementDetails = [
                'id' => $movement->id,
                'consecutive' => $movement->formatted_consecutive,
                'date' => $movement->date->format('d/m/Y H:i'),
                'type' => $movement->type === 'entrada' ? 'Entrada' : 'Salida',
                'store_name' => $movement->store->name ?? 'N/A',
                'reason_name' => $movement->reason->name ?? 'N/A',
                'user_name' => $movement->user->name ?? 'N/A',
                'supplier_name' => ($movement->supplier > 0 && $movement->supplierContact) ? $movement->supplierContact->firstName : '-',
                'status' => $movement->status,
                'observations' => $movement->observations,
                'details' => $movement->details->map(function ($detail) {
                    return [
                        'item_name' => $detail->item->name ?? 'N/A',
                        'quantity' => number_format($detail->quantity, 2),
                        'unit_name' => $detail->unitMeasurement->description ?? 'N/A',
                    ];
                })->toArray()
            ];

            $this->showDetailsModal = true;
        }
    }

    /**
     * Close details modal
     */
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->movementDetails = [];
    }

    /**
     * Annul a movement and revert inventory
     */
    public function annulMovement($movementId)
    {
        try {
            $this->ensureTenantConnection();

            $movement = InvInventoryAdjustment::with('details')->find($movementId);

            if (!$movement) {
                $this->errorMessage = 'Movimiento no encontrado';
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => $this->errorMessage
                ]);
                return;
            }

            if ($movement->status === 0) {
                $this->errorMessage = 'Este movimiento ya está anulado';
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => $this->errorMessage
                ]);
                return;
            }

            // ── Paso 1: Intentar eliminar en Alegra si tiene api_data_id ──────────
            if ($movement->api_data_id) {
                $service    = new MovementsService();
                $alegraResult = $service->deleteAdjustmentFromApi((int) $movement->api_data_id);

                if (!$alegraResult['success']) {
                    $userMessage = $alegraResult['message'] ?? 'Error desconocido al comunicarse con Alegra';
                    if (config('app.debug') && isset($alegraResult['technical_details'])) {
                        $userMessage .= ' (Detalle: ' . $alegraResult['technical_details'] . ')';
                    }
                    $this->errorMessage = 'No se pudo anular el movimiento en Alegra: ' . $userMessage . '. El movimiento NO fue anulado localmente.';
                    Log::error('❌ Anulación bloqueada: Alegra rechazó la eliminación', [
                        'movement_id'  => $movementId,
                        'api_data_id'  => $movement->api_data_id,
                        'alegra_error' => $alegraResult,
                    ]);
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => $this->errorMessage
                    ]);
                    return;
                }

                Log::info('✅ Ajuste eliminado en Alegra, procediendo con anulación local', [
                    'movement_id' => $movementId,
                    'api_data_id' => $movement->api_data_id,
                ]);
            }

            // ── Paso 2: Anular localmente ─────────────────────────────────────────
            DB::connection('tenant')->beginTransaction();

            // Revertir el inventario según el tipo de movimiento
            foreach ($movement->details as $detail) {
                $itemStore = InvItemsStore::where('itemId', $detail['itemId'])
                    ->where('storeId', $movement->storeId)
                    ->first();

                if (!$itemStore) {
                    DB::connection('tenant')->rollBack();
                    $this->errorMessage = 'No se encontró el registro de inventario para el item';
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => $this->errorMessage
                    ]);
                    return;
                }

                // Obtener la unidad de medida para calcular la cantidad en unidad de consumo
                $unitMeasurement = UnitMeasurements::find($detail->unitMeasurementId);
                $quantityInConsumptionUnit = $detail->quantity * ($unitMeasurement ? $unitMeasurement->quantity : 1);

                // Si es ENTRADA, al anular debemos RESTAR del inventario
                // Si es SALIDA, al anular debemos SUMAR al inventario
                if ($movement->type === 'entrada') {
                    // Verificar que hay suficiente stock para restar
                    if ($itemStore->stock_items_store < $quantityInConsumptionUnit) {
                        DB::connection('tenant')->rollBack();
                        $this->errorMessage = 'No hay suficiente stock para anular este movimiento de entrada';
                        $this->dispatch('show-toast', [
                            'type' => 'error',
                            'message' => $this->errorMessage
                        ]);
                        return;
                    }

                    // Restar del inventario (revertir la entrada)
                    $itemStore->stock_items_store -= $quantityInConsumptionUnit;
                } else {
                    // Sumar al inventario (revertir la salida)
                    $itemStore->stock_items_store += $quantityInConsumptionUnit;
                }

                $itemStore->save();
            }

            // Marcar el movimiento como anulado
            $movement->update(['status' => 0]);

            DB::connection('tenant')->commit();

            $this->successMessage = 'Movimiento anulado correctamente y el inventario ha sido actualizado';

            // Close details modal and refresh list
            $this->closeDetailsModal();

            // Despachar toast de confirmación al anular
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Movimiento anulado correctamente'
            ]);

            $this->dispatch('refreshMovements', type: $movement->type);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error al anular movimiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error al anular el movimiento: ' . $e->getMessage();
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => $this->errorMessage
            ]);
        }
    }


    public function render()
    {
        $this->ensureTenantConnection();

        return view('livewire.tenant.movements.components.movement-form');
    }

    /**
     * Computed property for reasons based on movement type
     */
    #[Computed]
    public function reasons()
    {
        if (empty($this->warehouseForm['movementType'])) {
            return collect([]);
        }

        $movementType = $this->warehouseForm['movementType'] === 'ENTRADA' ? 'e' : 's';
        return InvReason::active()->byType($movementType)->get();
    }

    /**
     * Computed property for items
     */
    #[Computed]
    public function items()
    {
        $this->ensureTenantConnection();
        return Items::where('status', 1)
            ->where('inventoriable', 1)
            ->get(['id', 'name', 'sku'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku ?? 'N/A'
                ];
            })
            ->toArray();
    }

    /**
     * Computed property for unit measurements
     */
    #[Computed]
    public function unitMeasurements()
    {
        $this->ensureTenantConnection();
        return UnitMeasurements::where('status', 1)
            ->get(['id', 'description', 'quantity'])
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'description' => $unit->description,
                    'quantity' => $unit->quantity
                ];
            })
            ->toArray();
    }


    /**
     * Computed property for suppliers (proveedores)
     * Gets contacts from companies marked as suppliers
     */
    #[Computed]
    public function suppliers()
    {
        $this->ensureTenantConnection();

        return \App\Models\Tenant\Customer\VntContacts::select('vnt_companies.id', 'vnt_contacts.firstName')
            ->join('vnt_companies', 'vnt_contacts.email', '=', 'vnt_companies.billingEmail')
            ->where('vnt_companies.type', 'PROVEEDOR')
            ->where('vnt_contacts.status', 1)
            ->whereNull('vnt_contacts.deleted_at')
            ->distinct()
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'firstName' => $supplier->firstName
                ];
            })
            ->toArray();
    }

    /**
     * Open modal to create new movement
     */
    public function create()
    {
        $this->showModal = true;
        $this->warehouseForm['movementType'] = ''; // Reset to force selection
        $this->movementForm['date'] = now()->format('Y-m-d');
        $this->clearMessages();
    }

    /**
     * Computed property to get warehouse name
     */
    #[Computed]
    public function warehouseMovement()
    {
        $this->ensureTenantConnection();

        // Get warehouse from user's contact
        $user = Auth::user();

        if (!$user || !$user->contact_id) {
            return 'Sin bodega asignada';
        }
        // Load contact with warehouse relationship
        $contact = \App\Models\Central\VntContact::with('warehouse')
            ->find($user->contact_id);

        if (!$contact || !$contact->warehouseId) {
            return 'Sin bodega asignada';
        }
        $warehouse = $contact->warehouse;

        // Set the warehouse ID for use in stores
        if ($warehouse && $warehouse->status) {
            $this->warehouseId = $warehouse->id;

            // Check stores count to determine if select should be shown
            $warehouseStores = InvStore::where('warehouseId', $this->warehouseId)->where('status', 1)->get();
            // Validate plan 
            $validateStore = $this->canAggregateStores(false, $warehouseStores->count());
            Log::info('validateStore', ['validateStore' => $validateStore]);
            if ($warehouseStores->count() == 1) {
                $this->showSelectStore = false;
                $this->selectedStoreId = $warehouseStores->first()->id;
            } elseif ($warehouseStores->count() > 1) {
                if ($validateStore) {
                    $this->showSelectStore = true;
                } else {
                    $this->showSelectStore = false;
                }
            } else {
                $this->showSelectStore = false;
                $this->selectedStoreId = null;
            }

            return $warehouse->name;
        }

        return 'Sin bodega asignada';
    }

    /**
     * Computed property for stores based on warehouse
     */
    #[Computed]
    public function stores()
    {
        if (!$this->warehouseId) {
            return collect([]);
        }

        $this->ensureTenantConnection();
        return InvStore::where('warehouseId', $this->warehouseId)->where('status', 1)->get();
    }
    /**
     * Add item detail to the table
     */
    public function addDetail()
    {
        // Prevenir múltiples clicks
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {

            // Validación básica para evitar errores con clicks múltiples
            if (empty($this->detailForm['itemId'])) {
                $this->errorMessage = 'Debe seleccionar un item';
                $this->isProcessing = false;
                return;
            }

            if (empty($this->detailForm['quantity']) || $this->detailForm['quantity'] <= 0) {
                $this->errorMessage = 'La cantidad debe ser mayor a 0';
                $this->isProcessing = false;
                return;
            }

            if (empty($this->detailForm['unitMeasurementId'])) {
                $this->errorMessage = 'Debe seleccionar una unidad de medida';
                $this->isProcessing = false;
                return;
            }

            // Validación condicional del campo cost
            if ($this->warehouseForm['movementType'] == 'ENTRADA' && $this->movementForm['reasonId'] == 1) {
                if (empty($this->detailForm['cost']) || $this->detailForm['cost'] <= 0) {
                    $this->errorMessage = 'El costo unitario es obligatorio y debe ser mayor a 0';
                    $this->isProcessing = false;
                    return;
                }

                if (!is_numeric($this->detailForm['cost'])) {
                    $this->errorMessage = 'El costo debe ser un valor numérico';
                    $this->isProcessing = false;
                    return;
                }

                // Validación del proveedor
                if (empty($this->detailForm['supplierId'])) {
                    $this->errorMessage = 'El proveedor es requerido para movimientos de compra';
                    $this->isProcessing = false;
                    return;
                }
            }
            // Validate detail form
            $this->ensureTenantConnection();

            // Get item and unit measurement info with all relationships
            $item = Items::with(['invValues', 'purchasingUnit', 'consumptionUnit'])
                ->find($this->detailForm['itemId']);

            if (!$item) {
                $this->errorMessage = 'El item seleccionado no existe';
                $this->isProcessing = false;
                return;
            }

            $unitMeasurement = UnitMeasurements::find($this->detailForm['unitMeasurementId']);

            if (!$unitMeasurement) {
                $this->errorMessage = 'La unidad de medida seleccionada no existe';
                $this->isProcessing = false;
                return;
            }

            // Get price from inv_values
            $price = $item->invValues->first()->values ?? 0;
            $quantity = $this->detailForm['quantity'];

            // Check if item already exists in details
            $existingIndex = collect($this->details)->search(function ($detail) {
                return $detail['itemId'] == $this->detailForm['itemId'];
            });


            if ($existingIndex !== false) {
                // Update quantity if item already exists


                $oldQuantity = $this->details[$existingIndex]['quantity'];
                $newQuantity = $oldQuantity + $quantity;
                $this->details[$existingIndex]['quantity'] = $newQuantity;
                $this->details[$existingIndex]['total'] = $price * $newQuantity;
                // Recalculate adjusted quantity
                $currentQty = $this->details[$existingIndex]['currentQuantity'];
                $this->details[$existingIndex]['adjustedQuantity'] = $this->warehouseForm['movementType'] === 'ENTRADA'
                    ? $currentQty + $newQuantity
                    : $currentQty - $newQuantity;
            } else {
                // Get current quantity from inv_items_store
                $itemStore = InvItemsStore::where('itemId', $this->detailForm['itemId'])
                    ->where('storeId', $this->selectedStoreId)
                    ->first();

                // dd($itemStore->stock_items_store);
                $currentQuantity = $itemStore ? $itemStore->stock_items_store : 0;

                // Calculate adjusted quantity based on movement type
                $adjustedQuantity = $this->warehouseForm['movementType'] === 'ENTRADA'
                    ? $currentQuantity + ($unitMeasurement->quantity * $quantity)
                    : $currentQuantity - ($unitMeasurement->quantity * $quantity);


                // for exit             
                if ($adjustedQuantity < 0) {
                    $this->errorMessage = 'La bodega no tiene stock suficiente para realizar el movimiento';
                    $this->isProcessing = false;
                    return;
                }

                // // for entry movement
                // if( floatval($adjustedQuantity) > floatval($itemStore->stock_max)){
                //     $this->errorMessage = 'La bodega no tiene stock suficiente para realizar el movimiento';
                //     return;
                // }
                // Add new detail
                $this->details[] = [
                    'itemId' => $this->detailForm['itemId'],
                    'itemName' => $item->name,
                    'sku' => $item->sku ?? 'N/A',
                    'quantity' => $quantity,
                    'unitMeasurementId' => $this->detailForm['unitMeasurementId'],
                    'unitMeasurementName' => $unitMeasurement->description,
                    'consumptionUnitName' => $unitMeasurement->quantity * $quantity,
                    'currentQuantity' => $currentQuantity,
                    'adjustedQuantity' => $adjustedQuantity,
                    'price' => $price,
                    'total' => $price * $quantity,
                    'cost' => $this->detailForm['cost'] ?? 0,
                    'supplierId' => $this->detailForm['supplierId'] ?? 0,
                    'supplierName' => $this->detailForm['supplierName'] ?? '-',
                ];
            }

            // Reset detail form
            $this->resetDetailForm();
            $this->clearMessages();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isProcessing = false;
            throw $e;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar el item: ' . $e->getMessage();
            Log::error('Error adding detail to movement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Remove detail from table
     */
    public function removeDetail($index)
    {
        if (isset($this->details[$index])) {
            unset($this->details[$index]);
            $this->details = array_values($this->details); // Re-index array
        }
    }

    /**
     * Save movement with details
     */
    public function saveMovement()
    {
        try {
            // Validate that there are details
            if (empty($this->details)) {
                $this->errorMessage = 'Debe agregar al menos un item al movimiento';
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => $this->errorMessage
                ]);
                return;
            }

            $this->ensureTenantConnection();

            $selectedType = strtolower($this->warehouseForm['movementType']);

            // ── Paso 1: Construir payload para Alegra ────────────────────────────
            // Solo incluir items que tengan api_data_id (alegraId)
            $itemsAlegra = [];
            foreach ($this->details as $detail) {
                $item = Items::find($detail['itemId']);
                if ($item && $item->api_data_id) {
                    // Obtener unitCost desde inv_values filtrado por itemId (ID local)
                    $invValue = InvValues::where('itemId', $detail['itemId'])->first();
                    $unitCost = $invValue ? floatval($invValue->values) : floatval($detail['cost'] ?? 0);

                    $itemsAlegra[] = [
                        'type'     => $selectedType === 'entrada' ? 'in' : 'out',
                        'id'       => $item->api_data_id,
                        'unitCost' => $unitCost,
                        'quantity' => floatval($detail['quantity']),
                    ];
                }
            }

            $alegraData = !empty($itemsAlegra) ? [
                'date'      => $this->movementForm['date'],
                'items'     => $itemsAlegra,
                'warehouse' => ['id' => '1'],
            ] : [];


            Log::info('📦 [MovementForm] Payload Alegra construido', [
                'items_total'  => count($this->details),
                'items_alegra' => count($itemsAlegra),
                'alegra_data'  => $alegraData,
            ]);

            // ── Paso 2: Enviar a Alegra ANTES de crear localmente ────────────────
            // Los movimientos de COMPRA (ENTRADA con reasonId == 1) no se sincronizan con Alegra
            $isCompra = strtoupper($this->warehouseForm['movementType']) === 'ENTRADA'
                && (string) $this->movementForm['reasonId'] === '1';

            if ($isCompra) {
                Log::info('🛒 [MovementForm] Movimiento de COMPRA - sync con Alegra omitido', [
                    'movementType' => $this->warehouseForm['movementType'],
                    'reasonId'     => $this->movementForm['reasonId'],
                ]);
                $alegraResult = [
                    'success'      => true,
                    'message'      => 'Movimiento de compra guardado localmente (sin sync Alegra)',
                    'error_type'   => null,
                    'retryable'    => false,
                    'api_response' => null,
                    'sync_skipped' => true,
                    'sync_reason'  => 'Movimiento de compra no requiere sincronización con Alegra',
                    'api_data_id'  => null,
                ];
            } else {
                $service      = new MovementsService();
                $alegraResult = $service->syncAdjustmentToApi($alegraData);
            }

            if (!$alegraResult['success']) {
                // Alegra rechazó el ajuste → no crear localmente
                $userMessage = $alegraResult['message'] ?? 'Error desconocido al comunicarse con Alegra';
                if (config('app.debug') && isset($alegraResult['technical_details'])) {
                    $userMessage .= ' (Detalle: ' . $alegraResult['technical_details'] . ')';
                }
                $this->errorMessage = 'No se pudo registrar el movimiento en Alegra: ' . $userMessage . '. El movimiento NO fue guardado.';
                Log::error('❌ [MovementForm] Creación bloqueada: Alegra rechazó el ajuste', [
                    'alegra_result' => $alegraResult,
                ]);
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => $this->errorMessage
                ]);
                return;
            }

            // ID del ajuste en Alegra (null si sync fue omitido)
            $apiDataId = $alegraResult['api_data_id'] ?? null;

            // ── Paso 3: Crear localmente en transacción ──────────────────────────
            DB::connection('tenant')->beginTransaction();

            try {
                // Get the store to access warehouseId
                $store       = InvStore::find($this->selectedStoreId);
                $warehouseId = $store ? $store->warehouseId : null;

                // Query for last consecutive filtering by warehouse, store, and type
                $lastMovement = InvInventoryAdjustment::byType($selectedType)
                    ->byStore($this->selectedStoreId)
                    ->whereHas('store', function ($query) use ($warehouseId) {
                        $query->where('warehouseId', $warehouseId);
                    })
                    ->orderBy('consecutive', 'desc')
                    ->first();
                $consecutive = $lastMovement ? $lastMovement->consecutive + 1 : 1;

                // Create movement
                $movement = InvInventoryAdjustment::create([
                    'date'         => $this->movementForm['date'],
                    'observations' => $this->movementForm['observations'],
                    'type'         => $selectedType,
                    'status'       => 1,
                    'storeId'      => $this->selectedStoreId,
                    'reasonId'     => $this->movementForm['reasonId'],
                    'consecutive'  => $consecutive,
                    'userId'       => Auth::id(),
                    'supplier'     => !empty($this->details[0]['supplierId']) ? (int)$this->details[0]['supplierId'] : 0,
                    'api_data_id'  => $apiDataId,
                ]);

                // Create details and update stock
                foreach ($this->details as $detail) {
                    InvDetailInventoryAdjustment::create([
                        'inventoryAdjustmentId' => $movement->id,
                        'itemId'                => $detail['itemId'],
                        'quantity'              => $detail['quantity'],
                        'unitMeasurementId'     => $detail['unitMeasurementId'],
                        'cost'                  => $detail['cost'] ?? 0,
                    ]);

                    // Update or create stock
                    $itemStore = InvItemsStore::where('itemId', $detail['itemId'])
                        ->where('storeId', $this->selectedStoreId)
                        ->first();

                    if ($itemStore) {
                        $itemStore->stock_items_store = $detail['adjustedQuantity'];
                        $itemStore->save();
                    } else {
                        InvItemsStore::create([
                            'itemId'            => $detail['itemId'],
                            'storeId'           => $this->selectedStoreId,
                            'stock_items_store' => $detail['adjustedQuantity'],
                        ]);
                    }
                }

                DB::connection('tenant')->commit();

                // Mensaje de éxito según si se sincronizó con Alegra
                if ($apiDataId) {
                    $this->successMessage = 'Movimiento creado y sincronizado exitosamente con Alegra';
                } elseif (!empty($alegraResult['sync_skipped'])) {
                    $this->successMessage = 'Movimiento creado exitosamente';
                } else {
                    $this->successMessage = 'Movimiento creado exitosamente';
                }

                Log::info('✅ [MovementForm] Movimiento creado exitosamente', [
                    'movementId'   => $movement->id,
                    'type'         => $selectedType,
                    'api_data_id'  => $apiDataId,
                    'detailsCount' => count($this->details),
                ]);

                $this->showModal = false;
                $this->resetForm();
                $this->movementType = $selectedType;
                
                // Despachar toast de confirmación al guardar
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => $this->successMessage
                ]);

                $this->dispatch('refreshMovements', type: $selectedType);

            } catch (\Exception $e) {
                DB::connection('tenant')->rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al guardar el movimiento: ' . $e->getMessage();
            Log::error('Error saving movement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => $this->errorMessage
            ]);
        }
    }

    /**
     * Close modal and reset form
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->clearMessages();
        $this->resetValidation();
    }

    /**
     * User plan
     */

    private function canAggregateStores(bool $update = false, $quantity): bool
    {

        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();
        $result = $this->isOptionEnabled(27);
        $value = $this->getOptionValue(27);

        // DEBUG: Log detallado de verificación
        Log::info('🔍 canCreateOrUpdateUsers() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 1,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(27) y getOptionValue(27)',
            'update' => $update,
        ]);


        if ($result == true && $quantity <= $value) {
            return true;
        }
        return false;
    }


    /**
     * Reset movement form
     */
    private function resetForm()
    {
        $this->warehouseForm = [
            'movementType' => '',
        ];
        $this->movementForm = [
            'date' => now()->format('Y-m-d'),
            'observations' => '',
            'reasonId' => '',
        ];
        $this->selectedStoreId = null;
        $this->details = [];
        $this->resetDetailForm();
    }

    /**
     * Reset detail form
     */
    private function resetDetailForm()
    {
        $this->detailForm = [
            'itemId' => '',
            'quantity' => '',
            'unitMeasurementId' => '',
            'cost' => 0,
            'supplierId' => '',
        ];

        // Set default unit measurement to UNIDAD
        $this->setDefaultUnitMeasurement();
    }

    /**
     * Clear messages
     */
    private function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    /**
     * Ensure tenant connection is established
     */
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

        // Set tenant connection
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Initialize tenancy
        tenancy()->initialize($tenant);
    }
}
