<?php

namespace App\Livewire\Tenant\Transfers\Components;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Tenant\Transfers\InvTransferRequest;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferRequestList extends Component
{
    use WithPagination;

    // Search and filtering
    public string $search = '';
    
    // Pagination
    public int $perPage = 10;
    
    // Sorting
    public string $sortField = 'date';
    public string $sortDirection = 'desc';
    
    // Modal state
    public bool $showDetailsModal = false;
    public array $requestDetails = [];
    
    // Transfer modal state
    public bool $showTransferModal = false;
    public ?int $selectedItemDetailId = null;
    public ?int $selectedDestinationStoreId = null;
    public int $transferQuantity = 0;
    public array $itemTransferData = [];
    
    // Multi-item transfer state
    public bool $showMultiTransferModal = false;
    public array $selectedItems = []; // Array of item detail IDs
    public array $itemQuantities = []; // Array of quantities per item [detailId => quantity]
    
    // Messages
    public string $errorMessage = '';
    public string $successMessage = '';

    /**
     * Reset pagination when search is updated
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update item quantities when warehouse selection changes
     */
    public function updatedSelectedDestinationStoreId(): void
    {
        // Clear selections when warehouse changes
        if (empty($this->selectedDestinationStoreId)) {
            $this->selectedItems = [];
            $this->itemQuantities = [];
            unset($this->selectedItemsStockInfo);
            return;
        }

        // Recalculate quantities for already selected items
        if (!empty($this->selectedItems)) {
            $this->ensureTenantConnection();
            
            foreach ($this->selectedItems as $detailId) {
                $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::with('item')->find($detailId);
                if (!$detail) {
                    continue;
                }

                // Get available stock in the new origin warehouse
                $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $detail->itemId)
                    ->where('storeId', $this->selectedDestinationStoreId)
                    ->first();

                $physicalStock = $stockRecord ? $stockRecord->stock_items_store : 0;

                // Calculate committed quantity
                $committedQuantity = DB::connection('tenant')
                    ->table('inv_detail_transfers')
                    ->join('inv_transfers', 'inv_detail_transfers.transferId', '=', 'inv_transfers.id')
                    ->where('inv_transfers.storeFromId', $this->selectedDestinationStoreId)
                    ->where('inv_detail_transfers.itemId', $detail->itemId)
                    ->whereIn('inv_transfers.status', ['REGISTRADO', 'EN TRANSITO'])
                    ->whereNull('inv_transfers.deleted_at')
                    ->whereNull('inv_detail_transfers.deleted_at')
                    ->sum('inv_detail_transfers.quantity');

                $availableStock = max(0, $physicalStock - $committedQuantity);
                $remainingToSend = $detail->quantity - $detail->quantitySend;
                $maxTransferable = min($remainingToSend, $availableStock);

                // Update quantity to the new maximum transferable
                $this->itemQuantities[$detailId] = $maxTransferable;
            }
            
            // Force recompute of stock info
            unset($this->selectedItemsStockInfo);
        }
    }

    /**
     * Sort by a specific field
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            // Toggle direction if same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Open details modal for a specific transfer request
     */
    public function openDetailsModal(int $requestId): void
    {
        try {
            $this->ensureTenantConnection();
            
            $request = InvTransferRequest::with([
                'store', 
                'store.warehouse',
                'detailTransferRequests.item'
            ])->find($requestId);
            
            if (!$request) {
                $this->errorMessage = 'Solicitud de transferencia no encontrada';
                return;
            }
            
            // Prepare items data
            $items = $request->detailTransferRequests->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'itemName' => $detail->item ? $detail->item->name : 'Item no especificado',
                    'quantity' => $detail->quantity,
                    'quantitySend' => $detail->quantitySend,
                ];
            })->toArray();
            
            $this->requestDetails = [
                'id' => $request->id,
                'status' => $request->status ?? 'REGISTRADO',
                'date' => $request->formatted_date,
                'warehouse' => optional($request->store)->warehouse->name ?? 'N/A',
                'store' => optional($request->store)->name ?? 'N/A',
                'quoteId' => $request->quoteId ?? '-',
                'observations' => $request->observations ?? 'N/A',
                'created_at' => $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'N/A',
                'updated_at' => $request->updated_at ? $request->updated_at->format('d/m/Y H:i') : 'N/A',
                'status_badge_class' => $request->status_badge_class ?? 'bg-gray-100 text-gray-800',
                'items' => $items,
            ];
            
            $this->showDetailsModal = true;
            $this->errorMessage = '';
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al cargar los detalles: ' . $e->getMessage();
            Log::error('Error loading transfer request details', [
                'requestId' => $requestId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Close details modal
     */
    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->requestDetails = [];
        $this->selectedDestinationStoreId = null;
        $this->selectedItems = [];
        $this->itemQuantities = [];
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    /**
     * Toggle item selection for multi-item transfer
     */
    public function toggleItemSelection(int $detailId): void
    {
        if (in_array($detailId, $this->selectedItems)) {
            // Remove from selection
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$detailId]));
            unset($this->itemQuantities[$detailId]);
        } else {
            // Add to selection
            $this->selectedItems[] = $detailId;
            
            // Initialize quantity to max transferable
            $this->ensureTenantConnection();
            $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::find($detailId);
            if ($detail) {
                $remainingToSend = $detail->quantity - $detail->quantitySend;
                $this->itemQuantities[$detailId] = $remainingToSend;
            }
        }
    }

    /**
     * Update quantity for a specific item in multi-item transfer
     */
    public function updateItemQuantity(int $detailId, int $quantity): void
    {
        // Ensure quantity is never negative
        $this->itemQuantities[$detailId] = max(0, $quantity);
    }

    /**
     * Set item quantity to maximum available for multi-item transfer
     */
    public function setItemQuantityToMax(int $detailId): void
    {
        if (!$this->selectedDestinationStoreId) {
            return;
        }

        $this->ensureTenantConnection();
        
        $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::with('item')->find($detailId);
        if (!$detail) {
            return;
        }

        // Get available stock in origin warehouse
        $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $detail->itemId)
            ->where('storeId', $this->selectedDestinationStoreId)
            ->first();

        $physicalStock = $stockRecord ? $stockRecord->stock_items_store : 0;

        // Calculate committed quantity
        $committedQuantity = DB::connection('tenant')
            ->table('inv_detail_transfers')
            ->join('inv_transfers', 'inv_detail_transfers.transferId', '=', 'inv_transfers.id')
            ->where('inv_transfers.storeFromId', $this->selectedDestinationStoreId)
            ->where('inv_detail_transfers.itemId', $detail->itemId)
            ->whereIn('inv_transfers.status', ['REGISTRADO', 'EN TRANSITO'])
            ->whereNull('inv_transfers.deleted_at')
            ->whereNull('inv_detail_transfers.deleted_at')
            ->sum('inv_detail_transfers.quantity');

        $availableStock = max(0, $physicalStock - $committedQuantity);
        $remainingToSend = $detail->quantity - $detail->quantitySend;
        $maxTransferable = min($remainingToSend, $availableStock);

        $this->itemQuantities[$detailId] = $maxTransferable;
    }

    /**
     * Open multi-item transfer modal
     */
    public function openMultiTransferModal(): void
    {
        if (empty($this->selectedItems)) {
            $this->errorMessage = 'Debe seleccionar al menos un item';
            return;
        }

        if (!$this->selectedDestinationStoreId) {
            $this->errorMessage = 'Debe seleccionar una bodega de origen primero';
            return;
        }

        $this->showMultiTransferModal = true;
        $this->errorMessage = '';
    }

    /**
     * Close multi-item transfer modal
     */
    public function closeMultiTransferModal(): void
    {
        $this->showMultiTransferModal = false;
        $this->errorMessage = '';
    }

    /**
     * Get stock information for selected items
     */
    #[Computed]
    public function selectedItemsStockInfo()
    {
        if (empty($this->selectedItems) || !$this->selectedDestinationStoreId) {
            return [];
        }

        $this->ensureTenantConnection();
        
        $stockInfo = [];
        
        foreach ($this->selectedItems as $detailId) {
            $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::with('item')->find($detailId);
            if (!$detail) {
                continue;
            }

            // Get available stock in origin warehouse
            $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $detail->itemId)
                ->where('storeId', $this->selectedDestinationStoreId)
                ->first();

            $physicalStock = $stockRecord ? $stockRecord->stock_items_store : 0;

            // Calculate committed quantity
            $committedQuantity = DB::connection('tenant')
                ->table('inv_detail_transfers')
                ->join('inv_transfers', 'inv_detail_transfers.transferId', '=', 'inv_transfers.id')
                ->where('inv_transfers.storeFromId', $this->selectedDestinationStoreId)
                ->where('inv_detail_transfers.itemId', $detail->itemId)
                ->whereIn('inv_transfers.status', ['REGISTRADO', 'EN TRANSITO'])
                ->whereNull('inv_transfers.deleted_at')
                ->whereNull('inv_detail_transfers.deleted_at')
                ->sum('inv_detail_transfers.quantity');

            $availableStock = max(0, $physicalStock - $committedQuantity);
            $remainingToSend = $detail->quantity - $detail->quantitySend;
            $maxTransferable = min($remainingToSend, $availableStock);

            $stockInfo[$detailId] = [
                'physicalStock' => $physicalStock,
                'committedQuantity' => $committedQuantity,
                'availableStock' => $availableStock,
                'remainingToSend' => $remainingToSend,
                'maxTransferable' => $maxTransferable,
            ];
        }

        return $stockInfo;
    }

    /**
     * Execute multi-item transfer
     */
    public function executeMultiTransfer(): void
    {
        try {
            if (empty($this->selectedItems)) {
                $this->errorMessage = 'Debe seleccionar al menos un item';
                return;
            }

            if (!$this->selectedDestinationStoreId) {
                $this->errorMessage = 'Debe seleccionar una bodega de origen';
                return;
            }

            $this->ensureTenantConnection();

            // Get the transfer request to know the destination
            $firstDetail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::find($this->selectedItems[0]);
            if (!$firstDetail) {
                $this->errorMessage = 'Detalle de solicitud no encontrado';
                return;
            }

            $request = \App\Models\Tenant\Transfers\InvTransferRequest::find($firstDetail->transferRequestId);
            if (!$request) {
                $this->errorMessage = 'Solicitud de transferencia no encontrada';
                return;
            }

            // Validate that origin and destination are different
            if ($this->selectedDestinationStoreId === $request->warehouseId) {
                $this->errorMessage = 'La bodega de origen no puede ser la misma que la bodega de destino';
                return;
            }

            // Validate all items and quantities
            $itemsToTransfer = [];
            foreach ($this->selectedItems as $detailId) {
                $quantity = $this->itemQuantities[$detailId] ?? 0;
                
                // Ensure quantity is never negative
                if ($quantity < 0) {
                    $this->errorMessage = 'Las cantidades no pueden ser negativas';
                    return;
                }
                
                if ($quantity <= 0) {
                    $this->errorMessage = 'Todas las cantidades deben ser mayores a cero';
                    return;
                }

                $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::with('item')->find($detailId);
                if (!$detail) {
                    $this->errorMessage = 'Detalle de solicitud no encontrado';
                    return;
                }

                // Verify item is active
                if (!$detail->item || $detail->item->status != 1) {
                    $this->errorMessage = 'El item ' . ($detail->item->name ?? 'desconocido') . ' no está activo';
                    return;
                }

                // Get available stock
                $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $detail->itemId)
                    ->where('storeId', $this->selectedDestinationStoreId)
                    ->first();

                $physicalStock = $stockRecord ? $stockRecord->stock_items_store : 0;

                // Calculate committed quantity
                $committedQuantity = DB::connection('tenant')
                    ->table('inv_detail_transfers')
                    ->join('inv_transfers', 'inv_detail_transfers.transferId', '=', 'inv_transfers.id')
                    ->where('inv_transfers.storeFromId', $this->selectedDestinationStoreId)
                    ->where('inv_detail_transfers.itemId', $detail->itemId)
                    ->whereIn('inv_transfers.status', ['REGISTRADO', 'EN TRANSITO'])
                    ->whereNull('inv_transfers.deleted_at')
                    ->whereNull('inv_detail_transfers.deleted_at')
                    ->sum('inv_detail_transfers.quantity');

                $availableStock = max(0, $physicalStock - $committedQuantity);
                $remainingToSend = $detail->quantity - $detail->quantitySend;

                if ($quantity > $availableStock) {
                    $this->errorMessage = 'La cantidad para ' . $detail->item->name . ' excede el stock disponible (' . $availableStock . ')';
                    return;
                }

                if ($quantity > $remainingToSend) {
                    $this->errorMessage = 'La cantidad para ' . $detail->item->name . ' excede lo que falta por enviar (' . $remainingToSend . ')';
                    return;
                }

                $itemsToTransfer[] = [
                    'detailId' => $detailId,
                    'itemId' => $detail->itemId,
                    'quantity' => $quantity,
                    'stockRecord' => $stockRecord,
                ];
            }

            // Execute transfer in transaction
            DB::connection('tenant')->transaction(function () use ($itemsToTransfer, $request) {
                // Get next consecutive
                $lastTransfer = \App\Models\Tenant\Transfers\InvTransfer::orderBy('consecutive', 'desc')->first();
                $consecutive = $lastTransfer ? $lastTransfer->consecutive + 1 : 1;

                // Create transfer
                $transfer = \App\Models\Tenant\Transfers\InvTransfer::create([
                    'date' => now(),
                    'observations' => 'Transferencia múltiple desde solicitud #' . $request->id,
                    'status' => 'REGISTRADO',
                    'storeFromId' => $this->selectedDestinationStoreId,
                    'storeToId' => $request->warehouseId,
                    'consecutive' => $consecutive,
                    'userId' => Auth::check() ? Auth::id() : null,
                ]);

                // Create transfer details and update stocks
                foreach ($itemsToTransfer as $item) {
                    // Create transfer detail
                    \App\Models\Tenant\Transfers\InvDetailTransfer::create([
                        'quantity' => $item['quantity'],
                        'transferId' => $transfer->id,
                        'itemId' => $item['itemId'],
                        'amount_received' => 0,
                    ]);

                    // Update request detail - increment sent quantity
                    $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::find($item['detailId']);
                    $detail->quantitySend += $item['quantity'];
                    $detail->save();

                    // Update stock in origin warehouse (reduce stock)
                    if ($item['stockRecord']) {
                        $item['stockRecord']->stock_items_store -= $item['quantity'];
                        $item['stockRecord']->save();
                    }
                }

                // Update request status
                $this->updateRequestStatus($request->id);
            });

            $this->successMessage = 'Transferencia múltiple creada exitosamente';
            
            // Save requestId before closing
            $requestId = $request->id;
            
            // Clear selections
            $this->selectedItems = [];
            $this->itemQuantities = [];
            
            $this->closeMultiTransferModal();
            
            // Refresh the details modal
            $this->openDetailsModal($requestId);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al ejecutar la transferencia múltiple: ' . $e->getMessage();
            Log::error('Error executing multi-item transfer', [
                'selectedItems' => $this->selectedItems,
                'itemQuantities' => $this->itemQuantities,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get available stores for destination selection
     */
    #[Computed]
    public function availableStores()
    {
        $this->ensureTenantConnection();
        
        // Get the destination store ID from the current request
        $destinationStoreId = !empty($this->requestDetails['id']) 
            ? InvTransferRequest::find($this->requestDetails['id'])->warehouseId ?? null
            : null;
        
        return \App\Models\Tenant\Items\InvStore::with('warehouse')
            ->where('status', 1)
            ->when($destinationStoreId, function ($query) use ($destinationStoreId) {
                // Exclude the destination store from the list
                $query->where('id', '!=', $destinationStoreId);
            })
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'warehouse_name' => $store->warehouse->name ?? 'N/A',
                    'display_name' => $store->name . ' - ' . ($store->warehouse->name ?? 'N/A'),
                ];
            });
    }

    /**
     * Open transfer modal for a specific item
     */
    public function openTransferModal(int $detailId): void
    {
        try {
            if (!$this->selectedDestinationStoreId) {
                $this->errorMessage = 'Debe seleccionar una bodega de origen primero';
                return;
            }

            $this->ensureTenantConnection();
            
            $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::with('item')
                ->find($detailId);
            
            if (!$detail) {
                $this->errorMessage = 'Detalle de solicitud no encontrado';
                return;
            }

            // Verify item is active (status = 1)
            if (!$detail->item || $detail->item->status != 1) {
                $this->errorMessage = 'El item no está activo o no existe';
                return;
            }

            // Get the transfer request to know the destination warehouse
            $request = \App\Models\Tenant\Transfers\InvTransferRequest::find($detail->transferRequestId);
            if (!$request) {
                $this->errorMessage = 'Solicitud de transferencia no encontrada';
                return;
            }

            // Get available stock in ORIGIN warehouse (selected by user)
            $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $detail->itemId)
                ->where('storeId', $this->selectedDestinationStoreId) // This is actually the origin
                ->first();

            $physicalStock = $stockRecord ? $stockRecord->stock_items_store : 0;

            // Calculate committed quantity (items in transfers that are not delivered)
            // Transfers FROM this store that are not ENTREGADO
            $committedQuantity = DB::connection('tenant')
                ->table('inv_detail_transfers')
                ->join('inv_transfers', 'inv_detail_transfers.transferId', '=', 'inv_transfers.id')
                ->where('inv_transfers.storeFromId', $this->selectedDestinationStoreId)
                ->where('inv_detail_transfers.itemId', $detail->itemId)
                ->whereIn('inv_transfers.status', ['REGISTRADO', 'EN TRANSITO'])
                ->whereNull('inv_transfers.deleted_at')
                ->whereNull('inv_detail_transfers.deleted_at')
                ->sum('inv_detail_transfers.quantity');

            // Available stock = physical stock - committed quantity
            $availableStock = max(0, $physicalStock - $committedQuantity);
            
            $remainingToSend = $detail->quantity - $detail->quantitySend;
            $maxTransferable = min($remainingToSend, $availableStock);

            $this->itemTransferData = [
                'detailId' => $detail->id,
                'itemId' => $detail->itemId,
                'itemName' => $detail->item->name ?? 'N/A',
                'requestedQuantity' => $detail->quantity,
                'sentQuantity' => $detail->quantitySend,
                'remainingToSend' => $remainingToSend,
                'physicalStock' => $physicalStock,
                'committedQuantity' => $committedQuantity,
                'availableStock' => $availableStock,
                'maxTransferable' => $maxTransferable,
                'originStoreId' => $this->selectedDestinationStoreId, // Origin is what user selected
                'destinationStoreId' => $request->warehouseId, // Destination is from the request
                'requestId' => $request->id,
            ];

            $this->transferQuantity = $maxTransferable;
            $this->selectedItemDetailId = $detailId;
            $this->showTransferModal = true;
            $this->errorMessage = '';
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al abrir modal de transferencia: ' . $e->getMessage();
            Log::error('Error opening transfer modal', [
                'detailId' => $detailId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Close transfer modal
     */
    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->selectedItemDetailId = null;
        $this->transferQuantity = 0;
        $this->itemTransferData = [];
    }

    /**
     * Set transfer quantity to all available stock
     */
    public function setQuantityToAvailable(): void
    {
        if (!empty($this->itemTransferData)) {
            $this->transferQuantity = $this->itemTransferData['availableStock'];
        }
    }

    /**
     * Set transfer quantity to requested amount
     */
    public function setQuantityToRequested(): void
    {
        if (!empty($this->itemTransferData)) {
            $this->transferQuantity = $this->itemTransferData['maxTransferable'];
        }
    }

    /**
     * Execute the transfer
     */
    public function executeTransfer(): void
    {
        try {
            // Validations
            if ($this->transferQuantity <= 0) {
                $this->errorMessage = 'La cantidad debe ser mayor a cero';
                return;
            }

            if ($this->transferQuantity > $this->itemTransferData['availableStock']) {
                $this->errorMessage = 'La cantidad excede el stock disponible';
                return;
            }

            if ($this->transferQuantity > $this->itemTransferData['maxTransferable']) {
                $this->errorMessage = 'La cantidad excede lo que falta por enviar';
                return;
            }

            // Validate that origin and destination are different
            if ($this->itemTransferData['originStoreId'] === $this->itemTransferData['destinationStoreId']) {
                $this->errorMessage = 'La bodega de origen no puede ser la misma que la bodega de destino';
                return;
            }

            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () {
                // Get next consecutive
                $lastTransfer = \App\Models\Tenant\Transfers\InvTransfer::orderBy('consecutive', 'desc')->first();
                $consecutive = $lastTransfer ? $lastTransfer->consecutive + 1 : 1;

                // Create transfer
                // storeFromId = origin (selected by user)
                // storeToId = destination (from the request warehouseId)
                $transfer = \App\Models\Tenant\Transfers\InvTransfer::create([
                    'date' => now(),
                    'observations' => 'Transferencia desde solicitud #' . $this->itemTransferData['requestId'],
                    'status' => 'REGISTRADO',
                    'storeFromId' => $this->itemTransferData['originStoreId'],
                    'storeToId' => $this->itemTransferData['destinationStoreId'],
                    'consecutive' => $consecutive,
                    'userId' => Auth::check() ? Auth::id() : null,
                ]);

                // Create transfer detail
                \App\Models\Tenant\Transfers\InvDetailTransfer::create([
                    'quantity' => $this->transferQuantity,
                    'transferId' => $transfer->id,
                    'itemId' => $this->itemTransferData['itemId'],
                    'amount_received' => 0,
                ]);

                // Update request detail - increment sent quantity
                $detail = \App\Models\Tenant\Transfers\InvDetailTransferRequest::find($this->itemTransferData['detailId']);
                $detail->quantitySend += $this->transferQuantity;
                $detail->save();

                // Update stock in ORIGIN warehouse (reduce stock)
                $stockRecord = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $this->itemTransferData['itemId'])
                    ->where('storeId', $this->itemTransferData['originStoreId'])
                    ->first();

                if ($stockRecord) {
                    $stockRecord->stock_items_store -= $this->transferQuantity;
                    $stockRecord->save();
                }

                // Update request status
                $this->updateRequestStatus($this->itemTransferData['requestId']);
            });

            $this->successMessage = 'Transferencia creada exitosamente';
            
            // Save requestId before closing modal (which clears itemTransferData)
            $requestId = $this->itemTransferData['requestId'];
            
            $this->closeTransferModal();
            
            // Refresh the details modal
            $this->openDetailsModal($requestId);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al ejecutar la transferencia: ' . $e->getMessage();
            Log::error('Error executing transfer', [
                'itemTransferData' => $this->itemTransferData,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update request status based on items completion
     */
    private function updateRequestStatus(int $requestId): void
    {
        $request = \App\Models\Tenant\Transfers\InvTransferRequest::with('detailTransferRequests')
            ->find($requestId);

        if (!$request) {
            return;
        }

        $allComplete = true;
        $anyPartial = false;

        foreach ($request->detailTransferRequests as $detail) {
            if ($detail->quantitySend >= $detail->quantity) {
                // Item is complete
                continue;
            } elseif ($detail->quantitySend > 0) {
                // Item is partial
                $anyPartial = true;
                $allComplete = false;
            } else {
                // Item is pending
                $allComplete = false;
            }
        }

        if ($allComplete) {
            $request->status = 'ENTREGADO';
        } elseif ($anyPartial) {
            $request->status = 'EN PROGRESO';
        } else {
            $request->status = 'REGISTRADO';
        }

        $request->save();
    }

    /**
     * Get transfer history for current request
     */
    #[Computed]
    public function transferHistory()
    {
        if (empty($this->requestDetails['id'])) {
            return collect();
        }

        $this->ensureTenantConnection();

        return \App\Models\Tenant\Transfers\InvTransfer::with(['storeTo', 'user'])
            ->where('observations', 'like', '%solicitud #' . $this->requestDetails['id'] . '%')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($transfer) {
                return [
                    'id' => $transfer->id,
                    'date' => $transfer->date->format('d/m/Y H:i'),
                    'destination' => $transfer->storeTo->name ?? 'N/A',
                    'warehouse' => $transfer->warehouse_to->name ?? 'N/A',
                    'status' => $transfer->status,
                    'user' => $transfer->user->name ?? 'N/A',
                    'consecutive' => $transfer->consecutive,
                ];
            });
    }

    /**
     * Get paginated transfer requests with search and sorting
     */
    #[Computed]
    public function transferRequests()
    {
        $this->ensureTenantConnection();
        
        return InvTransferRequest::query()
            ->with(['store', 'store.warehouse'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('date', 'like', "%{$this->search}%")
                        ->orWhere('observations', 'like', "%{$this->search}%")
                        ->orWhereHas('store', function ($wq) {
                            $wq->where('name', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('store.warehouse', function ($wq) {
                            $wq->where('name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
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
        return view('livewire.tenant.transfers.components.transfer-request-list');
    }
}
