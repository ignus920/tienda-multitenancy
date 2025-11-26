<?php

namespace App\Livewire\Tenant\Quoter;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Tenant\Quoter\VntDetailQuote;

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
    public $searchingCustomer = false;
    public $showCreateCustomerForm = false;
    public $showCreateCustomerButton = false;
    public $editingQuoteId = null;
    public $isEditing = false;

    protected $listeners = [
        'customer-created' => 'onCustomerCreated',
        'vnt-company-saved' => 'onCustomerCreated'
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 12],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
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

    public function mount($quoteId = null)
    {
        // Obtener viewType de la ruta o usar desktop por defecto
        $this->viewType = request()->route('viewType', 'desktop');
        $this->ensureTenantConnection();

        // Si se pasa un quoteId, estamos editando
        if ($quoteId) {
            $this->loadQuoteForEditing($quoteId);
        } else {
            $this->quoterItems = session('quoter_items', []);
        }

        $this->calculateTotal();
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

        $products = Items::query()
            ->active()
            ->with('principalImage')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $viewName = $this->viewType === 'mobile'
            ? 'livewire.tenant.quoter.components.mobile-product-quoter'
            : 'livewire.tenant.quoter.components.desktop-product-quoter';

        return view($viewName, [
            'products' => $products
        ])->layout('layouts.app');
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

        // Toast más rápido sin información innecesaria
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Agregado al carrito'
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

    public function clearQuoter()
    {
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
            // Obtener el siguiente consecutivo
            $lastQuote = VntQuote::orderBy('consecutive', 'desc')->first();
            $nextConsecutive = $lastQuote ? $lastQuote->consecutive + 1 : 1;

            // Crear la cotización
            $quote = VntQuote::create([
                'consecutive' => $nextConsecutive,
                'status' => 'REGISTRADO',
                'typeQuote' => 'POS',
                'customerId' => $this->selectedCustomer['id'],
                'warehouseId' => session('warehouse_id', 1), // Si tienes warehouse en sesión
                'userId' => auth()->id(),
                'observations' => 'Cliente: ' . ($this->selectedCustomer['businessName'] ?: $this->selectedCustomer['firstName'] . ' ' . $this->selectedCustomer['lastName']),
                'branchId' => session('branch_id', 1) // Si tienes branch en sesión
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









    public function toggleCartModal()
    {
        $this->showCartModal = !$this->showCartModal;
    }

    public function searchCustomer()
    {
        $this->searchingCustomer = true;
        $this->ensureTenantConnection();

        if (empty($this->customerSearch)) {
            $this->searchingCustomer = false;
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Por favor ingrese un NIT o cédula'
            ]);
            return;
        }

        // Simular un pequeño delay para mostrar la animación
        usleep(500000); // 0.5 segundos

        // Buscar cliente por NIT o cédula
        $customer = VntCompany::where('identification', $this->customerSearch)
            ->first();

        if ($customer) {
            // Almacenar solo los datos necesarios en lugar del objeto completo
            $this->selectedCustomer = [
                'id' => $customer->id,
                'businessName' => $customer->businessName,
                'firstName' => $customer->firstName,
                'lastName' => $customer->lastName,
                'identification' => $customer->identification,
                'billingEmail' => $customer->billingEmail,
            ];

            // Determinar el nombre a mostrar
            $customerName = $customer->businessName ?: $customer->firstName . ' ' . $customer->lastName;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cliente encontrado: ' . $customerName
            ]);
        } else {
            $this->selectedCustomer = null;
            $this->showCreateCustomerButton = true;
            $this->dispatch('show-toast', [
                'type' => 'info',
                'message' => 'Cliente no encontrado. Puedes crear uno nuevo'
            ]);
        }

        $this->searchingCustomer = false;
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->showCreateCustomerForm = false;
        $this->showCreateCustomerButton = false;
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
    }

    public function cancelCreateCustomer()
    {
        $this->showCreateCustomerButton = false;
        $this->showCreateCustomerForm = false;
        $this->customerSearch = '';
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

            // Limpiar estados del formulario de creación
            $this->showCreateCustomerForm = false;
            $this->showCreateCustomerButton = false;
            $this->customerSearch = '';

            // Determinar el nombre a mostrar
            $customerName = $customer->businessName ?: $customer->firstName . ' ' . $customer->lastName;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cliente creado y seleccionado: ' . $customerName
            ]);
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

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cantidad aumentada'
            ]);
        }
    }

    public function loadQuoteForEditing($quoteId)
    {
        $this->ensureTenantConnection();

        try {
            $quote = VntQuote::with('detalles')->findOrFail($quoteId);

            $this->editingQuoteId = $quoteId;
            $this->isEditing = true;

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
                'observations' => 'Cliente: ' . ($this->selectedCustomer['businessName'] ?: $this->selectedCustomer['firstName'] . ' ' . $this->selectedCustomer['lastName']),
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

    public function cancelEditing()
    {
        // Limpiar estados de edición
        $this->isEditing = false;
        $this->editingQuoteId = null;

        // Limpiar todos los campos del formulario
        $this->selectedCustomer = null;              // Limpiar cliente seleccionado
        $this->customerSearch = '';                  // Limpiar campo de búsqueda de cliente
        $this->showCreateCustomerForm = false;      // Ocultar formulario de creación
        $this->showCreateCustomerButton = false;    // Ocultar botón de creación

        // Limpiar cotizador
        $this->clearQuoter();

        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Edición cancelada'
        ]);
    }
}