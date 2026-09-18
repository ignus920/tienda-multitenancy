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
    use \Livewire\WithFileUploads;

    public $search = '';
    public $selectedCategory = '';
    public $perPage = 10;
    public $stockFilter = 'all';
    public $paymentFilter = '';

    public $branches = [];
    public $selectedBranchId = null;
    public $shippingAddress = '';
    public $showWarehouseModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'paymentFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingStockFilter()
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

        $user = auth()->user();
        if ($user && $user->tenant_company_id) {
            $this->branches = \App\Models\Tenant\Customer\VntWarehouse::where('companyId', $user->tenant_company_id)
                ->where('status', 1)
                ->get()
                ->toArray();
            
            // Establecer por defecto la sucursal principal
            $mainWarehouse = \App\Models\Tenant\Customer\VntWarehouse::where('companyId', $user->tenant_company_id)
                ->where('main', 1)
                ->first();
            if ($mainWarehouse) {
                $this->selectedBranchId = $mainWarehouse->id;
                $this->shippingAddress = $mainWarehouse->address;
            }
        }
    }

    #[\Livewire\Attributes\On('warehouse-selected')]
    public function onWarehouseSelected($branchId)
    {
        $this->ensureTenantConnection();
        $this->selectedBranchId = $branchId;
        $branch = \App\Models\Tenant\Customer\VntWarehouse::find($branchId);
        if ($branch) {
            $this->shippingAddress = $branch->address;
            
            // Recargar sucursales en caso de adición o edición
            $user = auth()->user();
            if ($user && $user->tenant_company_id) {
                $this->branches = \App\Models\Tenant\Customer\VntWarehouse::where('companyId', $user->tenant_company_id)
                    ->where('status', 1)
                    ->get()
                    ->toArray();
            }
        }
        $this->showWarehouseModal = false;
    }

    #[\Livewire\Attributes\On('warehouse-modal-closed')]
    public function closeWarehouseModal()
    {
        $this->showWarehouseModal = false;
        
        // Recargar sucursales para reflejar cambios
        $user = auth()->user();
        if ($user && $user->tenant_company_id) {
            $this->ensureTenantConnection();
            $this->branches = \App\Models\Tenant\Customer\VntWarehouse::where('companyId', $user->tenant_company_id)
                ->where('status', 1)
                ->get()
                ->toArray();
        }
    }

    public function changeBranch()
    {
        $this->ensureTenantConnection();
        $branch = \App\Models\Tenant\Customer\VntWarehouse::find($this->selectedBranchId);
        if ($branch) {
            $this->shippingAddress = $branch->address;
        }
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

        // Obtener listas de precios configuradas para el cliente
        $user = auth()->user();
        $cashPricelist = null;
        $creditPricelist = null;
        $settingsConfigured = false;
        $companyName = '';

        if ($user && $user->tenant_company_id) {
            $company = \App\Models\Tenant\Customer\VntCompany::find($user->tenant_company_id);
            if ($company) {
                $companyName = $company->businessName ?: ($company->firstName . ' ' . $company->lastName);
                $settings = $company->portalSettings;
                if ($settings && ($settings->cash_pricelist_id || $settings->credit_pricelist_id)) {
                    $settingsConfigured = true;
                    if ($settings->cash_pricelist_id) {
                        $cashPricelist = \App\Models\Tenant\Parameters\PriceList::find($settings->cash_pricelist_id);
                    }
                    if ($settings->credit_pricelist_id) {
                        $creditPricelist = \App\Models\Tenant\Parameters\PriceList::find($settings->credit_pricelist_id);
                    }
                }
            }
        }

        $query = Items::query()
            ->select(
                'inv_items.*',
                DB::raw('SUM(inv_items_store.stock_items_store) as total_stock'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)) as reserved_stock'),
                // % y cantidad mínima del Portal B2B: se configuran por producto en la
                // bodega principal (storeId=2), igual patrón que wp_stock_percentage.
                DB::raw('(SELECT b2b_stock_percentage FROM inv_items_store s2 WHERE s2.itemId = inv_items.id AND s2.storeId = 2 ORDER BY s2.id DESC LIMIT 1) as b2b_stock_percentage'),
                DB::raw('(SELECT b2b_min_stock FROM inv_items_store s2 WHERE s2.itemId = inv_items.id AND s2.storeId = 2 ORDER BY s2.id DESC LIMIT 1) as b2b_min_stock')
            )
            ->where('inv_items.status', 1)
            ->where('inv_items.type', '!=', 'INSUMO')
            ->with(['principalImage', 'invValues', 'tax', 'dimensions', 'suggestedProducts.suggestedItem.principalImage'])
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
                'inv_items.is_cuttable',
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

        if ($this->stockFilter === 'in_stock') {
            // Misma fórmula que % Stock WordPress / Can Mínima WordPress, pero con
            // b2b_stock_percentage / b2b_min_stock (storeId=2): si el stock neto cae
            // por debajo del mínimo configurado, cuenta como agotado (0); si no, se
            // muestra el % configurado del stock real. Sin configurar, usa 30% / 0
            // (aprox. el comportamiento fijo que tenía el portal antes de esto).
            $query->havingRaw("
                CASE
                    WHEN (COALESCE(SUM(inv_items_store.stock_items_store), 0) - (SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)))
                         >= COALESCE((SELECT b2b_min_stock FROM inv_items_store s2 WHERE s2.itemId = inv_items.id AND s2.storeId = 2 ORDER BY s2.id DESC LIMIT 1), 0)
                    THEN ROUND((COALESCE(SUM(inv_items_store.stock_items_store), 0) - (SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY))) * COALESCE((SELECT b2b_stock_percentage FROM inv_items_store s2 WHERE s2.itemId = inv_items.id AND s2.storeId = 2 ORDER BY s2.id DESC LIMIT 1), 30) / 100)
                    ELSE 0
                END > 0
            ");
        }

        $products = $query->paginate($this->perPage);

        $sliders = \App\Models\Tenant\Marketing\PromotionalSlider::where('status', 1)
            ->orderBy('order', 'asc')
            ->get();



        return view('livewire.tenant.portal.customer-portal', [
            'products' => $products,
            'categories' => $categories,
            'cashPricelist' => $cashPricelist,
            'creditPricelist' => $creditPricelist,
            'settingsConfigured' => $settingsConfigured,
            'companyName' => $companyName,
            'sliders' => $sliders,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }

    public $proofPaymentFile;

    public function submitOrder($cartItems)
    {
        $this->ensureTenantConnection();

        if (empty($cartItems)) {
            $this->dispatch('swal', [
                'title' => 'Carrito Vacío',
                'text' => 'El carrito de compras está vacío.',
                'icon' => 'warning'
            ]);
            return false;
        }

        if (!$this->shippingAddress) {
            $this->dispatch('swal', [
                'title' => 'Dirección Requerida',
                'text' => 'Por favor, ingresa o selecciona una dirección de envío.',
                'icon' => 'warning'
            ]);
            return false;
        }

        // Fase piloto: el comprobante de pago queda opcional (antes era
        // 'required'). Para reactivarlo cuando termine el piloto, volver
        // esta regla a 'required' y destapar el bloque del blade en
        // customer-portal.blade.php (buscar "Comprobante de pago:").
        try {
            $this->validate([
                'proofPaymentFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ], [
                'proofPaymentFile.mimes' => 'El comprobante debe ser un archivo de tipo: pdf, jpg, jpeg, png',
                'proofPaymentFile.max' => 'El comprobante no debe pesar más de 5MB',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('swal', [
                'title' => 'Archivo Inválido',
                'text' => 'El comprobante debe ser un archivo PDF, JPG o PNG de hasta 5MB.',
                'icon' => 'error'
            ]);
            return false;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            $user = auth()->user();
            $companyId = $user->tenant_company_id;

            // Encontrar un contacto local en la BD tenant asociado a la compañía
            $contact = \App\Models\Tenant\Customer\VntContacts::whereHas('warehouse', function($w) use ($companyId) {
                $w->where('companyId', $companyId);
            })->first();

            if (!$contact) {
                throw new \Exception('No se encontró un contacto registrado para tu empresa en el sistema.');
            }

            // 1. Obtener consecutivo y crear Cotización (VntQuote)
            $lastQuote = \App\Models\Tenant\Quoter\VntQuote::lockForUpdate()->orderBy('consecutive', 'desc')->first();
            $nextQuoteConsecutive = $lastQuote ? $lastQuote->consecutive + 1 : 1;

            // Bodega física a la que queda asociada la cotización. Debe ser la
            // MISMA bodega ("PRINCIPAL", storeId=2) que ya usa el resto del
            // Portal (% Stock Portal B2B) y donde está el equipo comercial —
            // el panel de Cotizaciones (Quoter.php) solo le muestra a cada
            // vendedor las cotizaciones de SU bodega asignada, así que si
            // aquí se pone una bodega distinta, la cotización del cliente
            // queda invisible para ellos aunque exista en la base de datos.
            $physicalStore = \App\Models\Tenant\Items\InvStore::find(2)
                ?? \App\Models\Tenant\Items\InvStore::where('status', 1)->first();
            $physicalStoreId = $physicalStore ? $physicalStore->id : 1;

            // Sucursal de entrega del cliente B2B (dirección de despacho)
            $customerBranchId = $this->selectedBranchId ?: $contact->warehouseId;

            // ¿Alguna cantidad pedida supera lo que el Portal le mostró como
            // disponible? Se recalcula aquí (no se confía en lo que mandó el
            // navegador) para decidir el mensaje y dejarle la alerta al
            // comercial en las observaciones de la cotización.
            $exceedsAvailable = false;
            foreach ($cartItems as $item) {
                if ((int) $item['qty'] > $this->computeVisibleStock((int) $item['id'])) {
                    $exceedsAvailable = true;
                    break;
                }
            }

            $observations = 'Pedido B2B recibido desde el Portal de Clientes';
            if ($exceedsAvailable) {
                $observations .= "\n⚠️ El cliente pidió cantidades por encima de lo ofrecido — requiere confirmar disponibilidad antes de convertir a OP.";
            }

            $quote = \App\Models\Tenant\Quoter\VntQuote::create([
                'consecutive' => $nextQuoteConsecutive,
                'status' => 'REGISTRADO',
                'typeQuote' => 'POS',
                'from_portal' => true,
                'customerId' => $contact->warehouseId,
                'warehouseId' => $physicalStoreId, // Asignar la bodega física del ERP
                'userId' => $user->id,
                'observations' => $observations,
                'branchId' => $customerBranchId, // Sucursal de entrega del cliente
                'flete' => 0
            ]);

            // Crear detalles de la cotización
            foreach ($cartItems as $item) {
                $itemModel = \App\Models\Tenant\Items\Items::find($item['id']);
                $taxPercentage = $itemModel && $itemModel->taxRelation ? $itemModel->taxRelation->value : 0;

                \App\Models\Tenant\Quoter\VntDetailQuote::create([
                    'quantity' => $item['qty'],
                    'tax' => $taxPercentage,
                    'value' => $item['price'],
                    'quoteId' => $quote->id,
                    'itemId' => $item['id'],
                    'description' => $item['name'],
                    'priceList' => $item['price'],
                    'price_label' => $item['label'] ?? 'Precio',
                ]);
            }

            // NOTA (fase piloto): antes, aquí mismo se creaba de una vez la
            // InvRemissions (la OP real), se descontaba inventario y se
            // autoaprobaban las autorizaciones de cartera — el pedido del
            // cliente quedaba "aprobado" sin que nadie del equipo comercial
            // lo revisara. Eso ya NO se hace: el pedido del cliente se queda
            // como cotización (arriba) hasta que un asesor comercial la
            // revise desde el panel de Cotizaciones y decida convertirla en
            // OP manualmente (mismo flujo que ya usan con cualquier otra
            // cotización, en ProductQuoter::confirmOrder()).

            DB::connection('tenant')->commit();

            // Resetear estados del backend
            $this->reset('proofPaymentFile');

            $this->dispatch('swal', $exceedsAvailable ? [
                'title' => 'Solicitud de Confirmación Enviada',
                'text' => "Tu cotización #{$nextQuoteConsecutive} tiene cantidades por encima de lo disponible. Nuestro equipo comercial confirmará qué cantidades sí se pueden entregar.",
                'icon' => 'warning'
            ] : [
                'title' => '¡Pedido Confirmado!',
                'text' => "Tu cotización #{$nextQuoteConsecutive} fue enviada dentro de las cantidades disponibles. Nuestro equipo comercial la confirmará en breve.",
                'icon' => 'success'
            ]);
            return true;
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error registrando pedido desde portal', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            $this->dispatch('swal', [
                'title' => 'Error al procesar',
                'text' => $e->getMessage(),
                'icon' => 'error'
            ]);
            return false;
        }
    }

    /**
     * Misma fórmula que usa el catálogo del Portal (% Stock Portal B2B +
     * Cant Mínima Portal B2B, bodega principal storeId=2) para saber cuánta
     * cantidad de un ítem se le puede mostrar/ofrecer al cliente. Se vuelve
     * a calcular aquí en submitOrder() en vez de confiar en lo que mandó el
     * navegador.
     */
    private function computeVisibleStock(int $itemId): int
    {
        $totalStock = (float) DB::connection('tenant')->table('inv_items_store')
            ->where('itemId', $itemId)
            ->sum('stock_items_store');

        $reservedStock = (float) DB::connection('tenant')->table('inv_reservations')
            ->where('item_id', $itemId)
            ->where('status_id', 1)
            ->where('stock_type', 1)
            ->whereNull('deleted_at')
            ->where('due_date', '>=', now()->subDays(15))
            ->sum('quantity');

        $realStock = $totalStock - $reservedStock;

        $storeConfig = DB::connection('tenant')->table('inv_items_store')
            ->where('itemId', $itemId)
            ->where('storeId', 2)
            ->orderByDesc('id')
            ->first();

        $percentage = $storeConfig->b2b_stock_percentage ?? 30;
        $minStock = $storeConfig->b2b_min_stock ?? 0;

        if ($realStock < $minStock) {
            return 0;
        }

        return max(0, (int) round($realStock * ($percentage / 100)));
    }
}
