<?php

namespace App\Livewire\Tenant\Movements;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Models\Tenant\Movements\InvReason;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MovementForm extends Component
{
    // Modal state
    public $showModal = false;
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
    ];
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    
    public function mount()
    {
        $this->warehouseForm['movementType'] = '';
        $this->movementForm['date'] = now()->format('Y-m-d');
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
        return Items::where('status', 1)->get();
    }
    
    /**
     * Computed property for unit measurements
     */
    #[Computed]
    public function unitMeasurements()
    {
        return UnitMeasurements::where('status', 1)->get();
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
            
            if ($warehouseStores->count() == 1) {
                $this->showSelectStore = false;
                $this->selectedStoreId = $warehouseStores->first()->id;
            } elseif ($warehouseStores->count() > 1) {
                $this->showSelectStore = true;
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
        try {
            // Validate detail form
            $this->ensureTenantConnection();
             // Hay un error en la validacion
            //  dd($this->detailForm['itemId']);
            // $this->validate([
            //     'detailForm.itemId' => 'required|exists:tenant.items,id',
            //     'detailForm.quantity' => 'required|numeric|min:0.01',
            //     'detailForm.unitMeasurementId' => 'required|exists:tenant.unit_measurements,id',
            // ], [
            //     'detailForm.itemId.required' => 'Debe seleccionar un item',
            //     'detailForm.itemId.exists' => 'El item seleccionado no es válido',
            //     'detailForm.quantity.required' => 'La cantidad es obligatoria',
            //     'detailForm.quantity.numeric' => 'La cantidad debe ser un número',
            //     'detailForm.quantity.min' => 'La cantidad debe ser mayor a 0',
            //     'detailForm.unitMeasurementId.required' => 'Debe seleccionar una unidad de medida',
            //     'detailForm.unitMeasurementId.exists' => 'La unidad de medida seleccionada no es válida',
            // ]);
            
            // Get item and unit measurement info with all relationships
            $item = Items::with(['invValues', 'purchasingUnit', 'consumptionUnit'])
                ->findOrFail($this->detailForm['itemId']);
            $unitMeasurement = UnitMeasurements::find($this->detailForm['unitMeasurementId']);
            
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
                if($adjustedQuantity < 0){
                    $this->errorMessage = 'La bodega no tiene stock suficiente para realizar el movimiento';
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
                ];
            }

            // Reset detail form
            $this->resetDetailForm();
            $this->clearMessages();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar el item: ' . $e->getMessage();
            Log::error('Error adding detail to movement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            // Validate movement form
            // $this->validate([
            //     'warehouseForm.movementType' => 'required|in:ENTRADA,SALIDA',
            //     'movementForm.date' => 'required|date',
            //     'movementForm.reasonId' => 'required|exists:tenant.inv_reasons,id',
            //     'movementForm.observations' => 'nullable|string|max:500',
            // ], [
            //     'warehouseForm.movementType.required' => 'Debe seleccionar el tipo de movimiento',
            //     'warehouseForm.movementType.in' => 'El tipo de movimiento no es válido',
            //     'movementForm.date.required' => 'La fecha es obligatoria',
            //     'movementForm.date.date' => 'La fecha no es válida',
            //     'movementForm.reasonId.required' => 'El motivo es obligatorio',
            //     'movementForm.reasonId.exists' => 'El motivo seleccionado no es válido',
            //     'movementForm.observations.max' => 'Las observaciones no pueden exceder 500 caracteres',
            // ]);
            
            // Validate that there are details
            if (empty($this->details)) {
                $this->errorMessage = 'Debe agregar al menos un item al movimiento';
                return;
            }
            
            $this->ensureTenantConnection();
            
            DB::connection('tenant')->beginTransaction();
            
            try {
                // Get next consecutive
                $selectedType = strtolower($this->warehouseForm['movementType']);
                $lastMovement = InvInventoryAdjustment::byType($selectedType)
                    ->orderBy('consecutive', 'desc')
                    ->first();
                $consecutive = $lastMovement ? $lastMovement->consecutive + 1 : 1;
                

            //    dd([
            //         'date'        => $this->movementForm['date'],
            //         'observations'=> $this->movementForm['observations'],
            //         'type'        => $selectedType,
            //         'status'      => 1,
            //         'warehouseId' => $this->warehouseId,
            //         'reasonId'    => $this->movementForm['reasonId'],
            //         'consecutive' => $consecutive,
            //         'storeId'    => $this->selectedStoreId,
            //         'userId'      => Auth::id(),
            //     ]);
                // Create movement
             
                $movement = InvInventoryAdjustment::create([
                    'date' => $this->movementForm['date'],
                    'observations' => $this->movementForm['observations'],
                    'type' => $selectedType,
                    'status' => 1,
                    'warehouseId' => $this->selectedStoreId,
                    'reasonId' => $this->movementForm['reasonId'],
                    'consecutive' => $consecutive,
                    'userId' => Auth::id(),
                ]);
                
                // Create details
                // dd($this->details);
                foreach ($this->details as $detail) {
                    InvDetailInventoryAdjustment::create([
                        'inventoryAdjustmentId' => $movement->id,
                        'itemId' => $detail['itemId'],
                        'quantity' => $detail['quantity'],
                        'unitMeasurementId' => $detail['unitMeasurementId'],
                    ]);

                    // Update or create stock
                    $itemStore = InvItemsStore::where('itemId', $detail['itemId'])
                        ->where('storeId', $this->selectedStoreId)
                        ->first();
                    
                    if ($itemStore) {
                        $itemStore->stock_items_store = $detail['adjustedQuantity'];
                        $itemStore->save();
                    } else {
                        // Create new item store record if it doesn't exist
                        InvItemsStore::create([
                            'itemId' => $detail['itemId'],
                            'storeId' => $this->selectedStoreId,
                            'stock_items_store' => $detail['adjustedQuantity'],
                        ]);
                    }
                }
                
                DB::connection('tenant')->commit();
                
                $this->successMessage = 'Movimiento creado exitosamente';
                
                Log::info('Movement created successfully', [
                    'movementId' => $movement->id,
                    'type' => $this->warehouseForm['movementType'],
                    'detailsCount' => count($this->details)
                ]);
                
                // Reset form and close modal
                $this->resetForm();
                $this->showModal = false;
                
                // Refresh the movement list with the movement type
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
        ];
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
