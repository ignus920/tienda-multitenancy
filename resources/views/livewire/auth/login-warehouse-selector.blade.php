<div>
    <!-- Modal -->
    <div 
        x-data="{ 
            show: @entangle('showModal'),
            init() {
                console.log('LoginWarehouseSelector inicializado', {
                    showModal: this.show,
                    needsSelection: @js(session('needs_warehouse_selection')),
                    isAuth: @js(auth()->check())
                });
            }
        }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Overlay (no se puede cerrar haciendo clic fuera) -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
        ></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                @click.stop
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-semibold text-white">
                                Selecciona tu Sucursal
                            </h3>
                            <p class="mt-1 text-sm text-indigo-100">
                                Elige la sucursal donde trabajarás hoy
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    @if(count($warehouses) > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
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
                                            <!-- Icon -->
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
                                            
                                            <!-- Info -->
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
                                        
                                        <!-- Check Icon -->
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
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($selectedWarehouseId)
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 text-green-500 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
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
                            :disabled="!@js($selectedWarehouseId)"
                            class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow-md"
                        >
                            <span wire:loading.remove wire:target="confirm">Continuar</span>
                            <span wire:loading wire:target="confirm" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                            <svg wire:loading.remove wire:target="confirm" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Listeners for SweetAlert -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('warehouse-error', (data) => {
                Swal.fire({
                    title: data[0].title,
                    text: data[0].message,
                    icon: data[0].icon,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#4F46E5',
                    background: '#ffffff',
                    color: '#111827',
                    customClass: {
                        popup: 'swal-popup-light',
                        title: 'swal-title-light',
                        content: 'swal-content-light'
                    }
                });
            });
        });
    </script>
</div>
