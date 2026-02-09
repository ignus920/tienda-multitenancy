<?php

namespace App\Livewire\Tenant\Transfers;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Transfers\InvTransfer;
use App\Models\Tenant\Transfers\InvDetailTransfer;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Central\VntWarehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransferForm extends Component
{
    // Tab state
    public string $activeTab = 'transfers';
    
    // Modal state
    public $showModal = false;
    public $showDetailsModal = false;
    public $transferDetails = [];
    // Form data
    public $transferForm = [
        'date' => '',
        'warehouseFromId' => '',
        'warehouseToId' => '',
        'storeFromId' => '',
        'storeToId' => '',
        'observations' => '',
    ];
    
    // Details management
    public $details = [];
    public $detailForm = [
        'itemId' => '',
        'quantity' => '',
        'unitMeasurementId' => ''
    ];
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    
    protected $listeners = ['showTransferDetails'];

    public function mount()
    {
        $this->transferForm['date'] = now()->format('Y-m-d');
    }

    /**
     * Set the active tab
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Watch for warehouse changes to reset store selection
     */
    public function updatedTransferFormWarehouseFromId()
    {
        $this->transferForm['storeFromId'] = '';
        // Clear details when warehouse changes
        $this->details = [];
        
        // Auto-select store if only one exists
        if (!empty($this->transferForm['warehouseFromId'])) {
            $this->ensureTenantConnection();
            $stores = InvStore::where('status', 1)
                ->where('warehouseId', $this->transferForm['warehouseFromId'])
                ->get();
            
            if ($stores->count() === 1) {
                $this->transferForm['storeFromId'] = $stores->first()->id;
            }
        }
    }
    
    /**
     * Watch for store from changes to clear details
     */
    public function updatedTransferFormStoreFromId()
    {
        // Clear details when store changes since available items will change
        $this->details = [];
        
        // If warehouses are the same, re-evaluate destination store auto-selection
        if (!empty($this->transferForm['warehouseFromId']) && 
            !empty($this->transferForm['warehouseToId']) && 
            $this->transferForm['warehouseFromId'] === $this->transferForm['warehouseToId']) {
            
            $this->ensureTenantConnection();
            $stores = InvStore::where('status', 1)
                ->where('warehouseId', $this->transferForm['warehouseToId'])
                ->get();
            
            // Exclude the origin store
            if (!empty($this->transferForm['storeFromId'])) {
                $stores = $stores->filter(function($store) {
                    return $store->id != $this->transferForm['storeFromId'];
                });
            }
            
            // Auto-select if only one option remains
            if ($stores->count() === 1) {
                $this->transferForm['storeToId'] = $stores->first()->id;
                $this->dispatch('$refresh');
            } elseif ($stores->count() === 0) {
                // Clear if no valid options
                $this->transferForm['storeToId'] = '';
            } elseif (!empty($this->transferForm['storeToId'])) {
                // Check if current selection is still valid
                $isValid = $stores->contains('id', $this->transferForm['storeToId']);
                if (!$isValid) {
                    $this->transferForm['storeToId'] = '';
                }
            }
        }
    }

    /**
     * Watch for warehouse changes to reset store selection
     */
    public function updatedTransferFormWarehouseToId()
    {
        $this->transferForm['storeToId'] = '';
        
        // Auto-select store if only one exists
        if (!empty($this->transferForm['warehouseToId'])) {
            $this->ensureTenantConnection();
            $stores = InvStore::where('status', 1)
                ->where('warehouseId', $this->transferForm['warehouseToId'])
                ->get();
            
            // If same warehouse as origin, exclude the origin store
            if ($this->transferForm['warehouseFromId'] === $this->transferForm['warehouseToId'] && !empty($this->transferForm['storeFromId'])) {
                $stores = $stores->filter(function($store) {
                    return $store->id != $this->transferForm['storeFromId'];
                });
            }
            
            if ($stores->count() === 1) {
                $this->transferForm['storeToId'] = $stores->first()->id;
                // Force Livewire to recognize the change and update computed properties
                $this->dispatch('$refresh');
            }
        }
    }
    
    /**
     * Watch for store destination changes to validate it's not the same as origin
     */
    public function updatedTransferFormStoreToId()
    {
        // Validate that the combination is not the same
        if (!empty($this->transferForm['warehouseFromId']) && 
            !empty($this->transferForm['warehouseToId']) &&
            !empty($this->transferForm['storeFromId']) && 
            !empty($this->transferForm['storeToId'])) {
            
            if ($this->transferForm['warehouseFromId'] === $this->transferForm['warehouseToId'] && 
                $this->transferForm['storeFromId'] === $this->transferForm['storeToId']) {
                $this->transferForm['storeToId'] = '';
                $this->errorMessage = 'No puede seleccionar la misma bodega de origen como destino';
            }
        }
    }

    /**
     * Show transfer details modal
     */
    public function showTransferDetails($transferId)
    {
        $this->ensureTenantConnection();
        $transfer = InvTransfer::with([
            'details.item',
            'storeFrom',
            'storeTo'
        ])->find($transferId);
        
        if ($transfer) {
            $this->transferDetails = [
                'id' => $transfer->id,
                'consecutive' => str_pad($transfer->consecutive, 6, '0', STR_PAD_LEFT),
                'date' => $transfer->date->format('d/m/Y H:i'),
                'warehouse_from' => $transfer->warehouseFrom->name ?? 'N/A',
                'store_from' => $transfer->storeFrom->name ?? 'N/A',
                'warehouse_to' => $transfer->warehouseTo->name ?? 'N/A',
                'store_to' => $transfer->storeTo->name ?? 'N/A',
                'user_name' => $transfer->user->name ?? 'N/A',
                'status' => $transfer->status,
                'packing' => $transfer->packing,
                'observations' => $transfer->observations,
                'details' => $transfer->details->map(function ($detail) {
                    return [
                        'item_name' => $detail->item->name ?? 'N/A',
                        'quantity' => number_format($detail->quantity, 2),
                        'amount_received' => number_format($detail->amount_received ?? 0, 2),
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
        $this->transferDetails = [];
        $this->clearMessages();
    }
    
    /**
     * Cancel/Void a transfer
     */
    public function cancelTransfer()
    {
        try {
            if (empty($this->transferDetails['id'])) {
                $this->errorMessage = 'No se encontró la transferencia';
                return;
            }
            
            $this->ensureTenantConnection();
            
            DB::connection('tenant')->beginTransaction();
            
            try {
                $transfer = InvTransfer::find($this->transferDetails['id']);
                
                if (!$transfer) {
                    throw new \Exception('Transferencia no encontrada');
                }
                
                // Check if transfer is already cancelled
                if ($transfer->status == 0) {
                    $this->errorMessage = 'La transferencia ya está anulada';
                    return;
                }
                
                // Update transfer status to cancelled (0)
                $transfer->status = 0;
                $transfer->save();
                
                DB::connection('tenant')->commit();
                
                $this->successMessage = 'Transferencia anulada exitosamente';
                
                Log::info('Transfer cancelled successfully', [
                    'transferId' => $transfer->id,
                    'userId' => Auth::id()
                ]);
                
                // Close modal and refresh list
                $this->closeDetailsModal();
                $this->dispatch('refreshTransfers');
                
            } catch (\Exception $e) {
                DB::connection('tenant')->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al anular la transferencia: ' . $e->getMessage();
            
            Log::error('Error cancelling transfer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Computed property for warehouses
     */
    #[Computed]
    public function warehouses()
    {
        $sessionTenant = session('tenant_id');
       
        // Obtener el tenant desde la base de datos usando el ID de sesión
        $tenant = Tenant::find($sessionTenant);
       
        if (!$tenant || !$tenant->company_id) {
            return collect([]);
        }
        
        // Traer todos los almacenes que coincidan con ese company_id
        return VntWarehouse::where('companyId', $tenant->company_id)
            ->where('status', true)
            ->with('company')
            ->orderBy('name')
            ->get();
    }
    

    /**
     * Computed property for items filtered by store with stock
     */
    #[Computed]
    public function items()
    {
        // If no store is selected, return empty collection
        if (empty($this->transferForm['storeFromId'])) {
            return collect([]);
        }
        
        // Get items that have stock in the selected store
        return Items::where('status', 1)
            ->whereHas('invItemsStore', function($query) {
                $query->where('storeId', $this->transferForm['storeFromId'])
                      ->where('stock_items_store', '>', 0);
            })
            ->with(['invItemsStore' => function($query) {
                $query->where('storeId', $this->transferForm['storeFromId']);
            }])
            ->orderBy('name')
            ->get();
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
     * Computed property for stores filtered by selected warehouse
     */
    #[Computed]
    public function storesFrom()
    {
        if (empty($this->transferForm['warehouseFromId'])) {
            return collect([]);
        }
        
        return InvStore::where('status', 1)
            ->where('warehouseId', $this->transferForm['warehouseFromId'])
            ->get();
    }

    /**
     * Computed property for destination stores filtered by selected warehouse
     */
    #[Computed]
    public function storesTo()
    {
        if (empty($this->transferForm['warehouseToId'])) {
            return collect([]);
        }
        
        return InvStore::where('status', 1)
            ->where('warehouseId', $this->transferForm['warehouseToId'])
            ->get();
    }
    
    /**
     * Check if the items section should be shown
     */
    #[Computed]
    public function canShowItemsSection()
    {
        return !empty($this->transferForm['warehouseFromId']) && 
               !empty($this->transferForm['warehouseToId']) && 
               !empty($this->transferForm['storeFromId']) && 
               !empty($this->transferForm['storeToId']);
    }

    /**
     * Check if transfers can be created
     */
    #[Computed]
    public function canCreateTransfer()
    {
        $this->ensureTenantConnection();
        
        $warehouseCount = $this->warehouses->count();
        
        // If only one warehouse, check if it has at least 2 stores
        if ($warehouseCount === 1) {
            $warehouse = $this->warehouses->first();
            $storeCount = InvStore::where('status', 1)
                ->where('warehouseId', $warehouse->id)
                ->count();
            
            return $storeCount >= 2;
        }
        
        // If multiple warehouses, we need at least 2 warehouses with at least 1 store each
        if ($warehouseCount >= 2) {
            $warehousesWithStores = 0;
            foreach ($this->warehouses as $warehouse) {
                $storeCount = InvStore::where('status', 1)
                    ->where('warehouseId', $warehouse->id)
                    ->count();
                if ($storeCount > 0) {
                    $warehousesWithStores++;
                }
            }
            return $warehousesWithStores >= 2;
        }
        
        return false;
    }

    /**
     * Get validation message for why transfers cannot be created
     */
    #[Computed]
    public function transferValidationMessage()
    {
        $this->ensureTenantConnection();
        
        $warehouseCount = $this->warehouses->count();
        
        if ($warehouseCount === 0) {
            return 'No hay sucursales disponibles para realizar transferencias.';
        }
        
        if ($warehouseCount === 1) {
            $warehouse = $this->warehouses->first();
            $storeCount = InvStore::where('status', 1)
                ->where('warehouseId', $warehouse->id)
                ->count();
            
            if ($storeCount < 2) {
                return 'Se necesitan al menos 2 bodegas en la sucursal para realizar transferencias.';
            }
        }
        
        if ($warehouseCount >= 2) {
            $warehousesWithStores = 0;
            foreach ($this->warehouses as $warehouse) {
                $storeCount = InvStore::where('status', 1)
                    ->where('warehouseId', $warehouse->id)
                    ->count();
                if ($storeCount > 0) {
                    $warehousesWithStores++;
                }
            }
            
            if ($warehousesWithStores < 2) {
                return 'Se necesitan al menos 2 sucursales con bodegas activas para realizar transferencias.';
            }
        }
        
        return '';
    }

    /**
     * Open modal to create new transfer
     */
    public function create()
    {
        // Validate if transfers can be created
        if (!$this->canCreateTransfer) {
            $this->errorMessage = $this->transferValidationMessage;
            return;
        }
        
        $this->showModal = true;
        $this->transferForm['date'] = now()->format('Y-m-d');
        $this->clearMessages();
    }
    
    /**
     * Add item detail to the table
     */
    public function addDetail()
    {
        try {
            $this->ensureTenantConnection();
            
            // Validate store selection
            if (empty($this->transferForm['storeFromId'])) {
                $this->errorMessage = 'Debe seleccionar la bodega de origen';
                return;
            }
            
            // Get item info
            $item = Items::with(['invValues', 'purchasingUnit', 'consumptionUnit'])
                ->findOrFail($this->detailForm['itemId']);
            $unitMeasurement = UnitMeasurements::find($this->detailForm['unitMeasurementId']);
            
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
                
                // Recalculate stock
                $quantityInConsumptionUnit = $unitMeasurement->quantity * $newQuantity;
                $this->details[$existingIndex]['quantityInConsumptionUnit'] = $quantityInConsumptionUnit;
                
                // Validate stock
                if ($this->details[$existingIndex]['currentStock'] < $quantityInConsumptionUnit) {
                    $this->errorMessage = 'Stock insuficiente en la bodega de origen';
                    return;
                }
            } else {
                // Get current stock from source store
                $itemStore = InvItemsStore::where('itemId', $this->detailForm['itemId'])
                    ->where('storeId', $this->transferForm['storeFromId'])
                    ->first();
                
                $currentStock = $itemStore ? $itemStore->stock_items_store : 0;
                $quantityInConsumptionUnit = $unitMeasurement->quantity * $quantity;
                
                // Validate stock
                if ($currentStock < $quantityInConsumptionUnit) {
                    $this->errorMessage = 'Stock insuficiente en la bodega de origen';
                    return;
                }
                
                // Add new detail
                $this->details[] = [
                    'itemId' => $this->detailForm['itemId'],
                    'itemName' => $item->name,
                    'sku' => $item->sku ?? 'N/A',
                    'quantity' => $quantity,
                    'unitMeasurementId' => $this->detailForm['unitMeasurementId'],
                    'unitMeasurementName' => $unitMeasurement->description,
                    'quantityInConsumptionUnit' => $quantityInConsumptionUnit,
                    'currentStock' => $currentStock
                ];
            }

            // Reset detail form
            $this->resetDetailForm();
            $this->clearMessages();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar el item: ' . $e->getMessage();
            Log::error('Error adding detail to transfer', [
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
            $this->details = array_values($this->details);
        }
    }
    
    /**
     * Save transfer with details
     */
    public function saveTransfer()
    {
        try {
            // Clear previous messages
            $this->clearMessages();
            
            // Validate required fields
            if (empty($this->transferForm['warehouseFromId'])) {
                $this->errorMessage = 'Debe seleccionar la sucursal de origen';
                return;
            }
            
            if (empty($this->transferForm['warehouseToId'])) {
                $this->errorMessage = 'Debe seleccionar la sucursal de destino';
                return;
            }
            
            if (empty($this->transferForm['storeFromId'])) {
                $this->errorMessage = 'Debe seleccionar la bodega de origen';
                return;
            }
            
            if (empty($this->transferForm['storeToId'])) {
                $this->errorMessage = 'Debe seleccionar la bodega de destino';
                return;
            }
            
            // CRITICAL VALIDATION: The combination of warehouse + store must ALWAYS be different
            // This means we cannot transfer from the same warehouse AND same store
            if ($this->transferForm['warehouseFromId'] === $this->transferForm['warehouseToId'] && 
                $this->transferForm['storeFromId'] === $this->transferForm['storeToId']) {
                $this->errorMessage = 'La combinación de sucursal y bodega de origen y destino no puede ser la misma';
                return;
            }
            
            // Validate that there are details
            if (empty($this->details) || count($this->details) === 0) {
                $this->errorMessage = 'Debe agregar al menos un item a la transferencia';
                return;
            }
            
            $this->ensureTenantConnection();
            
            DB::connection('tenant')->beginTransaction();
            
            try {
                // Get next consecutive
                $lastTransfer = InvTransfer::orderBy('consecutive', 'desc')->first();
                $consecutive = $lastTransfer ? $lastTransfer->consecutive + 1 : 1;
                 // Get stores directly by ID
                $storeFrom = InvStore::find($this->transferForm['storeFromId']);
                $storeTo = InvStore::find($this->transferForm['storeToId']);

                // Create transfer
                $transfer = InvTransfer::create([
                    'date' => $this->transferForm['date'],
                    'observations' => $this->transferForm['observations'],
                    'status' => 'REGISTRADO',
                    'storeFromId' => $this->transferForm['storeFromId'],
                    'storeToId' => $this->transferForm['storeToId'],
                    'consecutive' => $consecutive,
                    'userId' => Auth::id(),
                    'packing' => 0,
                ]);
                
             
                
                if (!$storeFrom) {
                    throw new \Exception('No se encontró la bodega de origen');
                }
                
                if (!$storeTo) {
                    throw new \Exception('No se encontró la bodega de destino');
                }
                
                // Create details and update stock
                foreach ($this->details as $detail) {

                    InvDetailTransfer::create([
                        'transferId' => $transfer->id,
                        'itemId' => $detail['itemId'],
                        'quantity' => $detail['quantity'],
                        'unitMeasurementId' => $detail['unitMeasurementId']
                    ]);

                    // Update stock in source warehouse (decrease)
                    // $itemStoreFrom = InvItemsStore::where('itemId', $detail['itemId'])
                    //     ->where('storeId', $storeFrom->id)
                    //     ->first();
                    
                    // if ($itemStoreFrom) {
                    //     $itemStoreFrom->stock_items_store -= $detail['quantityInConsumptionUnit'];
                    //     $itemStoreFrom->save();
                    // }

                    // // Update stock in destination warehouse (increase)
                    // $itemStoreTo = InvItemsStore::where('itemId', $detail['itemId'])
                    //     ->where('storeId', $storeTo->id)
                    //     ->first();
                    
                    // if ($itemStoreTo) {
                    //     $itemStoreTo->stock_items_store += $detail['quantityInConsumptionUnit'];
                    //     $itemStoreTo->save();
                    // } else {
                    //     // Create new item store record if it doesn't exist
                    //     InvItemsStore::create([
                    //         'itemId' => $detail['itemId'],
                    //         'storeId' => $storeTo->id,
                    //         'stock_items_store' => $detail['quantityInConsumptionUnit'],
                    //     ]);
                    // }
                }
                
                DB::connection('tenant')->commit();
                
                $this->successMessage = 'Transferencia creada exitosamente';
                
                Log::info('Transfer created successfully', [
                    'transferId' => $transfer->id,
                    'detailsCount' => count($this->details)
                ]);
                
                // Reset form and close modal
                $this->resetForm();
                $this->showModal = false;
                
                // Refresh the transfer list
                $this->dispatch('refreshTransfers');
                
            } catch (\Exception $e) {
                DB::connection('tenant')->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al guardar la transferencia: ' . $e->getMessage();
            
            Log::error('Error saving transfer', [
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
     * Reset transfer form
     */
    private function resetForm()
    {
        $this->transferForm = [
            'date' => now()->format('Y-m-d'),
            'warehouseFromId' => '',
            'warehouseToId' => '',
            'storeFromId' => '',
            'storeToId' => '',
            'observations' => '',
        ];
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
        
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        return view('livewire.tenant.transfers.transfer-form');
    }
}
