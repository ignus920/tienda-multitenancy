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
use App\Traits\Livewire\WithExport;
use Illuminate\Support\Facades\Log;

class ProductSpecialHistoryModal extends Component
{
    use WithPagination, WithExport;

    public $isOpen = false;
    public $productId = null;
    public $productName = null;
    public $productCode = null;
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
        $this->reset(['search']);

        // Establecer filtro de 30 días por defecto
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->ensureTenantConnection();
        $product = Items::find($productId);
        if ($product) {
            $this->productName = $product->name;
            $this->productCode = $product->internal_code ?? $product->sku;
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

    /**
     * Métodos requeridos por el trait WithExport
     */

    protected function getExportFilename(): string
    {
        $typeName = $this->type === 'quarantine' ? 'Cuarentena' : 'Vitrina';
        $code = $this->productCode ?? 'Producto';
        return "Historial_{$typeName}_{$code}_" . now()->format('Ymd');
    }

    protected function getExportHeadings(): array
    {
        return [
            'Fecha / Hora',
            'Tipo de Movimiento',
            'Cantidad',
            'Justificación',
            'Usuario Registra',
            'Email Usuario'
        ];
    }

    protected function getExportData()
    {
        $this->ensureTenantConnection();

        $query = $this->type === 'quarantine' 
            ? QuarantineMovement::query() 
            : ShowroomMovement::query();

        $query->where('item_id', $this->productId);
        $query->with('user');

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

        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getExportMapping($movement): array
    {
        return [
            \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y h:i A'),
            $movement->quantity > 0 ? 'Ingreso' : 'Liberación',
            $movement->quantity,
            $movement->justification,
            $movement->user?->name ?? 'Usuario Sistema',
            $movement->user?->email ?? ''
        ];
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
