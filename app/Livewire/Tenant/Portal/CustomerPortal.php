<?php

namespace App\Livewire\Tenant\Portal;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class CustomerPortal extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $categories = Category::orderBy('name')->get();

        $query = Items::query()
            ->select(
                'inv_items.*',
                DB::raw('SUM(inv_items_store.stock_items_store) as total_stock'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL) as reserved_stock')
            )
            ->where('inv_items.status', 1)
            ->where('inv_items.type', '!=', 'INSUMO')
            ->with(['principalImage', 'invValues', 'tax'])
            ->leftJoin('inv_items_store', 'inv_items.id', '=', 'inv_items_store.itemId')
            ->groupBy(
                'inv_items.id',
                'inv_items.api_data_id',
                'inv_items.categoryId',
                'inv_items.name',
                'inv_items.internal_code',
                'inv_items.sku',
                'inv_items.description',
                'inv_items.type',
                'inv_items.taxId',
                'inv_items.commandId',
                'inv_items.brandId',
                'inv_items.houseId',
                'inv_items.inventoriable',
                'inv_items.purchasing_unit',
                'inv_items.consumption_unit',
                'inv_items.handles_serial',
                'inv_items.status',
                'inv_items.generic',
                'inv_items.created_at',
                'inv_items.updated_at',
                'inv_items.deleted_at'
            );

        if ($this->search) {
            $words = array_filter(explode(' ', trim($this->search)));
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('inv_items.name', 'like', '%' . $word . '%')
                      ->orWhere('inv_items.internal_code', 'like', '%' . $word . '%')
                      ->orWhere('inv_items.description', 'like', '%' . $word . '%');
                });
            }
        }

        if ($this->selectedCategory) {
            $query->where('inv_items.categoryId', $this->selectedCategory);
        }

        $products = $query->paginate($this->perPage);

        return view('livewire.tenant.portal.customer-portal', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
