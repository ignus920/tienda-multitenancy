<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $storeId = 1; // Warehouse/Store ID configurable
    
    // Array para almacenar las cantidades seleccionadas por item
    public $selectedQuantities = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        $this->ensureTenantConnection();
        
        $query = Items::query()
            ->select([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                DB::raw('COALESCE(MAX(inv_unconfirmed_qty.qty), 0) AS quantity'),
                DB::raw('0 AS percentage'),
                DB::raw('SUM(CASE WHEN inv_inventory_adjustments.type = "entrada" THEN COALESCE(inv_detail_inv_adjustments.quantity, 0) ELSE 0 END) AS insideMovement'),
                DB::raw('SUM(CASE WHEN inv_inventory_adjustments.type = "salida" THEN COALESCE(inv_detail_inv_adjustments.quantity, 0) ELSE 0 END) AS outsideMovement'),
                'imp_items_setup.exw'
            ])
            ->join('inv_items_store', 'inv_items_store.itemId', '=', 'inv_items.id')
            ->join('imp_items_setup', 'imp_items_setup.item_id', '=', 'inv_items.id')
            ->join('inv_store', 'inv_store.id', '=', 'inv_items_store.storeId')
            ->leftJoin('inv_detail_inv_adjustments', 'inv_detail_inv_adjustments.itemId', '=', 'inv_items.id')
            ->leftJoin('inv_inventory_adjustments', 'inv_inventory_adjustments.id', '=', 'inv_detail_inv_adjustments.inventoryAdjustmentId')
            ->leftJoin('inv_unconfirmed_qty', 'inv_unconfirmed_qty.item_id', '=', 'inv_items.id')
            ->where('inv_items.status', 1)
            ->where('inv_store.id', $this->storeId)
            ->where('inv_items.type', 'IMPORTADO')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('inv_items.name', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.internal_code', 'like', '%' . $this->search . '%');
                });
            })
            ->groupBy([
                'inv_items.id',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items_store.stock_items_store',
                'imp_items_setup.exw'
            ])
            ->orderBy($this->sortField, $this->sortDirection);

        // Log del SQL generado
        Log::info('=== IMPORT ITEMS QUERY ===');
        Log::info('SQL: ' . $query->toSql());
        Log::info('Bindings: ' . json_encode($query->getBindings()));
        
        $results = $query->paginate($this->perPage);
        
        // Log de los resultados
        Log::info('Total items encontrados: ' . $results->total());
        Log::info('Items en página actual: ' . $results->count());
        
        if ($results->count() > 0) {
            Log::info('Primer item de ejemplo:');
            Log::info(json_encode($results->first()->toArray(), JSON_PRETTY_PRINT));
        }
        
        Log::info('=== FIN IMPORT ITEMS QUERY ===');
        
        return $results;
    }

    #[On('refresh-import-list')]
    public function refreshList()
    {
        $this->resetPage();
    }

    /**
     * Método que se ejecuta cuando cambia la cantidad seleccionada
     */
    public function updateQuantity($itemId, $quantity)
    {
        $this->selectedQuantities[$itemId] = $quantity;
        
        // Log para debug
        Log::info("Cantidad actualizada para item {$itemId}: {$quantity}");
        
        // Aquí puedes agregar la lógica que necesites cuando cambie la cantidad
        // Por ejemplo: actualizar base de datos, recalcular totales, etc.
        
        // Emitir evento si necesitas notificar a otros componentes
        $this->dispatch('quantity-updated', itemId: $itemId, quantity: $quantity);
        
        // Mostrar notificación al usuario (opcional)
        session()->flash('message', "Cantidad actualizada para item #{$itemId}: {$quantity}");
    }

    public function getLabels(){

        

    }

    public function render()
    {
        $items = $this->items;
        
        // Debug info para mostrar en la vista
        $debugInfo = [
            'total' => $items->total(),
            'per_page' => $items->perPage(),
            'current_page' => $items->currentPage(),
            'count' => $items->count(),
            'search' => $this->search,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'store_id' => $this->storeId,
        ];
        
        return view('livewire.tenant.imports.components.import-list', [
            'items' => $items,
            'debugInfo' => $debugInfo
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
        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}