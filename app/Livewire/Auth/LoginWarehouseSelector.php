<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Central\VntContact;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginWarehouseSelector extends Component
{
    public $showModal = false;
    public $stores = [];
    public $selectedStoreId = null;
    public $loading = false;
    public $redirectRoute = null;
    public $warehouseName = '';
    public $warehouseAddress = '';
    
    public $contactId = null;
    public $warehouseId = null;

    protected $listeners = ['openLoginWarehouseSelector' => 'open'];

    public function mount()
    {
        Log::info('🔵 LoginWarehouseSelector::mount() - INICIO', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_tenant_id' => session('tenant_id'),
            'needs_warehouse_selection' => session('needs_warehouse_selection')
        ]);

        // Verify authentication
        if (!Auth::check()) {
            Log::warning('⚠️ LoginWarehouseSelector::mount() - Usuario no autenticado');
            return;
        }

        // Load redirect route from session
        $this->redirectRoute = session('warehouse_redirect_route', 'tenant.select');
        
        Log::info('🔵 LoginWarehouseSelector::mount() - Llamando loadWarehouseAndStores()', [
            'redirect_route' => $this->redirectRoute
        ]);

        // Load warehouse and stores
        $this->loadWarehouseAndStores();
        
        // Si hay una marca de sesión, abrir el modal automáticamente
        if (session('needs_warehouse_selection')) {
            Log::info('🔵 LoginWarehouseSelector::mount() - Abriendo modal automáticamente', [
                'user_id' => Auth::id(),
                'redirect_route' => $this->redirectRoute
            ]);
            $this->showModal = true;
        }

        Log::info('🔵 LoginWarehouseSelector::mount() - FIN', [
            'showModal' => $this->showModal,
            'stores_count' => count($this->stores),
            'selectedStoreId' => $this->selectedStoreId
        ]);
    }

    public function open($redirectRoute = null)
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            Log::warning('Intento de abrir selector de sucursales sin autenticación');
            return;
        }

        Log::info('Abriendo modal de sucursales', [
            'user_id' => Auth::id(),
            'redirect_route' => $redirectRoute
        ]);

        $this->redirectRoute = $redirectRoute ?? 'tenant.select';
        $this->loadWarehouseAndStores();
        $this->showModal = true;
        
        Log::info('Modal de sucursales abierto', [
            'user_id' => Auth::id(),
            'stores_count' => count($this->stores),
            'show_modal' => $this->showModal
        ]);
    }

    private function loadWarehouseAndStores()
    {
        Log::info('🟢 loadWarehouseAndStores() - INICIO');

        // Verify authentication
        if (!Auth::check()) {
            Log::warning('⚠️ loadWarehouseAndStores() - Usuario no autenticado');
            $this->stores = [];
            return;
        }

        try {
            $user = Auth::user();
            
            Log::info('🟢 loadWarehouseAndStores() - Usuario obtenido', [
                'user_id' => $user->id,
                'contact_id' => $user->contact_id
            ]);

            // Check if user has contact_id
            if (!$user || !$user->contact_id) {
                Log::warning('⚠️ loadWarehouseAndStores() - Usuario sin contacto asociado', ['user_id' => $user?->id]);
                $this->stores = [];
                return;
            }

            // Store contact ID for later use
            $this->contactId = $user->contact_id;

            // Setup tenant connection
            $tenantId = session('tenant_id');
            Log::info('🟢 loadWarehouseAndStores() - Tenant ID de sesión', [
                'tenant_id' => $tenantId
            ]);

            if (!$tenantId) {
                Log::warning('⚠️ loadWarehouseAndStores() - No tenant_id in session', ['user_id' => $user->id]);
                $this->stores = [];
                $this->dispatch('warehouse-error', [
                    'title' => 'Empresa No Seleccionada',
                    'message' => 'No se ha seleccionado una empresa. Por favor, selecciona una empresa primero.',
                    'icon' => 'warning'
                ]);
                return;
            }

            $tenant = Tenant::find($tenantId);
            Log::info('🟢 loadWarehouseAndStores() - Tenant encontrado', [
                'tenant_id' => $tenant?->id,
                'tenant_active' => $tenant?->is_active,
                'tenant_database' => $tenant?->tenancy_db_name ?? 'N/A'
            ]);

            if (!$tenant || !$tenant->is_active) {
                Log::warning('⚠️ loadWarehouseAndStores() - Tenant not found or inactive', ['tenant_id' => $tenantId]);
                $this->stores = [];
                $this->dispatch('warehouse-error', [
                    'title' => 'Empresa No Disponible',
                    'message' => 'La empresa seleccionada no está disponible.',
                    'icon' => 'error'
                ]);
                return;
            }

            // Configure tenant connection
            Log::info('🟢 loadWarehouseAndStores() - Configurando conexión del tenant');
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);
            Log::info('🟢 loadWarehouseAndStores() - Conexión del tenant configurada');

            // Query central DB for contact with warehouse relationship
            Log::info('🟢 loadWarehouseAndStores() - Consultando contacto en BD central');
            $contact = VntContact::on('central')
                ->with('warehouse')
                ->find($this->contactId);

            Log::info('🟢 loadWarehouseAndStores() - Contacto obtenido', [
                'contact_found' => $contact !== null,
                'has_warehouse' => $contact?->warehouse !== null,
                'warehouse_id' => $contact?->warehouseId
            ]);

            if (!$contact || !$contact->warehouse) {
                Log::warning('⚠️ loadWarehouseAndStores() - Contacto sin sucursal asociada', ['contact_id' => $this->contactId]);
                $this->stores = [];
                $this->dispatch('warehouse-error', [
                    'title' => 'Sucursal No Asignada',
                    'message' => 'No tienes una sucursal asignada. Por favor, contacta al administrador para que te asigne una sucursal.',
                    'icon' => 'warning'
                ]);
                return;
            }

            // Extract and store warehouse information for display
            $this->warehouseId = $contact->warehouseId;
            $this->warehouseName = $contact->warehouse->name;
            $this->warehouseAddress = $contact->warehouse->address ?? '';

            Log::info('🟢 loadWarehouseAndStores() - Información de warehouse extraída', [
                'warehouse_id' => $this->warehouseId,
                'warehouse_name' => $this->warehouseName,
                'warehouse_address' => $this->warehouseAddress
            ]);

            // Query tenant DB for stores matching warehouseId with status = 1
            Log::info('🟢 loadWarehouseAndStores() - Consultando bodegas en BD tenant', [
                'warehouse_id' => $this->warehouseId,
                'connection' => 'tenant'
            ]);

            $this->stores = InvStore::on('tenant')
                ->where('warehouseId', $this->warehouseId)
                ->where('status', 1)
                ->orderBy('name')
                ->get()
                ->toArray();

            Log::info('🟢 loadWarehouseAndStores() - Bodegas obtenidas', [
                'stores_count' => count($this->stores),
                'stores' => $this->stores
            ]);

            // Check if no active stores are found
            if (empty($this->stores)) {
                Log::warning('⚠️ loadWarehouseAndStores() - No active stores found for warehouse', [
                    'contact_id' => $this->contactId,
                    'warehouse_id' => $this->warehouseId,
                    'warehouse_name' => $this->warehouseName
                ]);
                $this->dispatch('warehouse-error', [
                    'title' => 'Sin Bodegas Disponibles',
                    'message' => 'Tu sucursal no tiene bodegas activas disponibles. Por favor, contacta al administrador.',
                    'icon' => 'warning'
                ]);
                return;
            }

            // Pre-select current store if contact.store is set
            if ($contact->store) {
                $this->selectedStoreId = $contact->store;
                Log::info('🟢 loadWarehouseAndStores() - Bodega pre-seleccionada', [
                    'selected_store_id' => $this->selectedStoreId
                ]);
            }

            Log::info('🟢 loadWarehouseAndStores() - FIN EXITOSO', [
                'user_id' => $user->id,
                'contact_id' => $this->contactId,
                'warehouse_id' => $this->warehouseId,
                'warehouse_name' => $this->warehouseName,
                'stores_count' => count($this->stores),
                'pre_selected_store' => $this->selectedStoreId
            ]);

        } catch (\Exception $e) {
            Log::error('❌ loadWarehouseAndStores() - ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'contact_id' => $this->contactId ?? null,
                'warehouse_id' => $this->warehouseId ?? null
            ]);
            $this->stores = [];
            $this->dispatch('warehouse-error', [
                'title' => 'Error de Carga',
                'message' => 'No se pudieron cargar las bodegas. Por favor, intenta nuevamente.',
                'icon' => 'error'
            ]);
        }
    }

    public function selectStore($storeId)
    {
        Log::info('🟡 selectStore() - Bodega seleccionada', [
            'store_id' => $storeId,
            'previous_selection' => $this->selectedStoreId
        ]);

        $this->selectedStoreId = $storeId;

        Log::info('🟡 selectStore() - Selección actualizada', [
            'new_selected_store_id' => $this->selectedStoreId
        ]);
    }

    /**
     * Validate the selected store
     * 
     * @return array ['valid' => bool, 'error' => string|null]
     */
    private function validateStoreSelection(): array
    {
        Log::info('🟣 validateStoreSelection() - INICIO', [
            'selected_store_id' => $this->selectedStoreId
        ]);

        // Check if selectedStoreId is set
        if (!$this->selectedStoreId) {
            Log::warning('⚠️ validateStoreSelection() - No hay bodega seleccionada');
            return [
                'valid' => false,
                'error' => 'Por favor, selecciona una bodega para continuar.'
            ];
        }

        try {
            Log::info('🟣 validateStoreSelection() - Consultando bodega en BD tenant', [
                'store_id' => $this->selectedStoreId,
                'connection' => 'tenant'
            ]);

            // Query tenant DB to verify store exists
            $store = InvStore::on('tenant')
                ->find($this->selectedStoreId);

            Log::info('🟣 validateStoreSelection() - Resultado de consulta', [
                'store_found' => $store !== null,
                'store_id' => $store?->id,
                'store_name' => $store?->name,
                'store_warehouse_id' => $store?->warehouseId,
                'store_status' => $store?->status
            ]);

            if (!$store) {
                Log::warning('⚠️ validateStoreSelection() - Store not found during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no existe o ha sido eliminada. Por favor, selecciona otra bodega.'
                ];
            }

            // Verify store's warehouseId matches contact's warehouse
            if ($store->warehouseId !== $this->warehouseId) {
                Log::warning('⚠️ validateStoreSelection() - Store warehouse mismatch during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId,
                    'store_warehouse_id' => $store->warehouseId,
                    'contact_warehouse_id' => $this->warehouseId
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no pertenece a tu sucursal.'
                ];
            }

            // Verify store's status equals 1
            if ($store->status !== 1) {
                Log::warning('⚠️ validateStoreSelection() - Inactive store selected during validation', [
                    'user_id' => Auth::id(),
                    'contact_id' => $this->contactId,
                    'store_id' => $this->selectedStoreId,
                    'store_status' => $store->status
                ]);
                return [
                    'valid' => false,
                    'error' => 'La bodega seleccionada no está activa.'
                ];
            }

            // All validations passed
            Log::info('✅ validateStoreSelection() - Validación exitosa', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            return [
                'valid' => true,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('❌ validateStoreSelection() - ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'contact_id' => $this->contactId,
                'store_id' => $this->selectedStoreId
            ]);
            return [
                'valid' => false,
                'error' => 'Error al validar la bodega. Por favor, intenta nuevamente.'
            ];
        }
    }

    public function confirm()
    {
        Log::info('🔴 confirm() - INICIO', [
            'selected_store_id' => $this->selectedStoreId,
            'warehouse_id' => $this->warehouseId,
            'contact_id' => $this->contactId
        ]);

        $this->loading = true;

        try {
            $user = Auth::user();

            Log::info('🔴 confirm() - Usuario obtenido', [
                'user_id' => $user->id,
                'contact_id' => $user->contact_id
            ]);

            if (!$user || !$user->contact_id) {
                Log::warning('⚠️ confirm() - Usuario sin contacto');
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se encontró un contacto asociado a tu usuario.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Setup tenant connection
            $tenantId = session('tenant_id');
            Log::info('🔴 confirm() - Tenant ID de sesión', [
                'tenant_id' => $tenantId
            ]);

            if (!$tenantId) {
                Log::warning('⚠️ confirm() - No tenant_id in session');
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se ha seleccionado una empresa.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            $tenant = Tenant::find($tenantId);
            Log::info('🔴 confirm() - Tenant encontrado', [
                'tenant_id' => $tenant?->id,
                'tenant_active' => $tenant?->is_active,
                'tenant_database' => $tenant?->tenancy_db_name ?? 'N/A'
            ]);

            if (!$tenant || !$tenant->is_active) {
                Log::warning('⚠️ confirm() - Tenant not found or inactive');
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'La empresa seleccionada no está disponible.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Configure tenant connection
            Log::info('🔴 confirm() - Configurando conexión del tenant');
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);
            Log::info('🔴 confirm() - Conexión del tenant configurada');

            // Validate store selection using validateStoreSelection()
            Log::info('🔴 confirm() - Llamando validateStoreSelection()');
            $validation = $this->validateStoreSelection();
            
            Log::info('🔴 confirm() - Resultado de validación', [
                'valid' => $validation['valid'],
                'error' => $validation['error'] ?? null
            ]);

            if (!$validation['valid']) {
                Log::warning('⚠️ confirm() - Validación fallida');
                $this->dispatch('warehouse-error', [
                    'title' => 'Selección Requerida',
                    'message' => $validation['error'],
                    'icon' => 'warning'
                ]);
                $this->loading = false;
                return;
            }

            // Get contact from central DB
            Log::info('🔴 confirm() - Consultando contacto en BD central');
            $contact = VntContact::on('central')->find($user->contact_id);
            
            Log::info('🔴 confirm() - Contacto obtenido', [
                'contact_found' => $contact !== null,
                'contact_id' => $contact?->id,
                'current_store' => $contact?->store
            ]);

            if (!$contact) {
                Log::warning('⚠️ confirm() - Contacto no encontrado');
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se pudo verificar tu contacto.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Get store information for logging
            Log::info('🔴 confirm() - Consultando información de bodega en BD tenant');
            $store = InvStore::on('tenant')->find($this->selectedStoreId);

            Log::info('🔴 confirm() - Bodega obtenida', [
                'store_found' => $store !== null,
                'store_id' => $store?->id,
                'store_name' => $store?->name
            ]);

            // Update vnt_contacts.store field (NOT warehouseId)
            Log::info('🔴 confirm() - Actualizando campo store en contacto', [
                'old_value' => $contact->store,
                'new_value' => $this->selectedStoreId
            ]);

            $contact->store = $this->selectedStoreId;
            $contact->save();

            Log::info('✅ confirm() - Campo store actualizado exitosamente');

            // Log store selection with appropriate context
            Log::info('✅ Bodega seleccionada en login', [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'warehouse_id' => $this->warehouseId,
                'store_id' => $this->selectedStoreId,
                'store_name' => $store->name ?? 'Unknown'
            ]);

            // Clear session flags on success
            Log::info('🔴 confirm() - Limpiando banderas de sesión');
            session()->forget('needs_warehouse_selection');
            session()->forget('warehouse_redirect_route');

            $this->showModal = false;
            $this->loading = false;

            Log::info('✅ confirm() - FIN EXITOSO - Redirigiendo', [
                'redirect_route' => $this->redirectRoute
            ]);

            // Redirect on success
            $this->redirect(route($this->redirectRoute), navigate: true);

        } catch (\Exception $e) {
            // Handle validation and database errors
            Log::error('❌ confirm() - ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'contact_id' => $this->contactId,
                'warehouse_id' => $this->warehouseId,
                'store_id' => $this->selectedStoreId
            ]);

            $this->dispatch('warehouse-error', [
                'title' => 'Error de Actualización',
                'message' => 'No se pudo guardar tu selección de bodega. Por favor, intenta nuevamente.',
                'icon' => 'error'
            ]);

            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.auth.login-warehouse-selector');
    }
}
