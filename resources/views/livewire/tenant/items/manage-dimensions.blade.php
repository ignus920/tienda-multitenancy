<form wire:submit.prevent="saveInfoDimensions" class="p-6 space-y-6">
    <div class="space-y-6">
        <!-- Row 1: Voltaje - Potencia -->
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Voltaje
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Voltaje de operación del artículo.
                        </div>
                    </div>
                </label>
                <input wire:model="voltage" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Voltaje">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Potencia
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Potencia eléctrica del artículo (W).
                        </div>
                    </div>
                </label>
                <input wire:model="power" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Potencia">
            </div>
        </div>

        <!-- Row 2: Alto - Largo -->
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Alto
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Alto del artículo (cm).
                        </div>
                    </div>
                </label>
                <input wire:model="high" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Alto">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Largo
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Largo del artículo (cm).
                        </div>
                    </div>
                </label>
                <input wire:model="long" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Largo">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
        </div>

        <!-- Row 3: Ancho - Peso -->
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Ancho
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Ancho del artículo (cm).
                        </div>
                    </div>
                </label>
                <input wire:model="width" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ancho">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Peso
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Peso neto del artículo (gramos).
                        </div>
                    </div>
                </label>
                <input wire:model="weight" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Peso">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en gramos</span>
            </div>
        </div>

        <!-- Row 4: Cantidad por caja -->
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Cantidad por caja
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Cantidad de unidades por caja máster.
                        </div>
                    </div>
                </label>
                <input wire:model="quntityxbox" type="number"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: 12">
            </div>
        </div>

        <!-- Separador -->
        <hr class="border-t border-gray-200 dark:border-gray-700 my-4">

        <!-- Sección: Valor empaque especial -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center select-none">
                Valor empaque especial
                <!-- Tooltip -->
                <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                        Valor empaque especial (es un nuevo valor que se debe tener en cuenta cuando se cotiza un producto delicado y se deben hacer procesos especiales de empaque).
                    </div>
                </div>
            </h3>

            <!-- Fila de 3 columnas: Cantidad mínima - Valor Mínima - Adicional Unidad -->
            <div class="mb-3 grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                        Cantidad mínima
                        <!-- Tooltip -->
                        <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                            <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                                Valor que define hasta qué cantidad se cobra una mínima de empaque.
                            </div>
                        </div>
                    </label>
                    <input wire:model="min_packing_qty" type="number"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Ej: 5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                        Valor Mínima
                        <!-- Tooltip -->
                        <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                            <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                                Valor de la mínima por concepto de empaque.
                            </div>
                        </div>
                    </label>
                    <input wire:model="min_packing_val" type="number" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Ej: 5000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                        Adicional Unidad
                        <!-- Tooltip -->
                        <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                            <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                                Valor que se incrementa por cada unidad después de la mínima.
                            </div>
                        </div>
                    </label>
                    <input wire:model="add_packing_val" type="number" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Ej: 1000">
                </div>
            </div>
        </div>

        <div class="px-6 pt-4">
            <!-- Mensaje de éxito general -->
            @if (session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
            @endif
        </div>
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="closeItemsModal"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors order-2 sm:order-1">
                Cancelar
            </button>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors order-1 sm:order-2">
                <span>{{ $dimensions_id ? 'Actualizar' : 'Crear' }}</span>
            </button>
        </div>
    </div>
</form>