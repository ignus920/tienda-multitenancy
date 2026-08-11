<form wire:submit.prevent="saveInfoImport" class="p-6 space-y-6">
    <div class="space-y-6">
        @if (!$itemId)
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código Interno
                    <span class="text-red-500">*</span></label>
                <input wire:model.live.debounce.400ms="internal_code" type="text" id="internal_code"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Código Interno">
                @error('internal_code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror  

                @if($internal_codeExists && !$errors->has('internal_code'))
                    <span class="text-red-500 text-sm">
                        Este código interno ya está registrado
                    </span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción
                    <span class="text-red-500">*</span></label>
                <input wire:model="descripcion" type="text" id="descripcion"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Descripción">
                @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror 
            </div>
        </div>
        @endif
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">Porcentaje
                    <span class="text-red-500 ml-0.5">*</span>
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Porcentaje de arancel o utilidad aplicable.
                        </div>
                    </div>
                </label>
                <input wire:model="percentage" type="number" id="percentage"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Porcentaje">
                @error('percentage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Cantidad Mínima Proveedor
                    <span class="text-red-500 ml-0.5">*</span>
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Cantidad mínima de unidades requerida por el proveedor para realizar pedidos de importación (MOQ).
                        </div>
                    </div>
                </label>
                <input wire:model="cantidad_min" type="number" id="cantidad_min"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Cantidad mínima proveedor">
                @error('cantidad_min') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">Factor
                    <span class="text-red-500 ml-0.5">*</span>
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Factor multiplicador para calcular el precio final.
                        </div>
                    </div>
                </label>
                <input wire:model="factor" type="number" id="factor"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('factor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Proveedor
                    <span class="text-red-500 ml-0.5">*</span>
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Proveedor internacional del producto.
                        </div>
                    </div>
                </label>
                @livewire('selects.generic-select', [
                    'selectedValue' => $data_suppliers['supplierId'],
                    'items' => $this->suppliers,
                    'name' => 'data_suppliers.supplierId',
                    'placeholder' => 'Seleccionar proveedor',
                    'label' => '',
                    'required' => true,
                    'showLabel' => false,
                    'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400',
                    'eventName' => 'supplierSelected',
                    'displayField' => 'firstName',
                    'valueField' => 'id',
                    'searchFields' => ['firstName']
                ], key('supplier-select-' . now()->timestamp))
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Ref fabrica
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Referencia o modelo de fábrica del producto.
                        </div>
                    </div>
                </label>
                <input wire:model="factory_ref" type="text" id="factory_ref"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('factory_ref') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="border-t border-gray-300 my-6"></div>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Factores de Precio y Descuentos</h3>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    $EXW
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Precio EXW (Ex Works) en dólares.
                        </div>
                    </div>
                </label>
                <input wire:model="exw" type="number" step="any" id="exw"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('exw') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Incr. Fletes
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Incremento porcentual estimado por fletes.
                        </div>
                    </div>
                </label>
                <input wire:model="freight_increase" type="number" step="any" id="freight_increase"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('freight_increase') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Factor PVP1
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Factor multiplicador para el precio de venta al público 1.
                        </div>
                    </div>
                </label>
                <input wire:model="pvp_factor" type="number" step="any" id="pvp_factor"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('pvp_factor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center select-none">
                    Factor PVP Mín
                    <!-- Tooltip -->
                    <div x-data="{ show: false }" class="relative inline-block ml-1.5">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case">
                            Factor multiplicador para el precio mínimo de venta.
                        </div>
                    </div>
                </label>
                <input wire:model="pvp_min_factor" type="number" step="any" id="pvp_min_factor"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('pvp_min_factor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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
        <div
            class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="closeItemsModal"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors order-2 sm:order-1">
                Cancelar
            </button>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors order-1 sm:order-2">
                <span>{{ $items_setup_id ? 'Actualizar' : 'Crear' }}</span>
            </button>
        </div>
    </div>
</form>