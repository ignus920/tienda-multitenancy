<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntContact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginWarehouseSelector extends Component
{
    public $showModal = false;
    public $warehouses = [];
    public $selectedWarehouseId = null;
    public $loading = false;
    public $redirectRoute = null;

    protected $listeners = ['openLoginWarehouseSelector' => 'open'];

    public function mount()
    {
        // Si hay una marca de sesión, abrir el modal automáticamente
        if (session('needs_warehouse_selection') && Auth::check()) {
            $redirectRoute = session('warehouse_redirect_route', 'tenant.select');
            Log::info('Auto-abriendo modal de sucursales desde mount()', [
                'user_id' => Auth::id(),
                'redirect_route' => $redirectRoute
            ]);
            $this->open($redirectRoute);
        }
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
        $this->loadWarehouses();
        $this->showModal = true;
        
        Log::info('Modal de sucursales abierto', [
            'user_id' => Auth::id(),
            'warehouses_count' => count($this->warehouses),
            'show_modal' => $this->showModal
        ]);
    }

    public function loadWarehouses()
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            $this->warehouses = [];
            return;
        }

        try {
            $user = Auth::user();
            
            if (!$user || !$user->contact_id) {
                Log::warning('Usuario sin contacto asociado en login', ['user_id' => $user?->id]);
                $this->warehouses = [];
                return;
            }

            $contact = VntContact::on('central')
                ->with('warehouse.company')
                ->find($user->contact_id);

            if (!$contact || !$contact->warehouse) {
                Log::warning('Contacto sin sucursal asociada en login', ['contact_id' => $user->contact_id]);
                $this->warehouses = [];
                return;
            }

            $companyId = $contact->warehouse->companyId;

            // Obtener todas las sucursales de la empresa
            $this->warehouses = VntWarehouse::on('central')
                ->where('companyId', $companyId)
                ->where('status', true)
                ->orderBy('name')
                ->get()
                ->toArray();

            // Pre-seleccionar la sucursal actual si existe
            $this->selectedWarehouseId = $contact->warehouseId;

        } catch (\Exception $e) {
            Log::error('Error al cargar sucursales en login', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            $this->warehouses = [];
        }
    }

    public function selectWarehouse($warehouseId)
    {
        $this->selectedWarehouseId = $warehouseId;
    }

    public function confirm()
    {
        if (!$this->selectedWarehouseId) {
            $this->dispatch('warehouse-error', [
                'title' => 'Selección Requerida',
                'message' => 'Por favor, selecciona una sucursal para continuar.',
                'icon' => 'warning'
            ]);
            return;
        }

        $this->loading = true;

        try {
            $user = Auth::user();

            if (!$user || !$user->contact_id) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se encontró un contacto asociado a tu usuario.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Validar que la sucursal existe y pertenece a la empresa del usuario
            $contact = VntContact::on('central')->find($user->contact_id);
            
            if (!$contact || !$contact->warehouse) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se pudo verificar tu sucursal actual.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            $warehouse = VntWarehouse::on('central')
                ->where('id', $this->selectedWarehouseId)
                ->where('companyId', $contact->warehouse->companyId)
                ->where('status', true)
                ->first();

            if (!$warehouse) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'La sucursal seleccionada no es válida.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Actualizar el warehouseId en vnt_contacts
            $contact->warehouseId = $this->selectedWarehouseId;
            $contact->save();

            Log::info('Sucursal seleccionada en login', [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'warehouse_id' => $this->selectedWarehouseId,
                'warehouse_name' => $warehouse->name
            ]);

            // Limpiar la marca de sesión
            session()->forget('needs_warehouse_selection');
            session()->forget('warehouse_redirect_route');

            $this->showModal = false;
            $this->loading = false;

            // Redirigir a la ruta especificada
            $this->redirect(route($this->redirectRoute), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al seleccionar sucursal en login', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'warehouse_id' => $this->selectedWarehouseId
            ]);

            $this->dispatch('warehouse-error', [
                'title' => 'Error',
                'message' => 'No se pudo actualizar la sucursal. Por favor, intenta nuevamente.',
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
