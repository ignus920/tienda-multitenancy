<?php

namespace App\Livewire\Tenant\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Inventory\InventoryConfirmation;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;

class InventoryConfirmationList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 10;
    
    // Filtros
    public $filterDateFrom = '';
    public $filterDateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->ensureTenantConnection();
        // Inicializar fechas por defecto (±7 días)
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status']);
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');
        $this->resetPage();
    }

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function hydrate()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        // Si ya hay una base de datos seleccionada en la conexión tenant, no hacemos nada
        if (config('database.connections.tenant.database') && tenancy()->initialized) {
            return;
        }

        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirm($id, $quantity, $observations = '')
    {
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->beginTransaction();

            $confirmation = InventoryConfirmation::findOrFail($id);
            $confirmation->update([
                'confirmed_quantity' => $quantity,
                'confirmation_observations' => $observations,
                'confirmer_id' => auth()->id(),
                'confirmed_at' => now(),
                'status' => 2 // Confirmado
            ]);

            DB::connection('tenant')->commit();
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Inventario confirmado correctamente'
            ]);

            $this->dispatch('confirmation-updated');
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al confirmar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $query = InventoryConfirmation::with(['item', 'requester', 'confirmer'])
            ->when($this->search, function($q) {
                $q->whereHas('item', function($itemQuery) {
                    $itemQuery->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('internal_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function($q) {
                $q->where('status', $this->status);
            })
            ->when($this->filterDateFrom, function($q) {
                $q->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function($q) {
                $q->whereDate('created_at', '<=', $this->filterDateTo);
            })
            ->orderBy('created_at', 'desc');

        return view('livewire.tenant.inventory.inventory-confirmation-list', [
            'confirmations' => $query->paginate($this->perPage)
        ])->layout('layouts.app');
    }
}
