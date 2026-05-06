<div x-data="{ open: @entangle('isOpen') }">
    <template x-teleport="body">
        <div x-show="open" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 backdrop-blur-sm transition-opacity"></div>

                <!-- Modal Panel -->
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    
                    <div class="bg-white dark:bg-gray-800 px-6 py-6 sm:px-8">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Registrar Devolución Comercial</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pedido #{{ $remission->consecutive ?? '' }} - Cliente: {{ $remission->quote->customer_name ?? '' }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="applyTotalReturn" class="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 px-4 py-2 rounded-xl text-xs font-bold uppercase hover:bg-green-200 transition-colors">
                                    Devolución Total
                                </button>
                                <button wire:click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Observación General (Opcional)</label>
                            <textarea wire:model="generalObservation" rows="2" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm" placeholder="Ej: Mercancía dañada por transporte..."></textarea>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Cant. Original</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Previa. Dev.</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Disponible</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase w-32">A Devolver</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Observación Ítem</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($items as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['description'] }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $item['codigo'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-300 font-bold">
                                            {{ number_format($item['original_qty'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-red-500 font-bold">
                                            {{ number_format($item['previously_returned'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-indigo-600 dark:text-indigo-400 font-black">
                                            {{ number_format($item['available_qty'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" wire:model.live="items.{{ $index }}.return_qty" step="0.01" min="0" max="{{ $item['available_qty'] }}"
                                                   class="w-24 text-center border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm font-bold focus:ring-green-500 focus:border-green-500 {{ $item['return_qty'] > 0 ? 'bg-green-50 border-green-500' : '' }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" wire:model.blur="items.{{ $index }}.observation" placeholder="Motivo..."
                                                   class="w-full border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs focus:ring-indigo-500">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                            Cargando productos o remisión sin ítems...
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 flex justify-end gap-4">
                            <button wire:click="close" class="px-6 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                                Guardar Devolución
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
