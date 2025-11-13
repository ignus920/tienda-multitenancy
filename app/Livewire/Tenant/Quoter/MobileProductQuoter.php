<?php

namespace App\Livewire\Tenant\Quoter;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\InvItem;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class MobileProductQuoter extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $quoterItems = [];
    public $totalAmount = 0;
    public $showCartModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->quoterItems = session('quoter_items', []);
        $this->calculateTotal();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        \Illuminate\Support\Facades\Log::info('🔍 MobileProductQuoter - Verificando conexión tenant', [
            'tenant_id_session' => $tenantId,
            'current_connection' => config('database.default'),
            'tenant_connection_config' => config('database.connections.tenant')
        ]);

        if (!$tenantId) {
            \Illuminate\Support\Facades\Log::warning('❌ No hay tenant_id en session, redirigiendo');
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            \Illuminate\Support\Facades\Log::warning('❌ Tenant no encontrado', ['tenant_id' => $tenantId]);
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        \Illuminate\Support\Facades\Log::info('✅ Tenant encontrado', [
            'tenant_id' => $tenant->id,
            'tenant_db_name' => $tenant->database_name
        ]);

        // Solo usar tenancy() para inicializar - esto debería configurar automáticamente la conexión
        tenancy()->initialize($tenant);

        // Verificar que la conexión esté configurada correctamente
        $currentDbName = \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName();
        \Illuminate\Support\Facades\Log::info('🔗 Conexión tenant configurada', [
            'database_name' => $currentDbName,
            'expected_db' => $tenant->database_name
        ]);

        if ($currentDbName !== $tenant->database_name) {
            \Illuminate\Support\Facades\Log::error('❌ Conexión tenant incorrecta!', [
                'current_db' => $currentDbName,
                'expected_db' => $tenant->database_name
            ]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        // Verificar conexión antes de consultar productos
        $currentDbName = \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName();
        \Illuminate\Support\Facades\Log::info('🛒 MobileProductQuoter - Consultando productos', [
            'current_db' => $currentDbName,
            'search_term' => $this->search,
            'session_tenant_id' => session('tenant_id')
        ]);

        $products = InvItem::query()
            ->active()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate(20);

        \Illuminate\Support\Facades\Log::info('📦 Productos encontrados', [
            'count' => $products->count(),
            'total' => $products->total(),
            'first_product' => $products->count() > 0 ? $products->first()->name : 'N/A'
        ]);

        return view('livewire.tenant.quoter.components.mobile-product-quoter', [
            'products' => $products
        ])->layout('layouts.app');
    }

    public function addToQuoter($productId)
    {
        $this->ensureTenantConnection();

        $product = InvItem::findOrFail($productId);
        $existingIndex = $this->findProductInQuoter($productId);

        if ($existingIndex !== false) {
            $this->quoterItems[$existingIndex]['quantity']++;
        } else {
            $this->quoterItems[] = [
                'id' => $product->id,
                'name' => $product->display_name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'description' => $product->description,
            ];
        }

        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Producto agregado'
        ]);
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromQuoter($index);
            return;
        }

        $this->quoterItems[$index]['quantity'] = $quantity;
        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();
    }

    public function removeFromQuoter($index)
    {
        unset($this->quoterItems[$index]);
        $this->quoterItems = array_values($this->quoterItems);
        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();
    }

    public function clearQuoter()
    {
        $this->quoterItems = [];
        session()->forget('quoter_items');
        $this->calculateTotal();
        $this->showCartModal = false;
    }

    public function saveQuote()
    {
        if (empty($this->quoterItems)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos en el carrito'
            ]);
            return;
        }

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Cotización guardada exitosamente'
        ]);

        $this->showCartModal = false;
    }

    public function toggleCartModal()
    {
        $this->showCartModal = !$this->showCartModal;
    }

    private function findProductInQuoter($productId)
    {
        foreach ($this->quoterItems as $index => $item) {
            if ($item['id'] == $productId) {
                return $index;
            }
        }
        return false;
    }

    private function calculateTotal()
    {
        $this->totalAmount = collect($this->quoterItems)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function getQuoterCountProperty()
    {
        return collect($this->quoterItems)->sum('quantity');
    }

    public function getProductQuantity($productId)
    {
        foreach ($this->quoterItems as $item) {
            if ($item['id'] == $productId) {
                return $item['quantity'];
            }
        }
        return 0;
    }
}