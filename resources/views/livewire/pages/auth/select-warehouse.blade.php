<?php

use Livewire\Volt\Component;
use App\Models\Central\VntContact;
use App\Models\Tenant\Items\InvStore;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    // Display properties
    public string $warehouseName = '';
    public ?string $warehouseAddress = '';
    
    // Store selection
    public array $stores = [];
    public ?int $selectedStoreId = null;
    
    // State management
    public bool $loading = false;
    public ?string $redirectRoute = null;
    
    // Contact context
    private ?int $contactId = null;
    private ?int $warehouseId = null;

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // Obtener la ruta de redirección de la sesión
        $this->redirectRoute = session('warehouse_redirect_route', 'tenant.select');

        // Cargar sucursal y bodegas
        $this->loadWarehouseAndStores();
    }

    private function loadWarehouseAndStores(): void
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->contact_id) {
                Log::warning('Usuario sin contacto asociado', ['user_id' => $user?->id]);
                session()->flash('error', 'No se encontró un contacto asociado a tu usuario.');
                return;
            }

            $this->contactId = $user->contact_id;

            // Setup tenant connection
            $tenantId = session('tenant_id');
            if (!$tenantId) {
                Log::warning('No tenant_id in session', ['user_id' => $user->id]);
                session()->flash('error', 'No se ha seleccionado una empresa. Por favor, selecciona una empresa primero.');
                return;
            }

            $tenant = Tenant::find($tenantId);
            if (!$tenant || !$tenant->is_active) {
                Log::warning('Tenant not found or inactive', ['tenant_id' => $tenantId]);
                session()->flash('error', 'La empresa seleccionada no está disponible.');
                return;
            }

            // Configure tenant connection
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);

            // Query central DB for contact with warehouse relationship
            $contact = VntContact::on('central')
                ->with('warehouse')
                ->find($this->contactId);

            if (!$contact || !$contact->warehouse) {
                Log::warning('Contacto sin sucursal asociada', ['contact_id' => $this->contactId]);
                session()->flash('error', 'No tienes una sucursal asignada. Contacta al administrador.');
                return;
            }

            // Extract warehouse information for display
            $this->warehouseId = $contact->warehouseId;
            $this->warehouseName = $contact->warehouse->name;
            $this->warehouseAddress = $contact->warehouse->address;

            // Log warehouse info for debugging
            Log::info('Loading stores for warehouse', [
                'contact_id' => $this->contactId,
                'warehouse_id' => $this->warehouseId,
                'warehouse_name' => $this->warehouseName
            ]);

            // Query tenant DB for stores matching warehouseId and status=1
            $storesQuery = InvStore::on('tenant')
                ->where('warehouseId', $this->warehouseId)
                ->where('status', 1)
                ->orderBy('name');
            
            // Log the SQL query for debugging
            Log::info('Stores query SQL', [
                'sql' => $storesQuery->toSql(),
                'bindings' => $storesQuery->getBindings()
            ]);
            
            $this->stores = $storesQuery->get()
                ->map(function ($store) {
                    return [
                        'id' => $store->id,
                        'name' => $store->name,
                        'store_manager' => $store->store_manager,
                    ];
                })
                ->toArray();
            
            // Log results
            Log::info('Stores loaded', [
                'warehouse_id' => $this->warehouseId,
                'stores_count' => count($this->stores),
                'stores' => $this->stores
            ]);

            // Check if no active stores are found
            if (empty($this->stores)) {
                Log::warning('No active stores found for warehouse', [
                    'contact_id' => $this->contactId,
                    'warehouse_id' => $this->warehouseId,
                    'warehouse_name' => $this->warehouseName
                ]);
                session()->flash('error', 'Tu sucursal no tiene bodegas activas disponibles. Por favor, contacta al administrador.');
                return;
            }

            // Pre-select current store if contact.store is set
            if ($contact->store) {
                $this->selectedStoreId = $contact->store;
            }

        } catch (\Exception $e) {
            Log::error('Error al cargar sucursal y bodegas', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'No se pudieron cargar las bodegas. Por favor, intenta nuevamente.');
        }
    }

    public function selectStore(int $storeId): void
    {
        $this->selectedStoreId = $storeId;
    }

    private function validateStoreSelection(): array
    {
        // Check if selectedStoreId is set
        if (!$this->selectedStoreId) {
            return [
                'valid' => false,
                'error' => 'Por favor, selecciona una bodega para continuar.',
            ];
        }

        try {
            // Query tenant DB to verify store exists
            $store = InvStore::on('tenant')->find($this->selectedStoreId);

            if (!$store) {
                Log::warning('Store not found during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId,
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no existe o ha sido eliminada. Por favor, selecciona otra bodega.',
                ];
            }

            // Verify store's warehouseId matches contact's warehouse
            if ($store->warehouseId !== $this->warehouseId) {
                Log::warning('Store warehouse mismatch during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId,
                    'store_warehouse_id' => $store->warehouseId,
                    'contact_warehouse_id' => $this->warehouseId,
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no pertenece a tu sucursal.',
                ];
            }

            // Verify store's status equals 1
            if ($store->status !== 1) {
                Log::warning('Inactive store selected during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId,
                    'store_status' => $store->status,
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no está activa.',
                ];
            }

            // All validations passed
            return [
                'valid' => true,
                'error' => null,
                'store' => $store,
            ];

        } catch (\Exception $e) {
            Log::error('Error during store validation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'contact_id' => $this->contactId,
                'store_id' => $this->selectedStoreId,
            ]);
            return [
                'valid' => false,
                'error' => 'Error al validar la bodega. Por favor, intenta nuevamente.',
            ];
        }
    }

    public function confirm()
    {
        // Validate store selection
        $validation = $this->validateStoreSelection();
        
        if (!$validation['valid']) {
            session()->flash('error', $validation['error']);
            return;
        }

        $this->loading = true;

        try {
            $user = Auth::user();

            if (!$user || !$user->contact_id) {
                session()->flash('error', 'No se encontró un contacto asociado a tu usuario.');
                $this->loading = false;
                return;
            }

            // Update vnt_contacts.store field (NOT warehouseId)
            $contact = VntContact::on('central')->find($user->contact_id);
            
            if (!$contact) {
                session()->flash('error', 'No se pudo encontrar tu información de contacto.');
                $this->loading = false;
                return;
            }

            $contact->store = $this->selectedStoreId;
            $contact->save();

            $store = $validation['store'];

            Log::info('Bodega seleccionada en login', [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'warehouse_id' => $this->warehouseId,
                'store_id' => $this->selectedStoreId,
                'store_name' => $store->name,
            ]);

            // Limpiar las marcas de sesión
            session()->forget('needs_warehouse_selection');
            session()->forget('warehouse_redirect_route');

            $this->loading = false;

            // Redirigir a la ruta especificada
            $this->redirect(route($this->redirectRoute), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al seleccionar bodega', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'store_id' => $this->selectedStoreId,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'No se pudo guardar tu selección de bodega. Por favor, intenta nuevamente.');
            $this->loading = false;
        }
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 flex items-center justify-center bg-indigo-600 rounded-2xl shadow-lg mb-4">
                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Selecciona tu Bodega
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Elige la bodega donde trabajarás hoy
            </p>
        </div>

        <!-- Error Message -->
        @if (session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="ml-3 text-sm text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8">
            
            <!-- Warehouse Information (Read-only) -->
            @if($warehouseName)
                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Tu Sucursal Asignada
                            </p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $warehouseName }}
                            </p>
                            @if($warehouseAddress)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $warehouseAddress }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stores List -->
            @if(count($stores) > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto mb-6">
                    @foreach($stores as $store)
                        <button
                            type="button"
                            wire:click="selectStore({{ $store['id'] }})"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-5 py-4 rounded-xl border-2 transition-all duration-200 group
                                {{ $store['id'] == $selectedStoreId 
                                    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 shadow-md' 
                                    : 'border-gray-200 dark:border-gray-600 hover:border-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:shadow-sm' 
                                }}
                                disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-lg flex items-center justify-center
                                            {{ $store['id'] == $selectedStoreId 
                                                ? 'bg-indigo-600' 
                                                : 'bg-gray-100 dark:bg-gray-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50' 
                                            }}
                                            transition-colors duration-200">
                                            <svg class="h-6 w-6 {{ $store['id'] == $selectedStoreId ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-indigo-600' }}" 
                                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold {{ $store['id'] == $selectedStoreId ? 'text-indigo-900 dark:text-indigo-100' : 'text-gray-900 dark:text-white' }}">
                                            {{ $store['name'] }}
                                        </p>
                                        @if(!empty($store['store_manager']))
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $store['store_manager'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($store['id'] == $selectedStoreId)
                                    <div class="flex-shrink-0 ml-4">
                                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="mt-4 text-base text-gray-500 dark:text-gray-400">
                        No hay bodegas disponibles
                    </p>
                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">
                        @if($warehouseName)
                            Tu sucursal no tiene bodegas activas. Contacta al administrador.
                        @else
                            Contacta al administrador para obtener acceso
                        @endif
                    </p>
                </div>
            @endif

            <!-- Footer -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($selectedStoreId)
                        <span class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Bodega seleccionada
                        </span>
                    @else
                        Selecciona una bodega para continuar
                    @endif
                </p>
                <button
                    type="button"
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    @disabled(!$selectedStoreId)
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    <span wire:loading.remove wire:target="confirm">Continuar</span>
                    <span wire:loading wire:target="confirm" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                    <svg wire:loading.remove wire:target="confirm" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
