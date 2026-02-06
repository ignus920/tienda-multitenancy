<div>
    <!-- Modal -->
    <div 
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        @keydown.escape.window="show = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Overlay -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="show = false"
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
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                @click.stop
            >
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Seleccionar Sucursal
                        </h3>
                        <button 
                            type="button"
                            wire:click="close"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Warehouses List -->
                    <div class="mt-4">
                        @if(count($warehouses) > 0)
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @foreach($warehouses as $warehouse)
                                    <button
                                        type="button"
                                        wire:click="selectWarehouse({{ $warehouse['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200
                                            {{ $warehouse['id'] == $currentWarehouseId 
                                                ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20' 
                                                : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700' 
                                            }}
                                            disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-5 w-5 {{ $warehouse['id'] == $currentWarehouseId ? 'text-indigo-600' : 'text-gray-400' }}" 
                                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                                                    </svg>
                                                    <span class="font-medium {{ $warehouse['id'] == $currentWarehouseId ? 'text-indigo-900 dark:text-indigo-100' : 'text-gray-900 dark:text-white' }}">
                                                        {{ $warehouse['name'] }}
                                                    </span>
                                                </div>
                                                @if(!empty($warehouse['address']))
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">
                                                        {{ $warehouse['address'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            @if($warehouse['id'] == $currentWarehouseId)
                                                <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    No hay sucursales disponibles
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="selectWarehouse" class="mt-4">
                        <div class="flex items-center justify-center gap-2 text-indigo-600">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium">Actualizando sucursal...</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button
                        type="button"
                        wire:click="close"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-600 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Listeners for SweetAlert -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('warehouse-updated', (data) => {
                Swal.fire({
                    title: data[0].title,
                    text: data[0].message,
                    icon: data[0].icon,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    background: '#ffffff',
                    color: '#111827',
                    customClass: {
                        popup: 'swal-popup-light',
                        title: 'swal-title-light',
                        content: 'swal-content-light'
                    }
                });
            });

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
