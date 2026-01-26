<?php

use Livewire\Volt\Component;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntContact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    public $warehouses = [];
    public $selectedWarehouseId = null;
    public $loading = false;
    public $redirectRoute = null;

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // Obtener la ruta de redirección de la sesión
        $this->redirectRoute = session('warehouse_redirect_route', 'tenant.select');

        // Cargar sucursales
        $this->loadWarehouses();
    }

    public function loadWarehouses()
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->contact_id) {
                Log::warning('Usuario sin contacto asociado', ['user_id' => $user?->id]);
                $this->warehouses = [];
                return;
            }

            $contact = VntContact::on('central')
                ->with('warehouse.company')
                ->find($user->contact_id);

            if (!$contact || !$contact->warehouse) {
                Log::warning('Contacto sin sucursal asociada', ['contact_id' => $user->contact_id]);
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
            Log::error('Error al cargar sucursales', [
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
            session()->flash('error', 'Por favor, selecciona una sucursal para continuar.');
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

            $contact = VntContact::on('central')->find($user->contact_id);
            
            if (!$contact || !$contact->warehouse) {
                session()->flash('error', 'No se pudo verificar tu sucursal actual.');
                $this->loading = false;
                return;
            }

            $warehouse = VntWarehouse::on('central')
                ->where('id', $this->selectedWarehouseId)
                ->where('companyId', $contact->warehouse->companyId)
                ->where('status', true)
                ->first();

            if (!$warehouse) {
                session()->flash('error', 'La sucursal seleccionada no es válida.');
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

            // Limpiar las marcas de sesión
            session()->forget('needs_warehouse_selection');
            session()->forget('warehouse_redirect_route');

            $this->loading = false;

            // Redirigir a la ruta especificada
            $this->redirect(route($this->redirectRoute), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al seleccionar sucursal', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'warehouse_id' => $this->selectedWarehouseId
            ]);

            session()->flash('error', 'No se pudo actualizar la sucursal. Por favor, intenta nuevamente.');
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Selecciona tu Sucursal
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Elige la sucursal donde trabajarás hoy
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

        <!-- Warehouses List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8">
            @if(count($warehouses) > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto mb-6">
                    @foreach($warehouses as $warehouse)
                        <button
                            type="button"
                            wire:click="selectWarehouse({{ $warehouse['id'] }})"
                            wire:loading.attr="disabled"
                            class="w-full text-left px-5 py-4 rounded-xl border-2 transition-all duration-200 group
                                {{ $warehouse['id'] == $selectedWarehouseId 
                                    ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 shadow-md' 
                                    : 'border-gray-200 dark:border-gray-600 hover:border-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:shadow-sm' 
                                }}
                                disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-lg flex items-center justify-center
                                            {{ $warehouse['id'] == $selectedWarehouseId 
                                                ? 'bg-indigo-600' 
                                                : 'bg-gray-100 dark:bg-gray-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50' 
                                            }}
                                            transition-colors duration-200">
                                            <svg class="h-6 w-6 {{ $warehouse['id'] == $selectedWarehouseId ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-indigo-600' }}" 
                                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold {{ $warehouse['id'] == $selectedWarehouseId ? 'text-indigo-900 dark:text-indigo-100' : 'text-gray-900 dark:text-white' }}">
                                            {{ $warehouse['name'] }}
                                        </p>
                                        @if(!empty($warehouse['address']))
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                                                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $warehouse['address'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($warehouse['id'] == $selectedWarehouseId)
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                    </svg>
                    <p class="mt-4 text-base text-gray-500 dark:text-gray-400">
                        No hay sucursales disponibles
                    </p>
                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">
                        Contacta al administrador para obtener acceso
                    </p>
                </div>
            @endif

            <!-- Footer -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($selectedWarehouseId)
                        <span class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Sucursal seleccionada
                        </span>
                    @else
                        Selecciona una sucursal para continuar
                    @endif
                </p>
                <button
                    type="button"
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    @disabled(!$selectedWarehouseId)
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
