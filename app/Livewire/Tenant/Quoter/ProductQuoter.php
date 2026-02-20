<?php

namespace App\Livewire\Tenant\Quoter;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Customer\VntContacts;
use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Tenant\Quoter\VntDetailQuote;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Remissions\InvDetailRemissions;
use App\Models\Tenant\Remissions\InvDeliveryType;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Models\Tenant\Invoices\VntInvoicePayments;
use App\Models\Tenant\Items\Category;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Central\VntContact;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Services\Facturacion\InvoiceDataBuilder;
use App\Services\Tenant\RetentionCalculatorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductQuoter extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 12;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selectedProducts = [];
    public $quoterItems = [];
    public $totalAmount = 0;
    public $showCartModal = false;

    // Propiedades para desglose de impuestos
    public $subTotal = 0; // Valor sin impuestos
    public $taxBreakdown = [
        'iva_5' => 0,    // IVA 5%
        'iva_19' => 0,   // IVA 19%
        'exento' => 0,   // Sin impuestos
    ];
    public $totalTaxes = 0; // Total de todos los impuestos
    public $viewType = 'desktop'; // 'desktop' o 'mobile'
    public $customerSearch = '';
    public $selectedCustomer = null;
    public $observaciones = null;
    public $searchingCustomer = false;
    public $showCreateCustomerForm = false;
    public $showCreateCustomerButton = false;
    public $editingCustomerId = null;
    public $editingQuoteId = null;
    public $editingRemissionId = null;
    public $isEditing = false;
    public $isEditingRemission = false;
    public $hasChanges = false;

    // Propiedades para modal de pagos
    public $showPaymentModal = false;
    public $paymentMethods = [
        'efectivo' => ['name' => 'EFECTIVO', 'value' => 0, 'selected' => false],
        'nequi' => ['name' => 'NEQUI', 'value' => 0, 'selected' => false],
        'daviplata' => ['name' => 'DAVIPLATA', 'value' => 0, 'selected' => false],
        'tarjeta' => ['name' => 'TARJETA', 'value' => 0, 'selected' => false],
    ];
    public $currentPaymentMethod = 'efectivo';
    public $totalPaid = 0;
    public $remainingBalance = 0;
    public $changeAmount = 0;
    public $canProceedToPayment = false;
    public $cashAutoAdjusted = false; // Flag para controlar ajuste automático
    public $showObservations = false;
    // Nueva propiedad para la categoría seleccionada
    public $selectedCategory = '';
    public $customerResults = []; // Resultados de búsqueda de clientes

    // Propiedades para retenciones
    public $retentions = [
        'retention_fuente' => 0,
        'retention_ica' => 0,
        'retention_iva' => 0,
    ];
    public $showRetentions = false; // Mostrar sección de retenciones solo si hay valores > 0
    public $totalWithRetentions = 0; // Total después de aplicar retenciones

    // Propiedades para selección de tipo de entrega
    public $deliveryTypes = [];
    public $selectedDeliveryType = null;
    public $deliveryDetails = '';
    public $showDeliveryModal = false;
    public $requiresDeliveryDetails = false;

    // Propiedades para métodos de pago en remisión
    public $methodPayments = [];
    public $selectedMethodPayment = null;
    public $showOtherDeliveryInput = false;
    public $otherDeliveryDetails = '';

    // Propiedad para controlar el modo de vista de productos
    public $viewMode = 'grid'; // 'grid' (actual) o 'table' (nuevo)

    protected $listeners = [
        'customer-created' => 'onCustomerCreated',
        'vnt-company-saved' => 'onCustomerCreated',
        'customer-updated' => 'onCustomerUpdated',
        'customer-form-cancelled' => 'cancelCreateCustomer'
    ];

    public function updatedObservaciones()
    {
        // Marcar que hay cambios si estamos editando
        if ($this->isEditing) {
            $this->hasChanges = true;
        }
    }

    /**
     * Se ejecuta automáticamente cuando cambia cualquier valor en paymentMethods
     * Aplica validaciones y recalcula automáticamente los balances y el cambio
     */
    public function updatedPaymentMethods()
    {
        // Calcular total pagado por métodos que no son efectivo
        $totalNonCashPayments = 0;
        foreach ($this->paymentMethods as $key => $method) {
            if ($key !== 'efectivo') {
                $methodValue = (float) ($method['value'] ?? 0);

                // Calcular límite para este método (excluyendo efectivo)
                $otherNonCashTotal = 0;
                foreach ($this->paymentMethods as $otherKey => $otherMethod) {
                    if ($otherKey !== 'efectivo' && $otherKey !== $key) {
                        $otherNonCashTotal += (float) ($otherMethod['value'] ?? 0);
                    }
                }

                $maxAllowedForMethod = $this->totalAmount - $otherNonCashTotal;

                // Aplicar límite a métodos no-efectivo
                if ($methodValue > $maxAllowedForMethod) {
                    $this->paymentMethods[$key]['value'] = max(0, $maxAllowedForMethod);
                    $methodValue = $this->paymentMethods[$key]['value'];
                }

                $totalNonCashPayments += $methodValue;
            }
        }

        // NUEVA LÓGICA: Ajustar efectivo automáticamente
        $currentCashValue = (float) ($this->paymentMethods['efectivo']['value'] ?? 0);

        // Solo ajustar automáticamente si no se está editando efectivo directamente
        if ($this->currentPaymentMethod !== 'efectivo') {
            // CASO 1: Si hay otros métodos y efectivo tiene cualquier valor, ajustar
            if ($totalNonCashPayments > 0 && $currentCashValue > 0) {
                $remainingForCash = max(0, $this->totalAmount - $totalNonCashPayments);
                $this->paymentMethods['efectivo']['value'] = $remainingForCash;
                $this->cashAutoAdjusted = true;
            }
        } else {
            // Si se está editando efectivo, desactivar el ajuste automático
            $this->cashAutoAdjusted = false;
        }

        // Si se quitaron todos los otros métodos y efectivo es 0, restaurar efectivo al total
        if ($totalNonCashPayments == 0 && $currentCashValue == 0) {
            // Solo restaurar si antes había algún pago configurado
            $hadPreviousPayments = false;
            foreach ($this->paymentMethods as $method) {
                if ((float) ($method['value'] ?? 0) > 0) {
                    $hadPreviousPayments = true;
                    break;
                }
            }

            // No restaurar automáticamente - dejar que el usuario decida
        }

        $this->calculatePaymentBalances();
    }

    /**
     * Método específico que se ejecuta cuando se modifica efectivo
     * Desactiva el ajuste automático
     */
    public function updatedPaymentMethodsEfectivo()
    {
        // Resetear el flag cuando el usuario modifica efectivo manualmente
        $this->cashAutoAdjusted = false;
        $this->calculatePaymentBalances();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 12],
    ];

    public function boot()
    {
        // Establecer conexión tenant lo más pronto posible (antes de la hidratación de modelos)
        $this->ensureTenantConnection();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        Log::info('🔄 Reseteando página por cambio en búsqueda', ['search' => $this->search]);
    }

    public function updatingPerPage()
    {
        $this->resetPage();
        Log::info('🔄 Reseteando página por cambio en perPage', ['perPage' => $this->perPage]);
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
        Log::info('🔄 Reseteando página por cambio en categoría', ['category' => $this->selectedCategory]);
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

    public function mount($quoteId = null, $remissionId = null)
    {
        // Obtener viewType de la ruta o usar desktop por defecto
        $this->viewType = request()->route('viewType', 'desktop');
        $this->ensureTenantConnection();

        // Resetear página si viene de otra vista
        $this->resetPage();

        // Intentar obtener remissionId de los parámetros de la ruta si no se pasó directamente
        if (!$remissionId) {
            $remissionId = request()->route('remissionId');
        }

        // Si se pasa un quoteId, estamos editando una cotización
        if ($quoteId) {
            $this->loadQuoteForEditing($quoteId);
        } elseif ($remissionId) {
            // Si se pasa un remissionId, estamos editando una remisión
            $this->loadRemissionForEditing($remissionId);
        } else {
            $this->quoterItems = session('quoter_items', []);
        }

        $this->calculateTotal();
        $this->loadDeliveryTypes();
        $this->loadMethodPayments();

        Log::info('🚀 ProductQuoter montado', [
            'viewType' => $this->viewType,
            'quoteId' => $quoteId,
            'remissionId' => $remissionId,
            'quoterItems_count' => count($this->quoterItems)
        ]);

        // NUEVO: Detectar si viene del sistema de pagos para procesar factura
        if (session('process_invoice_after_payment') && $quoteId) {
            session()->forget('process_invoice_after_payment');

            // Obtener datos de pago desde la sesión
            $paymentData = session('payment_data_for_quote_' . $quoteId, []);
            session()->forget('payment_data_for_quote_' . $quoteId);

            Log::info('💳 Detectado retorno desde sistema de pagos, procesando factura', [
                'quote_id' => $quoteId,
                'payment_data_received' => !empty($paymentData)
            ]);

            // Procesar la factura automáticamente con los datos de pago
            if (!empty($paymentData)) {
                $this->processInvoiceAfterPayment($paymentData);
            }
        }
    }

    /**
     * Re-establecer conexión tenant en cada hidratación de Livewire
     */
    public function hydrate()
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


    /**
     * Renderizar los productos en la vista
     * 
     * Filtra automáticamente los productos por la bodega (store) del usuario autenticado.
     * La bodega se obtiene desde la BD central (RAP) a través de:
     * Auth::user() → contact_id → vnt_contacts.store
     * 
     * Los productos se filtran mediante joins comenzando desde inv_store:
     * inv_store (WHERE id = user's store) → inv_items_store → inv_items
     * Esto asegura que solo se traigan items disponibles en la bodega específica del usuario.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        try {
            $this->ensureTenantConnection();
            $userStoreId = $this->getUserStoreId();

            $query = Items::query()
                ->select(
                    'inv_items.*',
                    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(inv_store.name, ":", inv_items_store.stock_items_store) SEPARATOR ", ") as store_stock_details'),
                    DB::raw('SUM(inv_items_store.stock_items_store) as total_stock')
                )
                ->where('inv_items.status', 1)
                ->with(['principalImage', 'invValues', 'tax'])
                ->join('inv_items_store', 'inv_items.id', '=', 'inv_items_store.itemId')
                ->join('inv_store', 'inv_items_store.storeId', '=', 'inv_store.id')
                ->where('inv_items_store.stock_items_store', '>=', 0) // Mostrar todas las bodegas, incluyendo stock 0
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('inv_items.name', 'like', '%' . $this->search . '%')
                            ->orWhere('inv_items.internal_code', 'like', '%' . $this->search . '%')
                            ->orWhere('inv_items.sku', 'like', '%' . $this->search . '%')
                            ->orWhere('inv_items.description', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->selectedCategory, function ($query) {
                    $query->where('inv_items.categoryId', $this->selectedCategory);
                })
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
                )
                ->orderBy('inv_items.' . $this->sortField, $this->sortDirection);

            $products = $query->paginate($this->perPage);

            Log::info('✅ Productos cargados', [
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'last_page' => $products->lastPage(),
                'productos_en_pagina' => $products->count(),
                'productos_ids' => $products->pluck('id')->toArray(),
                'productos_nombres' => $products->pluck('name')->toArray(),
                'productos_stock_detalles' => $products->pluck('store_stock_details')->toArray(),
                'productos_stock_total' => $products->pluck('total_stock')->toArray()
            ]);

            // LOG DETALLADO DE PRECIOS POR PRODUCTO
            foreach ($products as $product) {
                // Obtener todos los precios del producto
                $allPrices = $product->all_prices;

                // Obtener específicamente Precio Regular y Precio Crédito desde inv_values
                $precioRegular = $product->invValues()
                    ->where('type', 'precio')
                    ->where('label', 'Precio Regular')
                    ->orderBy('date', 'desc')
                    ->first();

                $precioCredito = $product->invValues()
                    ->where('type', 'precio')
                    ->where('label', 'Precio Crédito')
                    ->orderBy('date', 'desc')
                    ->first();

                Log::info('💰 Precios del producto', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'all_prices_count' => count($allPrices),
                    'all_prices' => $allPrices,
                    'precio_regular_exists' => $precioRegular ? 'SÍ' : 'NO',
                    'precio_regular_value' => $precioRegular ? $precioRegular->values : null,
                    'precio_regular_label' => $precioRegular ? $precioRegular->label : null,
                    'precio_credito_exists' => $precioCredito ? 'SÍ' : 'NO',
                    'precio_credito_value' => $precioCredito ? $precioCredito->values : null,
                    'precio_credito_label' => $precioCredito ? $precioCredito->label : null,
                    'inv_values_total' => $product->invValues->count(),
                    'inv_values_precios' => $product->invValues()
                        ->where('type', 'precio')
                        ->get()
                        ->map(function ($value) {
                            return [
                                'label' => $value->label,
                                'value' => $value->values,
                                'date' => $value->date
                            ];
                        })
                        ->toArray()
                ]);
            }

            // Si estamos en una página que no existe, resetear a la página 1
            if ($products->currentPage() > $products->lastPage() && $products->total() > 0) {
                Log::warning('⚠️ Página actual mayor que última página, reseteando', [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage()
                ]);
                $this->resetPage();
                $products = $query->paginate($this->perPage);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en render() de ProductQuoter', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al cargar productos: ' . $e->getMessage()
            ]);

            // Retornar vista vacía en caso de error
            $products = Items::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }

        $viewName = $this->viewType === 'mobile'
            ? 'livewire.tenant.quoter.components.mobile-product-quoter'
            : 'livewire.tenant.quoter.components.desktop-product-quoter';

        return view($viewName, [
            'products' => $products
        ])->layout('layouts.app');
    }

    // Método para obtener las categorías
    public function getCategories()
    {
        $this->ensureTenantConnection();
        return Category::where('status', 1)->get();
    }

    public function addToQuoter($productId, $selectedPrice, $priceLabel)
    {
        // Verificar si el producto ya está en el cotizador (sin consulta DB)
        $existingIndex = $this->findProductInQuoter($productId);

        if ($existingIndex !== false) {
            // Si ya existe, incrementar la cantidad
            $this->quoterItems[$existingIndex]['quantity']++;
        } else {
            // Obtener el producto solo cuando es necesario
            $this->ensureTenantConnection();
            $product = Items::with('tax')->find($productId);

            if (!$product) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Producto no encontrado'
                ]);
                return;
            }

            // Si no existe, agregarlo con el precio seleccionado
            $this->quoterItems[] = [
                'id' => $product->id,
                'name' => $product->display_name,
                'sku' => $product->sku,
                'price' => $selectedPrice,
                'tax' => $product->tax->percentage,
                'tax_label' => $product->tax->name,
                'price_label' => $priceLabel,
                'quantity' => 1,
                'description' => $product->description,
            ];
        }

        // Marcar que hay cambios si estamos editando
        if ($this->isEditing) {
            $this->hasChanges = true;
        }

        // Optimización: Solo guardar en sesión si realmente cambió
        session(['quoter_items' => $this->quoterItems]);

        // Calcular total de forma más eficiente
        $this->calculateTotal();

        // Toast más rápido con el nombre del producto
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Producto agregado: ' . ($product->display_name ?? 'Producto')
        ]);
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromQuoter($index);
            return;
        }

        $this->quoterItems[$index]['quantity'] = $quantity;

        // Marcar que hay cambios si estamos editando
        if ($this->isEditing) {
            $this->hasChanges = true;
        }

        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();
    }

    public function removeFromQuoter($index)
    {
        unset($this->quoterItems[$index]);
        $this->quoterItems = array_values($this->quoterItems); // Reindexar array

        // Marcar que hay cambios si estamos editando
        if ($this->isEditing) {
            $this->hasChanges = true;
        }

        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();

        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Producto removido del cotizador'
        ]);
    }





    //funcion lipiar cotizacion completa con cliente y carrito de compras
    public function clearQuoter()
    {
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->showCreateCustomerForm = false;
        $this->showCreateCustomerButton = false;
        $this->quoterItems = [];
        session()->forget('quoter_items');
        $this->calculateTotal();
        $this->showCartModal = false;

        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Cotizador limpiado'
        ]);
    }






    // funcion para guardar una cotizacion 
    public function saveQuote()
    {
        if (empty($this->quoterItems)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos en el cotizador'
            ]);
            return;
        }

        if (!$this->selectedCustomer) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe seleccionar un cliente para la cotización'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        try {
            // Iniciar transacción
            DB::connection('tenant')->beginTransaction();

            $userStoreId = $this->getUserStoreId();

            // La tabla vnt_quotes requiere un ID de vnt_contacts en el campo customerId
            // Buscamos el primer contacto asociado a esta empresa
            $contact = VntContacts::whereHas('company', function ($q) {
                $q->where('vnt_companies.id', $this->selectedCustomer['id']);
            })->first();

            // Si la empresa no tiene contactos, creamos uno genérico para permitir el registro
            if (!$contact) {
                $contact = VntContacts::create([
                    'firstName' => $this->selectedCustomer['firstName'] ?: $this->selectedCustomer['businessName'],
                    'lastName' => $this->selectedCustomer['lastName'] ?: 'Cliente',
                    'email' => $this->selectedCustomer['billingEmail'] ?: 'cliente@ejemplo.com',
                    'status' => 1,
                    'warehouseId' => session('warehouse_id', $userStoreId),
                    'positionId' => 1
                ]);

                Log::info('🆕 Contacto genérico creado automáticamente para la empresa', [
                    'company_id' => $this->selectedCustomer['id'],
                    'contact_id' => $contact->id
                ]);
            }

            // Obtener el siguiente consecutivo
            $lastQuote = VntQuote::orderBy('consecutive', 'desc')->first();
            $nextConsecutive = $lastQuote ? $lastQuote->consecutive + 1 : 1;

            // Crear la cotización
            $quote = VntQuote::create([
                'consecutive' => $nextConsecutive,
                'status' => 'REGISTRADO',
                'typeQuote' => 'POS',
                'customerId' => $contact->id, // USAR EL ID DEL CONTACTO AQUÍ (Referencia a vnt_contacts)
                'warehouseId' => session('warehouse_id', $userStoreId), // Usar userStoreId como fallback
                'userId' => auth()->id(),
                'observations' => $this->observaciones,
                'branchId' => session('branch_id', $userStoreId) // Usar userStoreId como fallback
            ]);

            // Crear los detalles de la cotización
            foreach ($this->quoterItems as $item) {
                Log::info('💾 Guardando detalle de cotización', [
                    'item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'price_label' => $item['price_label'] ?? 'Precio',
                    'quantity' => $item['quantity'],
                    'tax' => $item['tax'] ?? 0
                ]);

                Log::info('💾 GUARDANDO DETALLE en cotización', [
                    'product_name' => $item['name'],
                    'item_price_from_quoter' => $item['price'],
                    'quantity' => $item['quantity'],
                    'tax_percentage' => $item['tax'] ?? 0,
                    'price_label' => $item['price_label'] ?? 'Precio'
                ]);

                VntDetailQuote::create([
                    'quantity' => $item['quantity'],
                    'tax' => $item['tax'] ?? 0,
                    'value' => $item['price'],
                    'quoteId' => $quote->id,
                    'itemId' => $item['id'],
                    'description' => $item['name'],
                    'priceList' => $item['price'],
                    'price_label' => $item['price_label'] ?? 'Precio' // Guardar el label de la lista de precios
                ]);
            }

            // Confirmar transacción
            DB::connection('tenant')->commit();

            Log::info('✅ Cotización guardada exitosamente', [
                'quote_id' => $quote->id,
                'consecutive' => $nextConsecutive
            ]);

            // Limpiar el cotizador y campos del formulario
            $this->quoterItems = [];
            $this->selectedCustomer = null;              // Limpiar cliente seleccionado
            $this->customerSearch = '';                  // Limpiar campo de búsqueda de cliente
            $this->showCreateCustomerForm = false;      // Ocultar formulario de creación
            $this->showCreateCustomerButton = false;    // Ocultar botón de creación
            session()->forget('quoter_items');
            $this->calculateTotal();
            $this->showCartModal = false;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cotización #' . $nextConsecutive . ' guardada exitosamente'
            ]);

            // Redirigir a la página de cotizaciones según el tipo de vista
            $routeName = $this->viewType === 'mobile'
                ? 'tenant.quoter.mobile'
                : 'tenant.quoter.desktop';

            return redirect()->route($routeName);
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::connection('tenant')->rollBack();

            Log::error('❌ Error al guardar cotización', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar la cotización: ' . $e->getMessage()
            ]);
        }
    }


    //funcion para validar la cantidad ingresada
    public function validateQuantity($index = null)
    {
        // Si no se pasa el índice, intentar obtenerlo de las propiedades (para Livewire binding)
        // Pero como estamos usando wire:model.lazy="quoterItems.{{ $index }}.quantity", 
        // Livewire ya actualiza el valor antes de llamar a este método.

        if ($index === null) {
            // En caso de que se llame sin índice, validamos todos los items
            foreach ($this->quoterItems as $idx => $item) {
                $this->sanitizeItemQuantity($idx);
            }
        } else {
            if (!isset($this->quoterItems[$index])) {
                return;
            }
            $this->sanitizeItemQuantity($index);
        }

        // Actualizar sesión
        session(['quoter_items' => $this->quoterItems]);

        // Recalcular total
        $this->calculateTotal();

        // Notificación opcional
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Contenido actualizado'
        ]);
    }

    private function sanitizeItemQuantity($index)
    {
        $quantity = $this->quoterItems[$index]['quantity'];

        if ($quantity === '' || !is_numeric($quantity) || intval($quantity) < 1) {
            $this->quoterItems[$index]['quantity'] = 1;
        } else {
            $this->quoterItems[$index]['quantity'] = intval($quantity);
        }
    }






    //metodo cerrar modal de carrito
    public function toggleCartModal()
    {
        $this->showCartModal = !$this->showCartModal;
    }


    // funcion para buscar cliente (Búsqueda predictiva)
    public function updatedCustomerSearch($value)
    {
        if (strlen($value) < 2) {
            $this->customerResults = [];
            return;
        }

        $this->ensureTenantConnection();

        $this->customerResults = VntCompany::select('id', 'businessName', 'firstName', 'lastName', 'identification', 'billingEmail')
            ->whereNot('type', 'PROVEEDOR') // Excluir proveedores
            ->where(function ($query) use ($value) {
                $query->where('identification', 'like', '%' . $value . '%')
                    ->orWhere('businessName', 'like', '%' . $value . '%')
                    ->orWhere('firstName', 'like', '%' . $value . '%')
                    ->orWhere('lastName', 'like', '%' . $value . '%');
            })
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function selectCustomer($customerId)
    {
        $this->ensureTenantConnection();
        $customer = VntCompany::find($customerId);

        if ($customer) {
            $this->selectedCustomer = $customer->toArray();
            $this->customerResults = [];
            $this->customerSearch = ''; // Opcional: limpiar búsqueda al seleccionar
            $this->showCreateCustomerButton = false;

            // Marcar que hay cambios si estamos editando
            if ($this->isEditing) {
                $this->hasChanges = true;
            }

            $name = $customer->businessName ?: ($customer->firstName . ' ' . $customer->lastName);
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cliente seleccionado: ' . $name
            ]);
        }
    }

    // Mantener searchCustomer por compatibilidad o si se presiona Enter, pero adaptado
    public function searchCustomer()
    {
        if (empty($this->customerSearch)) return;

        $this->ensureTenantConnection();
        $customer = VntCompany::where('identification', $this->customerSearch)->whereNot('type', 'PROVEEDOR')->first();

        if ($customer) {
            $this->selectCustomer($customer->id);
        } else {
            $this->customerResults = [];
            $this->showCreateCustomerButton = true;
            $this->dispatch('show-toast', [
                'type' => 'info',
                'message' => 'Cliente no encontrado. Puedes crear uno nuevo'
            ]);
        }
    }






    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->showCreateCustomerForm = false;
        $this->showCreateCustomerButton = false;
        $this->editingCustomerId = null;
    }

    /**
     * Editar el cliente actualmente seleccionado
     */
    public function editCustomer()
    {
        Log::info('🔧 editCustomer() llamado', [
            'selectedCustomer' => $this->selectedCustomer,
            'showCreateCustomerForm_antes' => $this->showCreateCustomerForm,
            'editingCustomerId_antes' => $this->editingCustomerId
        ]);

        if ($this->selectedCustomer) {
            $this->editingCustomerId = $this->selectedCustomer['id'];
            $this->showCreateCustomerForm = true;
            $this->showCreateCustomerButton = false;

            Log::info('✅ Cliente configurado para edición', [
                'editingCustomerId' => $this->editingCustomerId,
                'showCreateCustomerForm' => $this->showCreateCustomerForm,
                'showCreateCustomerButton' => $this->showCreateCustomerButton
            ]);
        } else {
            Log::warning('⚠️ No hay cliente seleccionado para editar');
        }
    }

    public function showCreateCustomerForm()
    {
        $this->showCreateCustomerForm = true;
        $this->showCreateCustomerButton = false;
    }

    public function hideCreateCustomerForm()
    {
        $this->showCreateCustomerForm = false;
        $this->showCreateCustomerButton = true;
        $this->editingCustomerId = null;
    }

    public function cancelCreateCustomer()
    {
        $this->showCreateCustomerButton = false;
        $this->showCreateCustomerForm = false;
        $this->customerSearch = '';
        $this->editingCustomerId = null;
    }

    public function onCustomerCreated($customerId)
    {
        $this->ensureTenantConnection();

        // Buscar el cliente recién creado
        $customer = VntCompany::find($customerId);

        if ($customer) {
            // Seleccionar el cliente recién creado
            $this->selectedCustomer = [
                'id' => $customer->id,
                'businessName' => $customer->businessName,
                'firstName' => $customer->firstName,
                'lastName' => $customer->lastName,
                'identification' => $customer->identification,
                'billingEmail' => $customer->billingEmail,
            ];

            // Limpiar estados del formulario de creación/edición
            $this->showCreateCustomerForm = false;
            $this->showCreateCustomerButton = false;
            $this->customerSearch = '';
            $this->editingCustomerId = null;

            // Determinar el nombre a mostrar
            $customerName = $customer->businessName ?: $customer->firstName . ' ' . $customer->lastName;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cliente creado y seleccionado: ' . $customerName
            ]);
        }
    }

    public function onCustomerUpdated($customerId)
    {
        $this->ensureTenantConnection();

        // Verificar si es el cliente que está actualmente seleccionado
        if ($this->selectedCustomer && $this->selectedCustomer['id'] == $customerId) {
            // Buscar el cliente actualizado
            $customer = VntCompany::find($customerId);

            if ($customer) {
                // Actualizar los datos del cliente seleccionado
                $this->selectedCustomer = [
                    'id' => $customer->id,
                    'businessName' => $customer->businessName,
                    'firstName' => $customer->firstName,
                    'lastName' => $customer->lastName,
                    'identification' => $customer->identification,
                    'billingEmail' => $customer->billingEmail,
                ];

                // Limpiar estados del formulario de edición
                $this->showCreateCustomerForm = false;
                $this->editingCustomerId = null;

                // Determinar el nombre a mostrar
                $customerName = $customer->businessName ?: $customer->firstName . ' ' . $customer->lastName;

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Cliente actualizado: ' . $customerName
                ]);
            }
        }
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
        // Resetear valores
        $this->subTotal = 0;
        $this->taxBreakdown = [
            'iva_5' => 0,
            'iva_19' => 0,
            'exento' => 0,
            // Subtotales por tipo de IVA para cálculo preciso
            'subtotal_iva_5' => 0,
            'subtotal_iva_19' => 0,
        ];
        $this->totalTaxes = 0;

        $totalAmount = 0;

        // Procesar cada item del cotizador
        foreach ($this->quoterItems as $item) {
            $priceWithTax = $item['price'];
            $taxPercentage = (float)($item['tax'] ?? 0);
            $quantity = $item['quantity'];

            if ($taxPercentage > 0) {
                // Producto con impuestos - calcular precio base sin impuestos
                $priceBase = round($priceWithTax / (1 + ($taxPercentage / 100)), 2);
                $subtotalThisItem = $priceBase * $quantity;

                // Acumular subtotales por tipo de IVA
                if ($taxPercentage == 5) {
                    $this->taxBreakdown['subtotal_iva_5'] += $subtotalThisItem;
                } elseif ($taxPercentage == 19) {
                    $this->taxBreakdown['subtotal_iva_19'] += $subtotalThisItem;
                }
            } else {
                // Producto exento - el precio es el precio base
                $priceBase = $priceWithTax;
                $this->taxBreakdown['exento'] += $priceBase * $quantity;
            }

            // Sumar al subtotal (sin impuestos)
            $this->subTotal += $priceBase * $quantity;

            // Sumar al total (con impuestos)
            $totalAmount += $priceWithTax * $quantity;
        }

        // Calcular IVA como lo hace Alegra: porcentaje sobre el subtotal de cada tipo
        $this->taxBreakdown['iva_5'] = round($this->taxBreakdown['subtotal_iva_5'] * 0.05, 2);
        $this->taxBreakdown['iva_19'] = round($this->taxBreakdown['subtotal_iva_19'] * 0.19, 2);

        // Calcular total de impuestos
        $this->totalTaxes = $this->taxBreakdown['iva_5'] + $this->taxBreakdown['iva_19'];

        // Redondear valores finales
        $this->subTotal = round($this->subTotal, 2);
        // IVA ya está redondeado en el cálculo anterior
        $this->taxBreakdown['exento'] = round($this->taxBreakdown['exento'], 2);
        $this->totalTaxes = round($this->totalTaxes, 2);

        // IMPORTANTE: Calcular total como subtotal + impuestos (igual que Alegra)
        // NO usar la suma de precios originales que puede tener discrepancias de redondeo
        $this->totalAmount = round($this->subTotal + $this->totalTaxes, 2);

        Log::debug('🧮 Desglose de impuestos calculado', [
            'subtotal' => $this->subTotal,
            'tax_breakdown' => $this->taxBreakdown,
            'total_taxes' => $this->totalTaxes,
            'total_amount' => $this->totalAmount,
            'items_count' => count($this->quoterItems)
        ]);
    }

    public function getQuoterCountProperty()
    {
        return collect($this->quoterItems)->sum('quantity');
    }

    public function getIsInvoiceModuleActiveProperty()
    {
        try {
            $this->ensureTenantConnection();

            $result = DB::connection('tenant')
                ->table('cnf_company_options')
                ->where('option_id', 8)
                ->value('value');

            Log::info('🔧 Verificando módulo de facturación', [
                'option_id' => 8,
                'raw_value' => $result,
                'boolean_result' => (bool) $result,
                'tenant_connection' => 'OK'
            ]);

            return (bool) $result;
        } catch (\Exception $e) {
            Log::error('❌ Error verificando módulo de facturación', [
                'error' => $e->getMessage(),
                'tenant_id' => session('tenant_id'),
                'trace' => $e->getTraceAsString()
            ]);
            return false; // Por defecto desactivado si hay error
        }
    }

    public function getCanShowInvoiceButtonProperty()
    {
        // Solo mostrar botón de facturar cuando:
        // 1. El módulo de facturación está activo
        // 2. Estamos editando una cotización existente
        // 3. No hay cambios pendientes
        return $this->isInvoiceModuleActive && $this->isEditing && !$this->hasChanges;
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

    public function getSelectedPriceInfo($productId)
    {
        foreach ($this->quoterItems as $item) {
            if ($item['id'] == $productId) {
                return [
                    'price' => $item['price'],
                    'label' => $item['price_label'] ?? 'Precio'
                ];
            }
        }
        return null;
    }

    /**
     * Verificar si un precio específico está seleccionado para un producto
     * Útil para resaltar el precio en la vista cuando se está editando
     */
    public function isPriceSelected($productId, $priceLabel)
    {
        foreach ($this->quoterItems as $item) {
            if ($item['id'] == $productId && isset($item['price_label'])) {
                return $item['price_label'] === $priceLabel;
            }
        }
        return false;
    }

    public function increaseQuantity($productId)
    {
        $this->ensureTenantConnection();

        // Verificar si el producto ya está en el cotizador
        $existingIndex = $this->findProductInQuoter($productId);

        if ($existingIndex !== false) {
            // Si ya existe, incrementar la cantidad
            $this->quoterItems[$existingIndex]['quantity']++;

            // Guardar en sesión
            session(['quoter_items' => $this->quoterItems]);
            $this->calculateTotal();

            $productName = $this->quoterItems[$existingIndex]['name'] ?? 'Producto';
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cantidad aumentada: ' . $productName
            ]);
        }
    }

    public function decreaseQuantity($productId)
    {
        $this->ensureTenantConnection();

        // Verificar si el producto ya está en el cotizador
        $existingIndex = $this->findProductInQuoter($productId);

        if ($existingIndex !== false) {
            // Si la cantidad es mayor a 1, disminuir
            if ($this->quoterItems[$existingIndex]['quantity'] > 1) {
                $this->quoterItems[$existingIndex]['quantity']--;
                $this->calculateTotal();
                session(['quoter_items' => $this->quoterItems]);

                $productName = $this->quoterItems[$existingIndex]['name'] ?? 'Producto';
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => 'Cantidad disminuida: ' . $productName
                ]);
            } else {
                // Si la cantidad es 1, preguntar o remover (según diseño suele ser remover)
                $this->removeFromQuoter($existingIndex);
            }
        }
    }

    public function loadQuoteForEditing($quoteId)
    {
        $this->ensureTenantConnection();
        try {
            $quote = VntQuote::with(['detalles', 'customer', 'customer.company'])->findOrFail($quoteId);

            $this->editingQuoteId = $quoteId;
            $this->isEditing = true;
            $this->hasChanges = false;

            // Cargar observaciones de la cotización
            $this->observaciones = $quote->observations;

            // Inicializar estado del acordeón de observaciones
            $this->showObservations = !empty($quote->observations);

            // Cargar información del cliente usando la relación definida en el modelo
            if ($quote->customerId && $quote->customer) {
                $contact = $quote->customer; // Obtener el contacto desde la relación

                // Intentar obtener la empresa asociada al contacto
                $company = $contact->company;

                if ($company) {
                    // Si existe empresa asociada, usar datos de la empresa
                    $this->selectedCustomer = [
                        'id' => $company->id,
                        'businessName' => $company->businessName,
                        'firstName' => $company->firstName,
                        'lastName' => $company->lastName,
                        'identification' => $company->identification,
                        'billingEmail' => $company->billingEmail,
                    ];
                } else {
                    // Si no hay empresa asociada, usar datos del contacto
                    $this->selectedCustomer = [
                        'id' => $contact->id,
                        'businessName' => $contact->full_name,
                        'firstName' => $contact->firstName,
                        'lastName' => $contact->lastName,
                        'identification' => null, // VntContacts no tiene identification
                        'billingEmail' => $contact->email,
                    ];
                }

                Log::info('🔄 Cliente cargado para edición', [
                    'contact_id' => $contact->id,
                    'company_id' => $company ? $company->id : null,
                    'selectedCustomer' => $this->selectedCustomer
                ]);
            } else {
                Log::warning('⚠️ No se pudo cargar cliente para edición', [
                    'quote_id' => $quoteId,
                    'customerId' => $quote->customerId,
                    'customer_exists' => $quote->customer ? 'YES' : 'NO'
                ]);
            }

            // Cargar productos de la cotización
            $this->quoterItems = [];
            Log::info('📦 Iniciando carga de productos de la cotización', [
                'quote_id' => $quoteId,
                'detalles_count' => $quote->detalles->count()
            ]);

            foreach ($quote->detalles as $detalle) {
                Log::info('📦 Procesando detalle de cotización', [
                    'detalle_id' => $detalle->id,
                    'itemId' => $detalle->itemId,
                    'value' => $detalle->value,
                    'price_label_from_db' => $detalle->price_label ?? 'NO EXISTE EN BD',
                    'priceList' => $detalle->priceList,
                    'quantity' => $detalle->quantity,
                    'detalle_completo' => $detalle->toArray()
                ]);

                $product = Items::with('tax')->find($detalle->itemId);
                if ($product) {
                    $itemData = [
                        'id' => $product->id,
                        'name' => $product->display_name,
                        'sku' => $product->sku,
                        'price' => $detalle->value,
                        'price_label' => $detalle->price_label ?? 'Precio seleccionado', // Recuperar el label guardado
                        'quantity' => $detalle->quantity,
                        'description' => $product->description,
                        'tax' => $product->tax->percentage ?? 0,
                        'tax_label' => $product->tax->name ?? 'IVA',
                    ];

                    $this->quoterItems[] = $itemData;

                    Log::info('✅ Producto agregado a quoterItems', [
                        'item_data' => $itemData
                    ]);
                } else {
                    Log::warning('⚠️ Producto no encontrado', [
                        'itemId' => $detalle->itemId
                    ]);
                }
            }

            Log::info('📦 Productos cargados completamente', [
                'quoterItems_count' => count($this->quoterItems),
                'quoterItems' => $this->quoterItems
            ]);

            // Guardar en sesión
            session(['quoter_items' => $this->quoterItems]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cotización #' . $quote->consecutive . ' cargada para edición'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al cargar la cotización: ' . $e->getMessage()
            ]);
        }
    }

    public function updateQuote()
    {
        Log::info('📤 INICIO - Actualizando cotización', [
            'isEditing' => $this->isEditing,
            'editingQuoteId' => $this->editingQuoteId,
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$this->isEditing || !$this->editingQuoteId) {
            Log::warning('⚠️ No hay cotización en modo edición');
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay cotización en modo edición'
            ]);
            return;
        }

        if (empty($this->quoterItems)) {
            Log::warning('⚠️ No hay productos en el cotizador');
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos en el cotizador'
            ]);
            return;
        }

        if (!$this->selectedCustomer) {
            Log::warning('⚠️ No hay cliente seleccionado');
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe seleccionar un cliente para la cotización'
            ]);
            return;
        }

        Log::info('📋 Datos a enviar para actualización', [
            'editingQuoteId' => $this->editingQuoteId,
            'selectedCustomer' => $this->selectedCustomer,
            'observaciones' => $this->observaciones,
            'quoterItems_count' => count($this->quoterItems),
            'quoterItems' => $this->quoterItems
        ]);

        $this->ensureTenantConnection();

        try {
            // Iniciar transacción
            DB::connection('tenant')->beginTransaction();

            $quote = VntQuote::findOrFail($this->editingQuoteId);
            $userStoreId = $this->getUserStoreId();

            Log::info('📋 Cotización encontrada en BD (antes de actualizar)', [
                'quote_id' => $quote->id,
                'consecutive' => $quote->consecutive,
                'current_customerId' => $quote->customerId,
                'current_observations' => $quote->observations,
                'quote_raw' => $quote->toArray()
            ]);

            // La tabla vnt_quotes requiere un ID de vnt_contacts en el campo customerId
            // Buscamos el primer contacto asociado a esta empresa
            $contact = VntContacts::whereHas('company', function ($q) {
                $q->where('vnt_companies.id', $this->selectedCustomer['id']);
            })->first();

            // Si la empresa no tiene contactos, creamos uno genérico para permitir el registro
            if (!$contact) {
                $contact = VntContacts::create([
                    'firstName' => $this->selectedCustomer['firstName'] ?: $this->selectedCustomer['businessName'],
                    'lastName' => $this->selectedCustomer['lastName'] ?: 'Cliente',
                    'email' => $this->selectedCustomer['billingEmail'] ?: 'cliente@ejemplo.com',
                    'status' => 1,
                    'warehouseId' => session('warehouse_id', $userStoreId),
                    'positionId' => 1
                ]);

                Log::info('🆕 Contacto genérico creado automáticamente para la empresa', [
                    'company_id' => $this->selectedCustomer['id'],
                    'contact_id' => $contact->id
                ]);
            }

            Log::info('👤 Contacto obtenido/creado para actualización', [
                'contact_id' => $contact->id,
                'company_id' => $this->selectedCustomer['id']
            ]);

            // Actualizar la cotización
            $updateData = [
                'customerId' => $contact->id, // USAR EL ID DEL CONTACTO (Referencia a vnt_contacts)
                'observations' => $this->observaciones,
                'warehouseId' => session('warehouse_id', $userStoreId),
                'userId' => auth()->id(),
                'branchId' => session('branch_id', $userStoreId)
            ];

            Log::info('💾 Datos que se van a actualizar en vnt_quotes', [
                'update_data' => $updateData
            ]);

            $quote->update($updateData);

            Log::info('✅ Cotización actualizada en BD', [
                'quote_id' => $quote->id,
                'new_customerId' => $quote->customerId,
                'new_observations' => $quote->observations
            ]);

            // Eliminar detalles existentes
            $deletedCount = VntDetailQuote::where('quoteId', $quote->id)->count();
            Log::info('🗑️ Eliminando detalles existentes', [
                'quote_id' => $quote->id,
                'detalles_a_eliminar' => $deletedCount
            ]);

            VntDetailQuote::where('quoteId', $quote->id)->delete();

            Log::info('✅ Detalles eliminados');

            // Crear los nuevos detalles
            Log::info('📦 Creando nuevos detalles', [
                'items_count' => count($this->quoterItems)
            ]);

            foreach ($this->quoterItems as $index => $item) {
                $detalleData = [
                    'quantity' => $item['quantity'],
                    'tax' => $item['tax'] ?? 0,
                    'value' => $item['price'],
                    'quoteId' => $quote->id,
                    'itemId' => $item['id'],
                    'description' => $item['name'],
                    'priceList' => $item['price'],
                    'price_label' => $item['price_label'] ?? 'Precio' // Guardar el label de la lista de precios
                ];

                Log::info("📦 Creando detalle #{$index}", [
                    'detalle_data' => $detalleData
                ]);

                $newDetalle = VntDetailQuote::create($detalleData);

                Log::info("✅ Detalle creado", [
                    'detalle_id' => $newDetalle->id,
                    'detalle_raw' => $newDetalle->toArray()
                ]);
            }

            // Resetear bandera de cambios ya que se guardó exitosamente
            $this->hasChanges = false;
            // Confirmar transacción
            DB::connection('tenant')->commit();

            Log::info('✅ FINALIZADO - Cotización actualizada exitosamente', [
                'quote_id' => $quote->id,
                'consecutive' => $quote->consecutive,
                'detalles_creados' => count($this->quoterItems)
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cotización #' . $quote->consecutive . ' actualizada exitosamente'
            ]);

            // Limpiar después de actualizar
            $this->clearQuoter();
            $this->isEditing = false;
            $this->editingQuoteId = null;

            // Redirigir al listado de cotizaciones según el tipo de vista
            $routeName = $this->viewType === 'mobile'
                ? 'tenant.quoter.mobile'
                : 'tenant.quoter.desktop';

            return redirect()->route($routeName);
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::connection('tenant')->rollBack();

            Log::error('❌ ERROR al actualizar cotización', [
                'editingQuoteId' => $this->editingQuoteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmOrder()
    {
        if (!$this->isEditing || !$this->editingQuoteId) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Primero debes cargar una cotización para confirmarla como pedido'
            ]);
            return;
        }

        if (empty($this->quoterItems)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos seleccionados'
            ]);
            return;
        }

        if (!$this->selectedCustomer) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debes tener un cliente seleccionado'
            ]);
            return;
        }

        // Mostrar modal de selección de tipo de entrega
        $this->showDeliveryModal = true;
    }

    /**
     * Cargar tipos de entrega activos
     */
    public function loadDeliveryTypes()
    {
        $this->ensureTenantConnection();
        try {
            $types = InvDeliveryType::on('tenant')->active()->get();
            // Convertir a array para evitar problemas de serialización de Livewire
            $this->deliveryTypes = $types->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'ask_details' => $type->ask_details,
                    'detail' => $type->detail
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error cargando tipos de entrega: ' . $e->getMessage());
            $this->deliveryTypes = [];
        }
    }

    /**
     * Cargar métodos de pago activos
     */
    public function loadMethodPayments()
    {
        $this->ensureTenantConnection();
        try {
            $methods = \App\Models\Tenant\MethodPayments\VntMethodPayMents::on('tenant')
                ->where('status', 1)
                ->get();

            // Convertir a array para evitar problemas de serialización de Livewire
            $this->methodPayments = $methods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'description' => $method->description,
                    'type' => $method->type,
                    'method' => $method->method,
                    'bank' => $method->bank
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error cargando métodos de pago: ' . $e->getMessage());
            $this->methodPayments = [];
        }
    }

    /**
     * Cuando se selecciona un tipo de entrega
     */
    public function updatedSelectedDeliveryType($value)
    {
        if ($value) {
            // Buscar en el array de tipos
            $deliveryType = collect($this->deliveryTypes)->firstWhere('id', $value);
            $this->requiresDeliveryDetails = $deliveryType ? $deliveryType['ask_details'] : false;

            // Verificar si el tipo de entrega es "Otro" para mostrar campo adicional
            $this->showOtherDeliveryInput = $deliveryType && strtolower($deliveryType['name']) === 'otro';

            if (!$this->requiresDeliveryDetails) {
                $this->deliveryDetails = '';
            }
            if (!$this->showOtherDeliveryInput) {
                $this->otherDeliveryDetails = '';
            }
        } else {
            $this->requiresDeliveryDetails = false;
            $this->deliveryDetails = '';
            $this->showOtherDeliveryInput = false;
            $this->otherDeliveryDetails = '';
        }
    }

    /**
     * Proceder con la creación de la remisión después de seleccionar tipo de entrega
     */
    public function proceedWithRemissionCreation()
    {
        if (!$this->selectedDeliveryType) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debes seleccionar un tipo de entrega'
            ]);
            return;
        }

        if (!$this->selectedMethodPayment) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debes seleccionar un método de pago'
            ]);
            return;
        }

        // Buscar en el array de tipos
        $deliveryType = collect($this->deliveryTypes)->firstWhere('id', $this->selectedDeliveryType);

        if ($deliveryType && $deliveryType['ask_details'] && empty(trim($this->deliveryDetails))) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Este tipo de entrega requiere detalles adicionales'
            ]);
            return;
        }

        // Validar campo "otro" tipo de entrega si está visible
        if ($this->showOtherDeliveryInput && empty(trim($this->otherDeliveryDetails))) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debes especificar cuál es el otro tipo de entrega'
            ]);
            return;
        }

        // Cerrar modal y crear remisión
        $this->showDeliveryModal = false;
        $this->createRemissionWithDeliveryType();
    }

    /**
     * Crear la remisión con el tipo de entrega seleccionado
     */
    private function createRemissionWithDeliveryType()
    {
        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->beginTransaction();

            $quote = VntQuote::findOrFail($this->editingQuoteId);

            // Validar stock disponible antes de procesar
            foreach ($this->quoterItems as $item) {
                $itemStore = InvItemsStore::where('itemId', $item['id'])
                    ->where('storeId', $quote->warehouseId)
                    ->first();

                if (!$itemStore) {
                    DB::connection('tenant')->rollBack();
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => "El producto '{$item['name']}' no está disponible en esta bodega"
                    ]);
                    return;
                }

                $newStock = $itemStore->stock_items_store - $item['quantity'];

                if ($newStock < 0) {
                    DB::connection('tenant')->rollBack();
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => "Stock insuficiente para '{$item['name']}'. Solicitado: {$item['quantity']}, Disponible: {$itemStore->stock_items_store}"
                    ]);
                    return;
                }
            }

            // Obtener siguiente consecutivo de remisiones
            $lastRemission = InvRemissions::orderBy('consecutive', 'desc')->first();
            $nextConsecutive = $lastRemission ? $lastRemission->consecutive + 1 : 1;

            // Determinar las observaciones de retorno
            $observationsReturn = $this->deliveryDetails;
            if ($this->showOtherDeliveryInput && !empty($this->otherDeliveryDetails)) {
                $observationsReturn = $this->otherDeliveryDetails;
            }

            // Crear Remisión con tipo de entrega y método de pago
            $remission = InvRemissions::create([
                'consecutive' => $nextConsecutive,
                'status' => 'REGISTRADO',
                'quoteId' => $quote->id,
                'warehouseId' => $quote->warehouseId,
                'deliveryTypeId' => $this->selectedDeliveryType,
                'methodPaymentId' => $this->selectedMethodPayment,
                'userId' => auth()->id(),
                'deliveryDate' => now()->format('Y-m-d'),
                'expiration' => 0,
                'modify' => 0,
                'observations_return' => $observationsReturn
            ]);

            // Crear detalles de la remisión y actualizar stock
            foreach ($this->quoterItems as $item) {
                InvDetailRemissions::create([
                    'quantity' => $item['quantity'],
                    'tax' => $item['tax'],
                    'tax_label' => $item['tax_label'],
                    'value' => $item['price'],
                    'remissionId' => $remission->id,
                    'itemId' => $item['id'],
                    'invoiceId' => null,
                ]);

                // Actualizar el stock del producto en la bodega específica
                $itemStore = InvItemsStore::where('itemId', $item['id'])
                    ->where('storeId', $quote->warehouseId)
                    ->first();

                $itemStore->update([
                    'stock_items_store' => $itemStore->stock_items_store - $item['quantity'],
                ]);
            }

            // Actualizar estado de la cotización
            $quote->update(['status' => 'REMISIÓN']);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => '¡Pedido confirmado exitosamente! Remisión #' . $remission->consecutive
            ]);

            // Limpiar y salir del modo edición
            $this->cancelEditing();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error en confirmOrder: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al confirmar el pedido: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cerrar modal de tipo de entrega
     */
    public function closeDeliveryModal()
    {
        $this->showDeliveryModal = false;
        $this->selectedDeliveryType = null;
        $this->deliveryDetails = '';
        $this->requiresDeliveryDetails = false;
        $this->selectedMethodPayment = null;
        $this->showOtherDeliveryInput = false;
        $this->otherDeliveryDetails = '';
    }

    /**
     * Alternar entre modo grid y tabla
     */
    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'table' : 'grid';
    }

    public function invoiceOrder()
    {
        // Permitir facturación tanto para cotizaciones existentes como nuevas
        // Si no está editando, debe crear la cotización primero
        if (!$this->isEditing || !$this->editingQuoteId) {
            // Si no está editando, primero guardar la cotización
            Log::info('🔄 Creando cotización antes de facturar', [
                'isEditing' => $this->isEditing,
                'editingQuoteId' => $this->editingQuoteId
            ]);

            $this->saveQuote(); // Esto creará la cotización y establecerá $editingQuoteId

            // Verificar que se creó correctamente
            if (!$this->editingQuoteId) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Error al crear la cotización'
                ]);
                return;
            }
        }

        if (empty($this->quoterItems)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos seleccionados'
            ]);
            return;
        }

        if (!$this->selectedCustomer) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debes tener un cliente seleccionado'
            ]);
            return;
        }

        // NUEVO FLUJO: Abrir modal de pagos antes de facturar
        Log::info('💰 Abriendo modal de pagos antes de facturar', [
            'quote_id' => $this->editingQuoteId,
            'customer_id' => $this->selectedCustomer['id'] ?? null,
            'total_amount' => $this->totalAmount
        ]);

        // Inicializar datos del modal de pagos
        $this->initPaymentModal();

        // Mostrar modal de pagos
        $this->showPaymentModal = true;
    }

    /**
     * Inicializar modal de pagos con datos de la cotización
     */
    public function initPaymentModal()
    {
        // Resetear métodos de pago
        foreach ($this->paymentMethods as $key => $method) {
            $this->paymentMethods[$key]['value'] = 0;
            $this->paymentMethods[$key]['selected'] = false;
        }

        // Calcular retenciones
        $this->calculateRetentionsForModal();

        // Calcular balances iniciales
        $this->calculatePaymentBalances();

        Log::info('💳 Modal de pagos inicializado', [
            'quote_id' => $this->editingQuoteId,
            'total_amount' => $this->totalAmount,
            'total_with_retentions' => $this->totalWithRetentions,
            'retentions' => $this->retentions,
            'show_retentions' => $this->showRetentions,
            'customer' => $this->selectedCustomer['businessName'] ?? $this->selectedCustomer['firstName'] ?? 'Sin nombre'
        ]);
    }

    /**
     * Calcular retenciones para mostrar en el modal
     */
    public function calculateRetentionsForModal()
    {
        // Resetear retenciones
        $this->retentions = [
            'retention_fuente' => 0,
            'retention_ica' => 0,
            'retention_iva' => 0,
        ];
        $this->showRetentions = false;
        $this->totalWithRetentions = $this->totalAmount;

        // Solo calcular si hay cliente seleccionado y configuración habilitada
        if (!$this->selectedCustomer || !config('facturacion.retentions.auto_calculate', true)) {
            return;
        }

        $retentionCalculator = new RetentionCalculatorService();

        // Obtener datos del cliente para calcular retenciones con bases oficiales 2026
        // Obtener régimen fiscal
        $regimeDescription = 'COMMON_REGIME'; // Por defecto
        if (isset($this->selectedCustomer['company']['regimen'])) {
            $regimeDescription = $this->selectedCustomer['company']['regimen'] === 'COMUN' ? 'COMMON_REGIME' : 'SPECIAL_REGIME';
        }

        // Obtener responsabilidad fiscal
        $fiscalResponsability = 0;
        if (isset($this->selectedCustomer['company']['fiscal_responsibility_id'])) {
            $fiscalResponsability = (int)$this->selectedCustomer['company']['fiscal_responsibility_id'];
        } elseif (isset($this->selectedCustomer['fiscal_responsibility_id'])) {
            $fiscalResponsability = (int)$this->selectedCustomer['fiscal_responsibility_id'];
        }

        // Obtener ciudad
        $city = '';
        if (isset($this->selectedCustomer['company']['city'])) {
            $city = $this->selectedCustomer['company']['city'];
        } elseif (isset($this->selectedCustomer['city'])) {
            $city = $this->selectedCustomer['city'];
        }

        // Calcular retenciones con bases oficiales 2026
        Log::info('🔍 SUBTOTAL EXACTO para retenciones', [
            'subTotal' => $this->subTotal,
            'totalAmount' => $this->totalAmount,
            'calculated_retention_2_5_percent' => $this->subTotal * 0.025,
            'rounded_retention' => round($this->subTotal * 0.025, 2)
        ]);

        $calculatedRetentions = $retentionCalculator->calculateAllRetentions([
            'regime_description' => $regimeDescription,
            'fiscal_responsability' => $fiscalResponsability,
            'city' => $city,
            'sub_total' => $this->subTotal
        ]);

        // Redondear retenciones para visualización, pero mantener precisión para cálculos internos
        $this->retentions = [
            'retention_fuente' => round($calculatedRetentions['retention_fuente'], 2),
            'retention_ica' => round($calculatedRetentions['retention_ica'], 2),
            'retention_iva' => round($calculatedRetentions['retention_iva'], 2),
        ];

        // Mostrar retenciones si hay alguna > 0
        $this->showRetentions = ($calculatedRetentions['retention_fuente'] > 0 ||
                                $calculatedRetentions['retention_ica'] > 0 ||
                                $calculatedRetentions['retention_iva'] > 0);

        // Calcular total con retenciones usando valores sin redondear para máxima precisión
        $totalRetentions = $calculatedRetentions['retention_fuente'] +
                          $calculatedRetentions['retention_ica'] +
                          $calculatedRetentions['retention_iva'];

        $this->totalWithRetentions = round($this->totalAmount - $totalRetentions, 2);

        // Consultar saldo exacto de Alegra si hay una factura asociada
        $this->updateWithAlegraBalance();

        Log::info('💰 Retenciones calculadas para modal', [
            'customer_id' => $this->selectedCustomer['id'] ?? null,
            'regime' => $regimeDescription,
            'fiscal_responsibility' => $fiscalResponsability,
            'city' => $city,
            'total_amount' => $this->totalAmount,
            'retentions' => $calculatedRetentions,
            'total_with_retentions' => $this->totalWithRetentions,
            'show_retentions' => $this->showRetentions
        ]);
    }

    /**
     * Obtiene el saldo disponible para un método de pago específico
     */
    public function getAvailableBalanceForMethod($methodKey)
    {
        // Para efectivo, no hay límite (puede dar cambio)
        if ($methodKey === 'efectivo') {
            return PHP_FLOAT_MAX; // Sin límite
        }

        // Para otros métodos, calcular saldo disponible excluyendo efectivo
        $totalPaidNonCash = 0;
        foreach ($this->paymentMethods as $key => $method) {
            if ($key !== $methodKey && $key !== 'efectivo') {
                $totalPaidNonCash += (float) ($method['value'] ?? 0);
            }
        }

        // El máximo disponible es el total menos lo pagado por otros métodos no-efectivo
        return max(0, $this->totalAmount - $totalPaidNonCash);
    }

    /**
     * Actualizar valores con saldo exacto de Alegra para evitar residuales
     */
    private function updateWithAlegraBalance()
    {
        // Esta función solo aplica cuando hay una factura existente para procesar pago
        // En el cotizador no hay factura aún, así que no hacer nada
        return;
    }

    /**
     * Calcular balances de pagos
     */
    public function calculatePaymentBalances()
    {
        // Calcular total pagado sumando todos los métodos de pago
        $this->totalPaid = 0;
        foreach ($this->paymentMethods as $key => $method) {
            $value = round((float) ($method['value'] ?? 0), 2);
            $this->totalPaid += $value;
        }
        $this->totalPaid = round($this->totalPaid, 2);

        // Usar el total con retenciones si hay retenciones aplicables
        $totalToCompare = $this->showRetentions ? $this->totalWithRetentions : $this->totalAmount;

        // Calcular balance restante y cambio
        if ($this->totalPaid > $totalToCompare) {
            // Si se pagó más del total, calcular el cambio
            $this->remainingBalance = 0;
            $this->changeAmount = round($this->totalPaid - $totalToCompare, 2);
        } else {
            // Si falta dinero por pagar
            $this->remainingBalance = round($totalToCompare - $this->totalPaid, 2);
            $this->changeAmount = 0;
        }

        // Permitir proceder si hay algún pago (simplificar la lógica)
        $this->canProceedToPayment = $this->totalPaid > 0;

        Log::debug('💳 Balances de pago calculados', [
            'total_original' => $this->totalAmount,
            'total_with_retentions' => $this->totalWithRetentions,
            'total_used_for_calculation' => $totalToCompare,
            'total_paid' => $this->totalPaid,
            'remaining_balance' => $this->remainingBalance,
            'change_amount' => $this->changeAmount,
            'show_retentions' => $this->showRetentions
        ]);
    }

    /**
     * Actualizar valor de método de pago
     */
    public function updatePaymentMethodValue($method, $value)
    {
        $value = max(0, (float) ($value ?? 0));

        // Limitar el valor máximo para métodos que no son efectivo
        if ($method !== 'efectivo' && $value > $this->totalAmount) {
            $value = $this->totalAmount;
        }

        $this->paymentMethods[$method]['value'] = $value;
        $this->calculatePaymentBalances();
    }

    /**
     * Seleccionar método de pago activo
     */
    public function selectPaymentMethod($method)
    {
        $this->currentPaymentMethod = $method;
    }

    /**
     * Seleccionar método de pago y pagar todo con él
     */
    public function selectAndPayTotal($method)
    {
        // Seleccionar el método
        $this->currentPaymentMethod = $method;

        // Limpiar el valor actual del método para recalcular correctamente
        $this->paymentMethods[$method]['value'] = 0;

        // Recalcular totales sin el método actual
        $totalPaidOthers = 0;
        foreach ($this->paymentMethods as $key => $methodData) {
            if ($key !== $method) {
                $totalPaidOthers += (float) ($methodData['value'] ?? 0);
            }
        }

        // Calcular cuánto falta por pagar
        $remainingAmount = $this->totalAmount - $totalPaidOthers;

        // Asegurar que no sea negativo
        $remainingAmount = max(0, $remainingAmount);

        // Para métodos que no son efectivo, no puede exceder el total pendiente
        if ($method !== 'efectivo') {
            $remainingAmount = min($remainingAmount, $this->totalAmount);
        }

        // Redondear a 2 decimales para evitar problemas de precisión
        $remainingAmount = round($remainingAmount, 2);

        // Asignar el monto al método actual
        $this->paymentMethods[$method]['value'] = $remainingAmount;

        // Recalcular balances
        $this->calculatePaymentBalances();

        // Forzar actualización del frontend
        $this->dispatch('payment-updated', [
            'method' => $method,
            'value' => $remainingAmount
        ]);
    }

    /**
     * Pagar total con el método actual
     * Solo funciona para métodos que no son efectivo
     */
    public function payTotalWithCurrentMethod()
    {
        // Solo permitir para métodos que no son efectivo
        if ($this->currentPaymentMethod === 'efectivo') {
            return;
        }

        // Limpiar el valor actual del método para recalcular correctamente
        $this->paymentMethods[$this->currentPaymentMethod]['value'] = 0;

        // Recalcular totales sin el método actual
        $totalPaidOthers = 0;
        foreach ($this->paymentMethods as $key => $method) {
            if ($key !== $this->currentPaymentMethod) {
                $totalPaidOthers += (float) ($method['value'] ?? 0);
            }
        }

        // Calcular cuánto falta por pagar
        $remainingAmount = $this->totalAmount - $totalPaidOthers;

        // Asegurar que no sea negativo y no exceda el total
        $remainingAmount = max(0, min($remainingAmount, $this->totalAmount));

        // Asignar el monto al método actual
        $this->paymentMethods[$this->currentPaymentMethod]['value'] = $remainingAmount;

        // Recalcular balances
        $this->calculatePaymentBalances();
    }

    /**
     * Cerrar modal de pagos
     */
    public function closePaymentModal()
    {
        $this->showPaymentModal = false;

        Log::info('❌ Modal de pagos cerrado sin completar', [
            'quote_id' => $this->editingQuoteId
        ]);
    }

    /**
     * Confirmar pago y procesar factura
     */
    public function confirmPayment()
    {
        // Validaciones
        if (!$this->canProceedToPayment) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe ingresar al menos un pago para proceder.'
            ]);
            return;
        }

        if ($this->remainingBalance < 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'El total pagado excede el valor de la cotización.'
            ]);
            return;
        }

        if ($this->remainingBalance > 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Falta pagar $' . number_format($this->remainingBalance, 0, ',', '.') . ' para completar el total.'
            ]);
            return;
        }

        // Preparar datos de pago
        $paymentData = [];
        foreach ($this->paymentMethods as $key => $method) {
            if (((float) ($method['value'] ?? 0)) > 0) {
                $paymentData[] = [
                    'method' => $method['name'],
                    'value' => (float) $method['value']
                ];
            }
        }

        Log::info('✅ Pago confirmado, procesando factura', [
            'quote_id' => $this->editingQuoteId,
            'payment_methods' => $paymentData,
            'total_paid' => $this->totalPaid
        ]);

        // Cerrar modal de pagos
        $this->showPaymentModal = false;

        // Procesar factura con datos de pago
        $this->processInvoiceAfterPayment($paymentData);
    }

    /**
     * Función para procesar factura DESPUÉS del pago
     * Esta función será llamada desde PaymentQuote o con datos de pago
     */
    public function processInvoiceAfterPayment(array $paymentData = [])
    {
        Log::info('🚀 Iniciando processInvoiceAfterPayment', [
            'editing_quote_id' => $this->editingQuoteId,
            'payment_data' => $paymentData,
            'has_payment_data' => !empty($paymentData),
            'payment_methods_count' => count($paymentData)
        ]);

        if (!$this->isEditing || !$this->editingQuoteId) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Primero debes cargar una cotización para facturarla'
            ]);
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // Cargar la cotización con todas sus relaciones
            $quote = VntQuote::with(['detalles.item', 'customer', 'warehouse'])->findOrFail($this->editingQuoteId);

            // Verificar si la facturación está habilitada para este tenant
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;

            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);

            if (!$hasFacturacionConfig) {
                Log::warning('⚠️ Facturación no configurada para tenant', ['tenant_id' => $tenant->id]);

                // Solo actualizar estado si no hay configuración de facturación
                $quote->update(['status' => 'FACTURADO']);

                DB::connection('tenant')->commit();

                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Cotización marcada como facturada, pero la facturación automática no está configurada.'
                ]);

                // Limpiar y salir del modo edición
                $this->cancelEditing();
                return;
            }

            // Log del inicio del proceso de facturación
            Log::info('🧾 Iniciando proceso de facturación', [
                'quote_id' => $quote->id,
                'consecutive' => $quote->consecutive,
                'tenant_id' => $tenant->id,
                'payment_data_received' => !empty($paymentData)
            ]);

            // Crear instancia del servicio de facturación
            $facturacionService = FacturacionService::forTenant($tenant);

            // Construir datos de la factura usando el nuevo servicio
            // Usar los datos de pago recibidos si están disponibles
            $paymentMethods = $paymentData; // Datos de pago desde PaymentQuote
            $retentions = [];     // Se calcularán automáticamente en buildFromQuote si están habilitadas
            $termDays = 0;        // TODO: Obtener del formulario de facturación

            $invoiceData = InvoiceDataBuilder::buildFromQuote(
                $quote,
                $paymentMethods,
                $retentions,
                $termDays,
                config('facturacion.retentions.auto_calculate', true) // Calcular retenciones automáticamente
            );

            // Calcular retenciones locales para guardar en BD
            $retentionCalculator = new RetentionCalculatorService();
            $customer = $quote->customer;
            $calculatedRetentions = ['retention_fuente' => 0, 'retention_ica' => 0, 'retention_iva' => 0];

            if ($customer && config('facturacion.retentions.auto_calculate', true)) {
                // Obtener datos del cliente para calcular retenciones con bases oficiales 2026
                $regimeDescription = $customer->company && $customer->company->regimen ?
                    ($customer->company->regimen === 'COMUN' ? 'COMMON_REGIME' : 'SPECIAL_REGIME') :
                    'COMMON_REGIME';
                $fiscalResponsability = $customer->company && $customer->company->fiscal_responsibility_id ?
                    (int)$customer->company->fiscal_responsibility_id :
                    ($customer->fiscal_responsibility_id ? (int)$customer->fiscal_responsibility_id : 0);
                $city = $customer->company && $customer->company->city ?
                    $customer->company->city :
                    ($customer->city ?: '');

                $calculatedRetentions = $retentionCalculator->calculateAllRetentions([
                    'regime_description' => $regimeDescription,
                    'fiscal_responsability' => $fiscalResponsability,
                    'city' => $city,
                    'sub_total' => $quote->sub_total
                ]);

                Log::info('💰 Retenciones calculadas para factura (bases 2026)', [
                    'quote_id' => $quote->id,
                    'customer_id' => $customer->id,
                    'regime' => $regimeDescription,
                    'fiscal_responsibility' => $fiscalResponsability,
                    'city' => $city,
                    'sub_total' => $quote->sub_total,
                    'base_fuente' => config('facturacion.retentions.base_amounts.fuente', 524000),
                    'base_ica' => config('facturacion.retentions.base_amounts.ica', 1418800),
                    'calculated_retentions' => $calculatedRetentions
                ]);
            }

            // Enviar factura a la API de Alegra
            Log::info('📡 Enviando factura a API de Alegra', [
                'invoice_data_summary' => [
                    'client_id' => $invoiceData['client']['id'] ?? null,
                    'items_count' => count($invoiceData['items'] ?? []),
                    'payment_form' => $invoiceData['paymentForm'] ?? null
                ]
            ]);

            // Crear la factura en la API
            $apiResponse = $facturacionService->createInvoice($invoiceData);

            if ($apiResponse['success'] && isset($apiResponse['data']['id'])) {
                $invoiceId = $apiResponse['data']['id'];
                $invoiceNumber = $apiResponse['data']['fullNumber'] ?? $invoiceId;

                // Crear el registro local de factura
                $invoice = VntInvoices::create([
                    'consecutive' => $invoiceId, // Usar ID de Alegra como consecutivo temporal
                    'status' => 'SIN EMITIR', // Estado inicial
                    'quoteId' => $quote->id,
                    'warehouseId' => $quote->warehouseId ?: session('warehouse_id', 1),
                    'api_data_id' => $invoiceId,
                    'invoiceNumber' => $invoiceNumber,
                    'partialPayment' => 0.00,
                    'retentionFuente' => $calculatedRetentions['retention_fuente'],
                    'retentionIca' => $calculatedRetentions['retention_ica'],
                    'retentionIva' => $calculatedRetentions['retention_iva'],
                    'creditNote' => null,
                    'orderNumber' => null,
                    'remission' => 0
                ]);

                // 1. CREAR REGISTRO EN BD CON ESTADO INICIAL "SIN EMITIR"
                $invoice->update(['status' => 'SIN EMITIR']);
                $quote->update(['status' => 'FACTURADO']); // Quote ya pasa a FACTURADO al crear la factura

                // 2. CREAR REGISTRO EN LA TABLA DE RELACIONES vnt_invoicesXsales
                VntInvoicesXsales::create([
                    'remissionId' => 0, // Desde cotizador, no hay remisión
                    'quoteId' => $quote->id,
                    'invoiceId' => $invoice->id
                ]);

                Log::info('📄 Factura creada con estado SIN EMITIR, procediendo a emitir (stamp)', [
                    'invoice_local_id' => $invoice->id,
                    'api_data_id' => $invoiceId,
                    'initial_status' => 'SIN EMITIR'
                ]);

                Log::info('🔗 Relación factura-cotización creada en vnt_invoicesXsales', [
                    'quote_id' => $quote->id,
                    'invoice_id' => $invoice->id,
                    'remission_id' => 0
                ]);

                // 2. INTENTAR EMITIR (STAMP) LA FACTURA
                $stampResponse = $facturacionService->stampInvoice($invoiceId);

                if ($stampResponse['success']) {
                    // ✅ STAMP EXITOSO: Actualizar estado a FACTURADO
                    $invoice->update(['status' => 'FACTURADO']);

                    // 3. REGISTRAR PAGO SI HAY DATOS DE PAGO
                    if (!empty($paymentData)) {
                        $this->registerInvoicePayments($invoice, $paymentData, $invoiceId, $facturacionService);
                    }

                    DB::connection('tenant')->commit();

                    Log::info('✅ Factura emitida exitosamente tras stamp', [
                        'quote_id' => $quote->id,
                        'invoice_id_alegra' => $invoiceId,
                        'invoice_number' => $invoiceNumber,
                        'final_status' => 'FACTURADO',
                        'has_payment_data' => !empty($paymentData)
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => "¡Factura creada y emitida exitosamente! Número: {$invoiceNumber}"
                    ]);

                    // Redirigir al cotizador (listado de facturas pendiente de implementar)
                    return redirect()->route('tenant.quoter');
                } else {
                    // ❌ STAMP FALLÓ: Factura queda como SIN EMITIR
                    DB::connection('tenant')->commit(); // Confirmamos la creación, pero sin emitir

                    Log::error('❌ Falló la emisión legal (stamp)', [
                        'invoice_id' => $invoiceId,
                        'invoice_status' => 'SIN EMITIR',
                        'stamp_response' => $stampResponse
                    ]);

                    // Extraer mensaje de error más claro
                    $errorMessage = 'Error desconocido en la emisión';

                    if (isset($stampResponse['data']['message'])) {
                        $errorMessage = $stampResponse['data']['message'];
                    } elseif (isset($stampResponse['message'])) {
                        $errorMessage = $stampResponse['message'];
                    } elseif (isset($stampResponse['error_details']['original_message'])) {
                        $errorMessage = $stampResponse['error_details']['original_message'];
                    }

                    // Limpiar mensaje para mostrar solo la razón específica
                    if (str_contains($errorMessage, 'La factura electrónica de venta no se ha podido emitir porque')) {
                        $errorMessage = str_replace('La factura electrónica de venta no se ha podido emitir porque ', '', $errorMessage);
                    }

                    $this->dispatch('show-toast', [
                        'type' => 'warning',
                        'message' => "Factura creada pero NO emitida. Razón: {$errorMessage}. Puede intentar emitirla desde el listado de facturas."
                    ]);

                    // NO redirigir, mantener en cotizador para mostrar el error
                }
            } else {
                // Error en la API - NO cambiar estado, hacer rollback
                DB::connection('tenant')->rollBack();

                // Obtener detalles del error
                $errorMessage = $apiResponse['message'] ?? 'Error desconocido';
                $statusCode = $apiResponse['status'] ?? 0;

                Log::error('❌ Error en API de facturación', [
                    'quote_id' => $quote->id,
                    'status_code' => $statusCode,
                    'api_error' => $errorMessage,
                    'full_response' => $apiResponse
                ]);

                // Determinar tipo de error para el usuario
                $userMessage = $this->formatApiErrorForUser($errorMessage, $statusCode);

                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => $userMessage
                ]);

                // NO cancelar edición para que el usuario pueda intentar de nuevo
                return;
            }

            // Limpiar y salir del modo edición
            $this->cancelEditing();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ Error en processInvoiceAfterPayment', [
                'quote_id' => $this->editingQuoteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar la facturación: ' . $e->getMessage()
            ]);
        }
    }

    private function formatApiErrorForUser(string $errorMessage, int $statusCode = 0): string
    {
        // Errores específicos de stock (como el que reportas)
        if (str_contains(strtolower($errorMessage), 'stock')) {
            return "❌ Error de stock: " . $errorMessage;
        }

        // Errores de validación 400
        if ($statusCode === 400) {
            return "❌ Error de validación: " . $errorMessage;
        }

        // Errores de autenticación 401
        if ($statusCode === 401) {
            return "❌ Error de autenticación: Verifique la configuración de la API de facturación";
        }

        // Errores de autorización 403
        if ($statusCode === 403) {
            return "❌ Sin permisos: No tiene autorización para crear facturas";
        }

        // Error de conexión
        if ($statusCode >= 500) {
            return "❌ Error del servidor: " . $errorMessage . " (Código: {$statusCode})";
        }

        // Otros errores específicos
        if (str_contains(strtolower($errorMessage), 'cliente') || str_contains(strtolower($errorMessage), 'client')) {
            return "❌ Error de cliente: " . $errorMessage;
        }

        if (str_contains(strtolower($errorMessage), 'item') || str_contains(strtolower($errorMessage), 'producto')) {
            return "❌ Error de producto: " . $errorMessage;
        }

        // Error genérico
        return "❌ Error en facturación: " . $errorMessage;
    }

    public function loadRemissionForEditing($remissionId)
    {
        $this->ensureTenantConnection();
        try {
            $remission = InvRemissions::with(['details.item', 'quote.customer'])->findOrFail($remissionId);

            $this->editingRemissionId = $remissionId;
            $this->isEditingRemission = true;
            $this->isEditing = false;

            // Cargar observaciones
            $this->observaciones = $remission->observations_return;

            // Cargar información del cliente
            if ($remission->quote && $remission->quote->customer) {
                $customer = $remission->quote->customer;
                $this->selectedCustomer = [
                    'id' => $customer->id,
                    'businessName' => $customer->businessName,
                    'firstName' => $customer->firstName,
                    'lastName' => $customer->lastName,
                    'identification' => $customer->identification,
                    'billingEmail' => $customer->billingEmail,
                ];
            }

            // Cargar productos
            $this->quoterItems = [];
            foreach ($remission->details as $detalle) {
                if ($detalle->item) {
                    $this->quoterItems[] = [
                        'id' => $detalle->item->id,
                        'name' => $detalle->item->display_name,
                        'sku' => $detalle->item->sku,
                        'price' => $detalle->value,
                        'price_label' => 'Precio remisión',
                        'quantity' => $detalle->quantity,
                        'description' => $detalle->item->description,
                        'tax' => $detalle->tax ?? 0,
                        'tax_label' => $detalle->tax_label ?? 'N/A',
                    ];
                }
            }

            session(['quoter_items' => $this->quoterItems]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Remisión #' . $remission->consecutive . ' cargada para edición'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en loadRemissionForEditing: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al cargar la remisión: ' . $e->getMessage()
            ]);
        }
    }

    public function updateRemission()
    {
        if (!$this->isEditingRemission || !$this->editingRemissionId) {
            return;
        }

        if (empty($this->quoterItems)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay productos en la remisión'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->beginTransaction();

            $remission = InvRemissions::findOrFail($this->editingRemissionId);

            // Actualizar remisión
            $remission->update([
                'observations_return' => $this->observaciones
            ]);

            // Eliminar detalles existentes
            InvDetailRemissions::where('remissionId', $remission->id)->delete();

            // Crear nuevos detalles
            foreach ($this->quoterItems as $item) {
                InvDetailRemissions::create([
                    'quantity' => $item['quantity'],
                    'tax' => $item['tax'] ?? 0, // ✅ CORREGIDO: Usar tax del item en lugar de hardcodeado
                    'value' => $item['price'],
                    'remissionId' => $remission->id,
                    'itemId' => $item['id'],
                    'invoiceId' => null,
                ]);
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Remisión #' . $remission->consecutive . ' actualizada exitosamente'
            ]);

            return redirect()->route('tenant.remissions');
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error en updateRemission: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar la remisión: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelEditing()
    {
        $redirectRoute = $this->isEditingRemission ? 'tenant.remissions' : 'tenant.quoter';

        // Limpiar estados de edición
        $this->isEditing = false;
        $this->isEditingRemission = false;
        $this->editingQuoteId = null;
        $this->editingRemissionId = null;

        // Limpiar campos
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->observaciones = null;
        $this->showCreateCustomerForm = false;
        $this->showCreateCustomerButton = false;

        // Limpiar cotizador
        $this->clearQuoter();

        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Edición cancelada'
        ]);

        return redirect()->route($redirectRoute);
    }

    /**
     * Obtener el storeId del usuario autenticado desde la BD central (RAP)
     * 
     * @return int
     * @throws \Exception
     */
    private function getUserStoreId()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::error('getUserStoreId: Usuario no autenticado');
                throw new \Exception('Usuario no autenticado');
            }

            if (!$user->contact_id) {
                Log::error('getUserStoreId: Usuario sin contact_id', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                throw new \Exception('Usuario sin contacto asignado');
            }

            // Consultar vnt_contacts en BD central (RAP) usando el modelo
            $contact = VntContact::find($user->contact_id);

            if (!$contact) {
                Log::error('getUserStoreId: Contacto no encontrado en vnt_contacts', [
                    'user_id' => $user->id,
                    'contact_id' => $user->contact_id
                ]);
                throw new \Exception('Contacto no encontrado en vnt_contacts');
            }

            if (!$contact->store) {
                Log::error('getUserStoreId: Contacto sin store asignado', [
                    'user_id' => $user->id,
                    'contact_id' => $user->contact_id
                ]);
                throw new \Exception('Contacto sin bodega (store) asignada');
            }

            Log::info('getUserStoreId: Store obtenido exitosamente', [
                'user_id' => $user->id,
                'contact_id' => $user->contact_id,
                'store_id' => $contact->store
            ]);

            return $contact->store;
        } catch (\Exception $e) {
            Log::error('getUserStoreId: Error al obtener store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Registrar pagos de la factura en BD local y enviar a Alegra
     */
    private function registerInvoicePayments(VntInvoices $invoice, array $paymentData, string $alegraInvoiceId, FacturacionService $facturacionService): void
    {
        try {
            Log::info('💳 Iniciando registro de pagos de factura', [
                'invoice_id' => $invoice->id,
                'alegra_invoice_id' => $alegraInvoiceId,
                'payment_data' => $paymentData
            ]);

            // Obtener métodos de pago desde BD Tenant
            $paymentMethods = DB::connection('tenant')->table('vnt_method_payments')->get()->keyBy('id');

            $totalPayments = 0;
            $paymentRecords = [];

            // Procesar cada método de pago
            foreach ($paymentData as $index => $payment) {
                // Verificar diferentes formatos posibles de datos de pago
                $methodName = $payment['method'] ?? $payment['form'] ?? null;
                $rawAmount = floatval($payment['value'] ?? $payment['valor'] ?? $payment['amount'] ?? 0);

                // Preservar decimales para pagos exactos (NO redondear)
                $amount = round($rawAmount, 2); // Redondear solo a 2 decimales

                Log::info("💳 Procesando pago #{$index}", [
                    'payment_raw' => $payment,
                    'has_method' => isset($payment['method']),
                    'has_value' => isset($payment['value']),
                    'has_valor' => isset($payment['valor']),
                    'method_value' => $payment['method'] ?? null,
                    'raw_amount' => $rawAmount,
                    'rounded_amount' => $amount
                ]);

                if (empty($methodName) || $amount <= 0) {
                    Log::warning("⚠️ Pago #{$index} inválido - saltando", [
                        'method_name' => $methodName,
                        'amount' => $amount,
                        'payment' => $payment
                    ]);
                    continue;
                }

                // Buscar el método por nombre en BD Tenant (en lugar de por ID)
                $paymentMethod = $paymentMethods->firstWhere('name', $methodName);

                if (!$paymentMethod) {
                    Log::warning('⚠️ Método de pago no encontrado en BD Tenant por nombre', [
                        'method_name' => $methodName,
                        'amount' => $amount,
                        'available_methods' => $paymentMethods->pluck('name', 'id')->toArray()
                    ]);
                    continue;
                }

                $methodId = $paymentMethod->id;

                // Registrar en vnt_invoice_payments
                $paymentRecord = VntInvoicePayments::create([
                    'value' => $amount,
                    'invoiceId' => $invoice->id,
                    'methodPaymentId' => $methodId
                ]);

                $paymentRecords[] = $paymentRecord;
                $totalPayments += $amount;

                Log::info('💳 Pago registrado en BD local', [
                    'payment_id' => $paymentRecord->id,
                    'method_id' => $methodId,
                    'method_name' => $paymentMethod->name,
                    'method_bank' => $paymentMethod->bank ?? null,
                    'amount' => $amount
                ]);
            }

            // Preparar datos para envío a Alegra (similar a la función JS)
            if ($totalPayments > 0) {
                // Obtener valor total de la factura local CON DECIMALES
                // Si hay retenciones calculadas en el modal, el monto a pagar es menor
                $modalRetentionsTotal = ($this->retentions['retention_fuente'] ?? 0) +
                                      ($this->retentions['retention_ica'] ?? 0) +
                                      ($this->retentions['retention_iva'] ?? 0);

                // Redondear el total final para evitar discrepancias de centavos
                $invoiceTotal = $modalRetentionsTotal > 0 ?
                    round(floatval($this->totalAmount) - $modalRetentionsTotal, 2) :
                    floatval($this->totalAmount);
                $paymentAmount = floatval($totalPayments);
                $changeAmount = 0;

                // Si el pago es mayor al total de la factura, calcular cambio
                if ($paymentAmount > $invoiceTotal) {
                    $changeAmount = $paymentAmount - $invoiceTotal;
                    $finalPaymentAmount = $invoiceTotal; // Solo enviar el valor exacto de la factura

                    Log::info('💰 Pago excede valor de factura - calculando cambio', [
                        'invoice_total_exact' => $invoiceTotal,
                        'payment_amount' => $paymentAmount,
                        'change_amount' => $changeAmount,
                        'sending_to_alegra_exact' => $finalPaymentAmount
                    ]);
                } else {
                    // Si el pago es igual o menor, enviar el valor exacto de la factura si están pagando el total
                    // o el monto pagado si es parcial
                    if (abs($paymentAmount - $invoiceTotal) < 0.01) {
                        // Están pagando el total exacto (o muy cercano), enviar el total de la factura
                        $finalPaymentAmount = $invoiceTotal;
                    } else {
                        // Pago parcial, enviar lo que pagaron
                        $finalPaymentAmount = $paymentAmount;
                    }

                    Log::info('💰 Pago dentro del rango normal', [
                        'invoice_total_exact' => $invoiceTotal,
                        'payment_amount' => $paymentAmount,
                        'difference' => abs($paymentAmount - $invoiceTotal),
                        'is_full_payment' => abs($paymentAmount - $invoiceTotal) < 0.01,
                        'sending_to_alegra_exact' => $finalPaymentAmount
                    ]);
                }

                // Opcional: También validar con el saldo de Alegra si está disponible
                $invoiceBalance = $facturacionService->getInvoiceBalance($alegraInvoiceId);

                if ($invoiceBalance['success']) {
                    $alegraBalance = $invoiceBalance['balance'];
                    $alegraTotal = $invoiceBalance['total'];

                    Log::info('💰 Validación con saldo de Alegra', [
                        'alegra_total' => $alegraTotal,
                        'alegra_balance' => $alegraBalance,
                        'local_final_amount' => $finalPaymentAmount
                    ]);

                    // SIEMPRE usar el saldo exacto de Alegra para evitar centavos residuales
                    if ($alegraBalance > 0) {
                        $originalAmount = $finalPaymentAmount;
                        $finalPaymentAmount = $alegraBalance;
                        Log::info('💰 Usando saldo EXACTO de Alegra para evitar residuales', [
                            'local_calculation' => $originalAmount,
                            'alegra_balance' => $alegraBalance,
                            'final_payment_amount' => $finalPaymentAmount,
                            'difference' => $originalAmount - $alegraBalance
                        ]);
                    }
                } else {
                    Log::warning('⚠️ No se pudo obtener saldo de Alegra, usando cálculo local', [
                        'error' => $invoiceBalance['error'] ?? 'Error desconocido',
                        'using_local_amount' => $finalPaymentAmount
                    ]);
                }

                // Asegurar que las retenciones del modal estén calculadas
                if (!$this->showRetentions && $this->selectedCustomer) {
                    $this->calculateRetentionsForModal();
                }

                // Usar las retenciones calculadas en el modal (valores correctos)
                // en lugar de las guardadas en la factura (que pueden ser incorrectas)
                // Usar valores redondeados para envío a API
                $invoiceRetentions = [
                    'retention_fuente' => $this->retentions['retention_fuente'] ?? 0,
                    'retention_ica' => $this->retentions['retention_ica'] ?? 0,
                    'retention_iva' => $this->retentions['retention_iva'] ?? 0,
                ];

                Log::info('🔍 DEBUG: Valores antes de construir JSON para Alegra', [
                    'totalAmount' => $this->totalAmount,
                    'subTotal' => $this->subTotal,
                    'showRetentions' => $this->showRetentions,
                    'totalWithRetentions' => $this->totalWithRetentions,
                    'finalPaymentAmount_being_sent' => $finalPaymentAmount,
                    'retentions_from_modal' => $this->retentions,
                    'invoiceRetentions_being_sent' => $invoiceRetentions,
                    'modalRetentionsTotal' => $modalRetentionsTotal,
                    'calculated_total_minus_modal_retentions' => $this->totalAmount - $modalRetentionsTotal
                ]);

                $alegraPaymentData = $this->buildAlegraPaymentData($paymentData, $alegraInvoiceId, $finalPaymentAmount, $paymentMethods, $invoiceRetentions);

                Log::info('📤 Enviando pago a Alegra', [
                    'payment_data' => $alegraPaymentData,
                    'local_total' => $totalPayments,
                    'adjusted_amount' => $finalPaymentAmount
                ]);

                // Enviar a través del servicio de facturación (reutilizar el mismo servicio configurado)
                $paymentResponse = $facturacionService->registerPayment($alegraPaymentData);

                if ($paymentResponse['success'] ?? false) {
                    // Actualizar el status_payment en la tabla vnt_invoices
                    $invoice->update([
                        'status_payment' => 'PAGADO',
                        'api_data_id_pay' => $paymentResponse['data']['id'] ?? null
                    ]);

                    Log::info('✅ Pago registrado exitosamente en Alegra y status actualizado', [
                        'invoice_id' => $invoice->id,
                        'alegra_payment_id' => $paymentResponse['data']['id'] ?? null,
                        'total_amount' => $totalPayments,
                        'status_payment' => 'PAGADO'
                    ]);
                } else {
                    Log::error('❌ Error registrando pago en Alegra', [
                        'error' => $paymentResponse['error'] ?? 'Error desconocido',
                        'response' => $paymentResponse
                    ]);
                }
            }

            Log::info('✅ Registro de pagos completado', [
                'total_payments' => count($paymentRecords),
                'total_amount' => $totalPayments
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error registrando pagos de factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar excepción para no afectar el flujo principal de facturación
        }
    }

    /**
     * Construir datos de pago para Alegra API
     */
    private function buildAlegraPaymentData(array $paymentData, string $alegraInvoiceId, float $totalAmount, $paymentMethods = null, array $retentions = []): array
    {
        Log::info('🔧 Construyendo datos de pago para Alegra', [
            'input_payment_data' => $paymentData,
            'alegra_invoice_id' => $alegraInvoiceId,
            'total_amount' => $totalAmount,
            'retentions' => $retentions
        ]);

        // Obtener primer método de pago para determinar bankAccount
        $firstPayment = reset($paymentData);
        $bankAccount = '1'; // Default

        // Si tenemos los métodos de pago de BD RAP, buscar el bank correcto
        if ($paymentMethods && isset($firstPayment['method'])) {
            $paymentMethod = $paymentMethods->firstWhere('name', $firstPayment['method']);
            if ($paymentMethod && !empty($paymentMethod->bank)) {
                $bankAccount = $paymentMethod->bank;
            }
        }

        // Fallback a datos directos si están disponibles
        $bankAccount = $firstPayment['bank'] ?? $firstPayment['bankAccount'] ?? $bankAccount;

        Log::info('💳 Datos del primer método de pago', [
            'first_payment' => $firstPayment,
            'payment_method_from_db' => $paymentMethods ? $paymentMethods->firstWhere('name', $firstPayment['method'] ?? '') : null,
            'extracted_bank_account' => $bankAccount
        ]);

        // Construir retenciones si existen
        $formattedRetentions = [];
        if (!empty($retentions)) {
            // Retención en la fuente (ID: 14)
            if (($retentions['retention_fuente'] ?? 0) > 0) {
                $formattedRetentions[] = [
                    'id' => config('facturacion.retentions.alegra_ids.fuente', '14'),
                    'amount' => round($retentions['retention_fuente'], 2)
                ];
            }

            // Retención ICA (ID: 11)
            if (($retentions['retention_ica'] ?? 0) > 0) {
                $formattedRetentions[] = [
                    'id' => config('facturacion.retentions.alegra_ids.ica', '11'),
                    'amount' => round($retentions['retention_ica'], 2)
                ];
            }

            // Retención IVA (ID: 12)
            if (($retentions['retention_iva'] ?? 0) > 0) {
                $formattedRetentions[] = [
                    'id' => config('facturacion.retentions.alegra_ids.iva', '12'),
                    'amount' => round($retentions['retention_iva'], 2)
                ];
            }
        }

        // Construir invoice object con retenciones dentro si existen
        $invoiceObject = [
            'id' => $alegraInvoiceId,
            'amount' => $totalAmount
        ];

        // Solo agregar retenciones si hay alguna con valor > 0
        if (!empty($formattedRetentions)) {
            $invoiceObject['retentions'] = $formattedRetentions;
        }

        // Construir payload según formato correcto de Alegra API
        $payloadData = [
            'bankAccount' => [
                'id' => (string)$bankAccount
            ],
            'type' => 'in', // Pago entrante
            'date' => now()->format('Y-m-d'), // Fecha actual
            'invoices' => [$invoiceObject] // Retenciones van dentro del invoice
        ];

        Log::info('📋 JSON FINAL PARA ALEGRA PAYMENTS API', [
            'payload' => $payloadData,
            'total_retentions' => count($formattedRetentions),
            'json_string' => json_encode($payloadData, JSON_PRETTY_PRINT)
        ]);

        return $payloadData;
    }
}
