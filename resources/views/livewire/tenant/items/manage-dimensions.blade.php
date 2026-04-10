<form wire:submit.prevent="saveInfoDimensions" class="p-6 space-y-6">
    <div class="space-y-6">
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alto</label>
                <input wire:model="high" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Alto">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Largo</label>
                <input wire:model="long" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Largo">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ancho</label>
                <input wire:model="width" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ancho">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en centimetros</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Voltaje</label>
                <input wire:model="voltage" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Voltaje">
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Potencia</label>
                <input wire:model="power" type="number" step="any"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Potencia">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Peso</label>
                <input wire:model="weight" type="number" step="any"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Peso">
                <span class="text-xs text-gray-500 dark:text-gray-400">Diligencie los valores en kilogramos</span>
            </div>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad por caja</label>
                <input wire:model="quntityxbox" type="number"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: 12">
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
                <span>{{ $dimensions_id ? 'Actualizar' : 'Crear' }}</span>
            </button>
        </div>
    </div>
</form>