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
use App\Models\Tenant\Items\Category;
use App\Models\Central\VntContact;
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
    public $showObservations = false;
     // Nueva propiedad para la categoría seleccionada
    public $selectedCategory = '';
    public $customerResults = []; // Resultados de búsqueda de clientes

    protected $listeners = [
        'customer-created' => 'onCustomerCreated',
        'vnt-company-saved' => 'onCustomerCreated',
        'customer-updated' => 'onCustomerUpdated',
        'customer-form-cancelled' => 'cancelCreateCustomer'
    ];

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
        
        Log::info('🚀 ProductQuoter montado', [
            'viewType' => $this->viewType,
            'quoteId' => $quoteId,
            'remissionId' => $remissionId,
            'quoterItems_count' => count($this->quoterItems)
        ]);
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
    try{
        $this->ensureTenantConnection();
        $userStoreId = $this->getUserStoreId();

        $query = Items::query()
            ->select(
                'inv_items.*',
                DB::raw('GROUP_CONCAT(DISTINCT inv_store.name SEPARATOR ", ") as store_names'),
                DB::raw('GROUP_CONCAT(DISTINCT inv_store.id SEPARATOR ",") as store_ids')
            )
            ->where('inv_items.status', 1)
            ->with('principalImage')
            ->join('inv_items_store', 'inv_items.id', '=', 'inv_items_store.itemId')
            ->join('inv_store', 'inv_items_store.storeId', '=', 'inv_store.id')
            ->where('inv_store.id', $userStoreId)
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('inv_items.name', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.sku', 'like', '%' . $this->search . '%')
                      ->orWhere('inv_items.description', 'like', '%' . $this->search . '%');
                });
            })
             ->when($this->selectedCategory, function ($query) {
             $query->where('inv_items.categoryId', $this->selectedCategory);
            })
            ->groupBy('inv_items.id')
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
            'productos_bodegas' => $products->pluck('store_names')->toArray()
        ]);
        
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
            $product = Items::find($productId);

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
                'price_label' => $priceLabel,
                'quantity' => 1,
                'description' => $product->description,
            ];
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
        session(['quoter_items' => $this->quoterItems]);
        $this->calculateTotal();
    }

    public function removeFromQuoter($index)
    {
        unset($this->quoterItems[$index]);
        $this->quoterItems = array_values($this->quoterItems); // Reindexar array
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
            $userStoreId = $this->getUserStoreId();
            
            // La tabla vnt_quotes requiere un ID de vnt_contacts en el campo customerId
            // Buscamos el primer contacto asociado a esta empresa
            $contact = VntContacts::whereHas('company', function($q) {
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
                'customerId' => $contact->id, // USAR EL ID DEL CONTACTO AQUÍ
                'warehouseId' => session('warehouse_id', $userStoreId), // Usar userStoreId como fallback
                'userId' => auth()->id(),
                'observations' => $this->observaciones,
                'branchId' => session('branch_id', $userStoreId) // Usar userStoreId como fallback
            ]);

            // Crear los detalles de la cotización
            foreach ($this->quoterItems as $item) {
                VntDetailQuote::create([
                    'quantity' => $item['quantity'],
                    'tax' => 0, // Puedes ajustar esto según tus necesidades
                    'value' => $item['price'],
                    'quoteId' => $quote->id,
                    'itemId' => $item['id'],
                    'description' => $item['name'],
                    'priceList' => $item['price'] // O el ID de la lista de precios si lo tienes
                ]);
            }

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
            ->where(function($query) use ($value) {
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
        $customer = VntCompany::where('identification', $this->customerSearch)->first();

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
            $quote = VntQuote::with('detalles')->findOrFail($quoteId);

            $this->editingQuoteId = $quoteId;
            $this->isEditing = true;

            // Cargar observaciones de la cotización
            $this->observaciones = $quote->observations;

            // Inicializar estado del acordeón de observaciones
            $this->showObservations = !empty($quote->observations);

            // Cargar información del cliente
            if ($quote->customerId) {
                $customer = VntCompany::find($quote->customerId);
                if ($customer) {
                    $this->selectedCustomer = [
                        'id' => $customer->id,
                        'businessName' => $customer->businessName,
                        'firstName' => $customer->firstName,
                        'lastName' => $customer->lastName,
                        'identification' => $customer->identification,
                        'billingEmail' => $customer->billingEmail,
                    ];
                }
            }

            // Cargar productos de la cotización
            $this->quoterItems = [];
            foreach ($quote->detalles as $detalle) {
                $product = Items::find($detalle->itemId);
                if ($product) {
                    $this->quoterItems[] = [
                        'id' => $product->id,
                        'name' => $product->display_name,
                        'sku' => $product->sku,
                        'price' => $detalle->value,
                        'price_label' => 'Precio seleccionado', // Podrías mejorarlo para detectar el label correcto
                        'quantity' => $detalle->quantity,
                        'description' => $product->description,
                    ];
                }
            }

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
        if (!$this->isEditing || !$this->editingQuoteId) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay cotización en modo edición'
            ]);
            return;
        }

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
            $quote = VntQuote::findOrFail($this->editingQuoteId);

            // Actualizar la cotización
            $quote->update([
                'customerId' => $this->selectedCustomer['id'],
                'observations' => $this->observaciones,
            ]);

            // Eliminar detalles existentes
            VntDetailQuote::where('quoteId', $quote->id)->delete();

            // Crear los nuevos detalles
            foreach ($this->quoterItems as $item) {
                VntDetailQuote::create([
                    'quantity' => $item['quantity'],
                    'tax' => 0,
                    'value' => $item['price'],
                    'quoteId' => $quote->id,
                    'itemId' => $item['id'],
                    'description' => $item['name'],
                    'priceList' => $item['price']
                ]);
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cotización #' . $quote->consecutive . ' actualizada exitosamente'
            ]);

            // Opcional: limpiar después de actualizar
            // $this->clearQuoter();
            // $this->isEditing = false;
            // $this->editingQuoteId = null;

        } catch (\Exception $e) {
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

        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->beginTransaction();

            $quote = VntQuote::findOrFail($this->editingQuoteId);

            // Obtener siguiente consecutivo de remisiones
            $lastRemission = InvRemissions::orderBy('consecutive', 'desc')->first();
            $nextConsecutive = $lastRemission ? $lastRemission->consecutive + 1 : 1;

            // Crear Remisión
            $remission = InvRemissions::create([
                'consecutive' => $nextConsecutive,
                'status' => 'REGISTRADO',
                'quoteId' => $quote->id,
                'warehouseId' => $quote->warehouseId ?: session('warehouse_id', 1),
                'methodPaymentId' => 1, // Por defecto efectivo
                'userId' => auth()->id(),
                'deliveryDate' => now()->format('Y-m-d'),
                'expiration' => 0,
                'modify' => 0
            ]);

            // Crear detalles de la remisión
            foreach ($this->quoterItems as $item) {
                InvDetailRemissions::create([
                    'quantity' => $item['quantity'],
                    'tax' => 0,
                    'value' => $item['price'],
                    'remissionId' => $remission->id,
                    'itemId' => $item['id'],
                    'invoiceId' => null,
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
                    'tax' => 0,
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
}