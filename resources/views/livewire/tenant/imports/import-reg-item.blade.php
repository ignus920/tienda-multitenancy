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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Porcentaje
                    <span class="text-red-500">*</span></label>
                <input wire:model="percentage" type="number" id="percentage"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Porcentaje">
                @error('percentage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad Minima
                    <span class="text-red-500">*</span></label>
                <input wire:model="cantidad_min" type="number" id="cantidad_min"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Cantidad minima">
                @error('cantidad_min') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Factor
                    <span class="text-red-500">*</span></label>
                <input wire:model="factor" type="number" id="factor"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('factor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proveedor <span class="text-red-500">*</span></label>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ref fabrica
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">$EXW
                <input wire:model="exw" type="number" id="exw"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('exw') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Incr. Fletes
                <input wire:model="freight_increase" type="number" id="freight_increase"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('freight_increase') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Factor PVP1
                <input wire:model="pvp_factor" type="number" id="pvp_factor"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese nombre del producto">
                @error('pvp_factor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Factor PVP Mín
                <input wire:model="pvp_min_factor" type="number" id="pvp_min_factor"
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
            <button type="button" wire:click="cancel"
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