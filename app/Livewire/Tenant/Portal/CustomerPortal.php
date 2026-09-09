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
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)) as reserved_stock')
            )
            ->where('inv_items.status', 1)
            ->where('inv_items.type', '!=', 'INSUMO')
            ->with(['principalImage', 'invValues', 'tax', 'dimensions'])
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

        if ($this->stockFilter === 'in_stock') {
            $query->havingRaw("
                CASE 
                    WHEN (COALESCE(SUM(inv_items_store.stock_items_store), 0) - (SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY))) > 100 THEN 30
                    ELSE ROUND((COALESCE(SUM(inv_items_store.stock_items_store), 0) - (SELECT COALESCE(SUM(quantity), 0) FROM inv_reservations WHERE item_id = inv_items.id AND status_id = 1 AND stock_type = 1 AND deleted_at IS NULL AND due_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY))) * 0.30)
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

        if (!$this->proofPaymentFile) {
            $this->dispatch('swal', [
                'title' => 'Comprobante Requerido',
                'text' => 'El comprobante de pago es obligatorio para procesar el pedido.',
                'icon' => 'warning'
            ]);
            return false;
        }

        try {
            $this->validate([
                'proofPaymentFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ], [
                'proofPaymentFile.required' => 'El comprobante de pago es obligatorio',
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

            // Obtener la primera bodega física activa del inquilino (tenant) para el descuento de inventario
            $physicalStore = \App\Models\Tenant\Items\InvStore::where('status', 1)->first();
            $physicalStoreId = $physicalStore ? $physicalStore->id : 1;
            $tenantBranchId = $physicalStore ? $physicalStore->warehouseId : 1;

            // Sucursal de entrega del cliente B2B (dirección de despacho)
            $customerBranchId = $this->selectedBranchId ?: $contact->warehouseId;

            $quote = \App\Models\Tenant\Quoter\VntQuote::create([
                'consecutive' => $nextQuoteConsecutive,
                'status' => 'REGISTRADO',
                'typeQuote' => 'POS',
                'customerId' => $contact->warehouseId,
                'warehouseId' => $physicalStoreId, // Asignar la bodega física del ERP
                'userId' => $user->id,
                'observations' => 'Pedido B2B recibido desde el Portal de Clientes',
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

            // 2. Almacenar el archivo de comprobante de pago
            $tenantId = session('tenant_id', 'default');
            $proofPaymentPath = $this->proofPaymentFile->store("remissions/proofs/{$tenantId}", 'public');

            // 3. Obtener consecutivo y crear Remisión (InvRemissions)
            $lastRemission = \App\Models\Tenant\Remissions\InvRemissions::lockForUpdate()->orderBy('consecutive', 'desc')->first();
            $nextRemissionConsecutive = $lastRemission ? $lastRemission->consecutive + 1 : 1;

            // Obtener el primer método de pago (transferencia, etc. por defecto)
            $methodPayment = \App\Models\Tenant\MethodPayments\VntMethodPayMents::first();
            $methodPaymentId = $methodPayment ? $methodPayment->id : null;

            $paymentsArray = [[
                'method_payment_id' => $methodPaymentId,
                'value' => $quote->total,
                'proof_payment' => $proofPaymentPath,
                'observation' => 'Comprobante cargado por el cliente desde el portal'
            ]];

            $remission = \App\Models\Tenant\Remissions\InvRemissions::create([
                'consecutive' => $nextRemissionConsecutive,
                'status' => 'REGISTRADO',
                'quoteId' => $quote->id,
                'warehouseId' => $physicalStoreId, // Asignar correctamente el ID de la bodega física (inv_store.id)
                'deliveryTypeId' => 1, // Por defecto Contra entrega/estándar
                'methodPaymentId' => $methodPaymentId,
                'userId' => $quote->userId,
                'created_by' => $user->id,
                'deliveryDate' => now()->format('Y-m-d'),
                'expiration' => 0,
                'modify' => 0,
                'obs' => 'Pedido B2B registrado desde el Portal de Clientes',
                'observations_delivery' => $this->shippingAddress,
                'flete' => 0,
                'proof_payment' => $proofPaymentPath,
                'payment_details' => $paymentsArray,
                'from_portal' => true,
            ]);

            // 4. Crear detalles de remisión y descontar inventario
            foreach ($cartItems as $item) {
                $itemModel = \App\Models\Tenant\Items\Items::find($item['id']);
                $taxPercentage = $itemModel && $itemModel->taxRelation ? $itemModel->taxRelation->value : 0;
                $taxLabel = $itemModel && $itemModel->taxRelation ? $itemModel->taxRelation->name : 'N/A';

                \App\Models\Tenant\Remissions\InvDetailRemissions::create([
                    'quantity' => $item['qty'],
                    'tax' => $taxPercentage,
                    'tax_label' => $taxLabel,
                    'value' => $item['price'],
                    'remissionId' => $remission->id,
                    'itemId' => $item['id'],
                    'description' => $item['name']
                ]);

                // Descontar inventario de la bodega física activa del inquilino
                $itemStore = \App\Models\Tenant\Items\InvItemsStore::where('itemId', $item['id'])
                    ->where('storeId', $physicalStoreId)
                    ->first();

                $productModel = \App\Models\Tenant\Items\Items::find($item['id']);
                $isAssembled = $productModel && $productModel->type === 'ENSAMBLADO';

                if ($itemStore) {
                    $newStock = $itemStore->stock_items_store - $item['qty'];
                    if ($newStock < 0 && !$isAssembled) {
                        throw new \Exception("Stock insuficiente para el producto '{$item['name']}'. Disponible: {$itemStore->stock_items_store}");
                    }
                    $itemStore->update(['stock_items_store' => $newStock]);
                } else {
                    if (!$isAssembled) {
                        throw new \Exception("El producto '{$item['name']}' no cuenta con inventario registrado en esta sucursal.");
                    }
                }
            }

            // 5. Crear autorizaciones de cartera automáticas (Chuliado automático)
            $authTypes = ['empaque', 'despacho', 'pago'];
            foreach ($authTypes as $authType) {
                \App\Models\Tenant\Sales\VntOrderAuthorization::create([
                    'remission_id' => $remission->id,
                    'auth_type' => $authType,
                    'status' => 1,
                    'user_id' => auth()->id() // Asignar el ID de usuario autenticado
                ]);
            }

            DB::connection('tenant')->commit();

            // Resetear estados del backend
            $this->reset('proofPaymentFile');
            
            $this->dispatch('swal', [
                'title' => '¡Pedido Enviado!',
                'text' => "El pedido #{$nextRemissionConsecutive} ha sido registrado con éxito y enviado para verificación.",
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
}
