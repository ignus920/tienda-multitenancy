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

            // Mark transfer as annulled
            $transfer->update(['status' => 'ANULADO']);
            
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
