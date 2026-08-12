<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\QuarantineMovement;
use App\Models\Tenant\Items\ShowroomMovement;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ProductSpecialHistoryModal extends Component
{
    use WithPagination;

    public $isOpen = false;
    public $productId = null;
    public $productName = null;
    public $type = 'quarantine'; // 'quarantine' o 'showroom'
    
    // Filtros
    public $search = '';
    public $startDate = '';
    public $endDate = '';

    protected $listeners = ['openSpecialHistoryModal' => 'open'];

    public function boot()
    {
        if (!tenancy()->initialized) {
            $this->ensureTenantConnection();
        }
    }

    private function ensureTenantConnection()
    {
        $tenantManager = app(TenantManager::class);
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openSpecialHistoryModal')]
    public function open($productId, $type = 'quarantine')
    {
        $this->productId = $productId;
        $this->type = $type;
        $this->resetPage(); // Resetear paginación al abrir
        $this->reset(['search', 'startDate', 'endDate']);

        $this->ensureTenantConnection();
        $product = Items::find($productId);
        if ($product) {
            $this->productName = $product->name;
        }

        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->ensureTenantConnection();

        // Determinar el modelo según el tipo de historial
        $query = $this->type === 'quarantine' 
            ? QuarantineMovement::query() 
            : ShowroomMovement::query();

        // Filtrar por producto
        $query->where('item_id', $this->productId);

        // Relación con el usuario para mostrar quién hizo el movimiento
        $query->with('user');

        // Filtro de búsqueda (Justificación o nombre de usuario)
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('justification', 'like', $searchTerm)
                  ->orWhereHas('user', function($uq) use ($searchTerm) {
                      $uq->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                  });
            });
        }

        // Filtro de fecha inicio
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        // Filtro de fecha fin
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Ordenar por fecha descendente
        $query->orderBy('created_at', 'desc');

        // Paginado
        $movements = $query->paginate(8);

        return view('livewire.tenant.components.product-special-history-modal', [
            'movements' => $movements
        ]);
    }
}
