<?php

namespace App\Livewire\Tenant\Transfers\Components;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Tenant\Transfers\InvTransferRequest;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

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
    
    // Messages
    public string $errorMessage = '';

    /**
     * Reset pagination when search is updated
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
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
            
            $request = InvTransferRequest::with('warehouse')->find($requestId);
            
            if (!$request) {
                $this->errorMessage = 'Solicitud de transferencia no encontrada';
                return;
            }
            
            $this->requestDetails = [
                'id' => $request->id,
                'type' => $request->type,
                'date' => $request->formatted_date,
                'warehouse' => $request->warehouse->name ?? 'N/A',
                'quoteId' => $request->quoteId ?? '-',
                'observations' => $request->observations ?? 'N/A',
                'created_at' => $request->created_at->format('d/m/Y H:i'),
                'updated_at' => $request->updated_at->format('d/m/Y H:i'),
                'status_badge_class' => $request->status_badge_class,
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
        $this->errorMessage = '';
    }

    /**
     * Get paginated transfer requests with search and sorting
     */
    #[Computed]
    public function transferRequests()
    {
        $this->ensureTenantConnection();
        
        return InvTransferRequest::query()
            ->with('warehouse')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('date', 'like', "%{$this->search}%")
                        ->orWhere('observations', 'like', "%{$this->search}%")
                        ->orWhereHas('warehouse', function ($wq) {
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
