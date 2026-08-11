<?php

namespace App\Livewire\Tenant\Transfers\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Transfers\InvTransfer;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\InvItemsStore;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';
    
    // Modal state for sending transfer
    public $showSendModal = false;
    public $selectedTransferId = null;
    public $selectedTransferConsecutive = '';
    
    // Packing details
    public $packingBags = 0;
    public $packingBaskets = 0;
    public $packingBoxes = 0;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    protected $listeners = ['refreshTransfers' => 'refreshList'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function refreshList()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function getTransfersProperty()
    {
        $this->ensureTenantConnection();
        return InvTransfer::query()
            ->with(['storeFrom', 'storeTo'])
            ->withCount('details')
            ->withSum('details as total_quantity', 'quantity')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('consecutive', 'like', '%' . $this->search . '%')
                        ->orWhere('observations', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function openDetailsModal($transferId)
    {
        $this->dispatch('showTransferDetails', transferId: $transferId);
    }

    public function annulTransfer($transferId)
    {
        try {
            $this->ensureTenantConnection();
            
            DB::connection('tenant')->beginTransaction();
            
            $transfer = InvTransfer::with('details')->find($transferId);
            
            if (!$transfer) {
                $this->dispatch('notify', type: 'error', message: 'Transferencia no encontrada');
                return;
            }

            if ($transfer->status === 'ANULADO') {
                $this->dispatch('notify', type: 'warning', message: 'Esta transferencia ya está anulada');
                return;
            }

            // Get stores directly
            $storeFrom = $transfer->storeFrom;
            $storeTo = $transfer->storeTo;

            if (!$storeFrom || !$storeTo) {
                DB::connection('tenant')->rollBack();
                $this->dispatch('notify', type: 'error', message: 'No se encontraron las bodegas de origen o destino');
                return;
            }

            // Reverse the inventory changes
            foreach ($transfer->details as $detail) {
                // Get unit measurement to calculate quantity in consumption unit
                $unitMeasurement = \App\Models\Tenant\Items\UnitMeasurements::find($detail->unitMeasurementId ?? 1);
                $quantityInConsumptionUnit = $detail->quantity * ($unitMeasurement ? $unitMeasurement->quantity : 1);

                // Reverse in source warehouse (add back)
                $itemStoreFrom = InvItemsStore::where('itemId', $detail->itemId)
                    ->where('storeId', $storeFrom->id)
                    ->first();
                
                if ($itemStoreFrom) {
                    $itemStoreFrom->stock_items_store += $quantityInConsumptionUnit;
                    $itemStoreFrom->save();
                }

                // Reverse in destination warehouse (subtract)
                $itemStoreTo = InvItemsStore::where('itemId', $detail->itemId)
                    ->where('storeId', $storeTo->id)
                    ->first();
                
                if ($itemStoreTo) {
                    // Validate there's enough stock to reverse
                    if ($itemStoreTo->stock_items_store < $quantityInConsumptionUnit) {
                        DB::connection('tenant')->rollBack();
                        $this->dispatch('notify', type: 'error', message: 'No hay suficiente stock en la bodega de destino para anular esta transferencia');
                        return;
                    }
                    
                    $itemStoreTo->stock_items_store -= $quantityInConsumptionUnit;
                    $itemStoreTo->save();
                }
            }

            // Mark transfer as annulled and update user (quien anula)
            $transfer->update([
                'status' => 'ANULADO',
                'userId' => auth()->id() // Usuario que anula
            ]);
            
            DB::connection('tenant')->commit();
            
            $this->dispatch('notify', type: 'success', message: 'Transferencia anulada correctamente y el inventario ha sido actualizado');
            $this->resetPage();
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error al anular transferencia', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', type: 'error', message: 'Error al anular la transferencia: ' . $e->getMessage());
        }
    }
    
    /**
     * Open send transfer modal
     */
    public function openSendModal($transferId)
    {
        try {
            $this->ensureTenantConnection();
            
            $transfer = InvTransfer::find($transferId);
            
            if (!$transfer) {
                $this->errorMessage = 'Transferencia no encontrada';
                return;
            }
            
            if ($transfer->status !== 'REGISTRADO') {
                $this->errorMessage = 'Solo se pueden enviar transferencias con estado REGISTRADO';
                return;
            }
            
            // Validar que el usuario esté en la store de origen
            $user = auth()->user();
            if (!$user || !$user->contact_id) {
                $this->errorMessage = 'Usuario no tiene un contacto asociado';
                return;
            }
            
            $contact = \App\Models\Central\VntContact::on('central')->find($user->contact_id);
            if (!$contact || !$contact->store) {
                $this->errorMessage = 'Usuario no tiene una store asignada';
                return;
            }
            
            // Verificar que la store del usuario sea la store de origen
            if ($contact->store != $transfer->storeFromId) {
                $this->errorMessage = 'Solo el usuario de la bodega de origen puede enviar esta transferencia';
                return;
            }
            
            $this->selectedTransferId = $transferId;
            $this->selectedTransferConsecutive = str_pad($transfer->consecutive, 6, '0', STR_PAD_LEFT);
            $this->packingBags = 0;
            $this->packingBaskets = 0;
            $this->packingBoxes = 0;
            $this->showSendModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error opening send modal', [
                'error' => $e->getMessage(),
                'transferId' => $transferId
            ]);
            $this->errorMessage = 'Error al abrir el modal: ' . $e->getMessage();
        }
    }
    
    /**
     * Close send transfer modal
     */
    public function closeSendModal()
    {
        $this->showSendModal = false;
        $this->selectedTransferId = null;
        $this->selectedTransferConsecutive = '';
        $this->packingBags = 0;
        $this->packingBaskets = 0;
        $this->packingBoxes = 0;
    }
    
    /**
     * Start transfer (change status to EN TRANSITO)
     */
    public function startTransfer()
    {
        try {
            // Validar que al menos un campo tenga valor mayor a 0
            if ($this->packingBags <= 0 && $this->packingBaskets <= 0 && $this->packingBoxes <= 0) {
                $this->errorMessage = 'Debe especificar al menos una cantidad de empaque (bolsas, canastas o cajas)';
                return;
            }
            
            $this->ensureTenantConnection();
            
            DB::connection('tenant')->beginTransaction();
            
            $transfer = InvTransfer::find($this->selectedTransferId);
            
            if (!$transfer) {
                $this->errorMessage = 'Transferencia no encontrada';
                return;
            }
            
            if ($transfer->status !== 'REGISTRADO') {
                DB::connection('tenant')->rollBack();
                $this->errorMessage = 'Solo se pueden enviar transferencias con estado REGISTRADO';
                return;
            }
            
            // Construir JSON con los datos de empaque
            $packingData = json_encode([
                'bolsas' => (string)$this->packingBags,
                'canastas' => (string)$this->packingBaskets,
                'cajas' => (string)$this->packingBoxes
            ]);
            
            // Update transfer status, packing and user (quien envía)
            $transfer->update([
                'status' => 'EN TRANSITO',
                'packing' => $packingData,
                'userId' => auth()->id() // Usuario que envía
            ]);
            
            DB::connection('tenant')->commit();
            
            $packingText = $this->formatPackingText();
            $this->successMessage = "La transferencia #{$this->selectedTransferConsecutive} ha cambiado de estado a EN TRÁNSITO ({$packingText})";
            $this->closeSendModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error starting transfer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transferId' => $this->selectedTransferId
            ]);
            $this->errorMessage = 'Error al iniciar la transferencia: ' . $e->getMessage();
        }
    }
    
    /**
     * Format packing text for display
     */
    private function formatPackingText(): string
    {
        $parts = [];
        
        if ($this->packingBags > 0) {
            $parts[] = $this->packingBags . ' ' . ($this->packingBags == 1 ? 'bolsa' : 'bolsas');
        }
        
        if ($this->packingBaskets > 0) {
            $parts[] = $this->packingBaskets . ' ' . ($this->packingBaskets == 1 ? 'canasta' : 'canastas');
        }
        
        if ($this->packingBoxes > 0) {
            $parts[] = $this->packingBoxes . ' ' . ($this->packingBoxes == 1 ? 'caja' : 'cajas');
        }
        
        return implode(', ', $parts);
    }

    public function render()
    {
        return view('livewire.tenant.transfers.components.transfer-list', [
            'transfers' => $this->transfers,
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
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }
}
