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
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                    
                    <div class="bg-white dark:bg-gray-800 px-6 py-6 sm:px-8">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Registrar Solicitud de Garantía</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    OP #{{ $remission->consecutive ?? '' }} - Cliente: {{ $remission->quote->customer_name ?? '' }} 
                                    ({{ $remission->quote->city ?? 'Ciudad No Definida' }})
                                </p>
                                <p class="text-xs text-gray-400 mt-1">Fecha OP: {{ $remission->created_at ?? 'N/A' }}</p>
                            </div>
                            <button wire:click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10">Selección</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Disponible</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Cant. Reclamo</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detalles del Reclamo</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Evidencias</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($items as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors {{ $item['isSelected'] ? 'bg-indigo-50/20 dark:bg-indigo-900/10' : '' }}">
                                        <!-- Checkbox Selección -->
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model.live="items.{{ $index }}.isSelected"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        </td>
                                        <!-- Producto -->
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['description'] }}</div>
                                            <div class="text-[10px] text-gray-500">Cod: {{ $item['codigo'] }}</div>
                                            <div class="text-[10px] text-gray-400">Total Pedido: {{ number_format($item['original_qty'], 2) }} | Previo Garantía: {{ number_format($item['previously_returned'], 2) }}</div>
                                        </td>
                                        <!-- Disponible -->
                                        <td class="px-4 py-3 text-center text-sm font-bold text-gray-600 dark:text-gray-300">
                                            {{ number_format($item['available_qty'], 2) }}
                                        </td>
                                        <!-- Cant. Reclamo -->
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" wire:model.live="items.{{ $index }}.qty" step="0.01" min="0" max="{{ $item['available_qty'] }}"
                                                   class="w-full text-center border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm font-bold focus:ring-indigo-500 {{ $item['isSelected'] && $item['qty'] > 0 ? 'bg-indigo-50 border-indigo-500 dark:bg-indigo-900/20' : '' }}"
                                                   {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                        </td>
                                        <!-- Detalles (Falla y Solicitud) -->
                                        <td class="px-4 py-3">
                                            <div class="space-y-2">
                                                <input type="text" wire:model.blur="items.{{ $index }}.failure" placeholder="Falla detallada/Concepto..." 
                                                       class="w-full border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs focus:ring-indigo-500"
                                                       {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                                <input type="text" wire:model.blur="items.{{ $index }}.request" placeholder="¿Qué solicita el cliente?..." 
                                                       class="w-full border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs focus:ring-indigo-500"
                                                       {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        <!-- Evidencias -->
                                        <td class="px-4 py-3 text-xs">
                                            <div class="space-y-2">
                                                <input type="file" wire:model="tempEvidences.{{ $index }}" multiple accept="image/*,video/*"
                                                       class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                       {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                                
                                                <!-- Barra de progreso de carga de archivos -->
                                                <div wire:loading wire:target="tempEvidences.{{ $index }}" class="text-[10px] text-indigo-500 font-semibold">
                                                    Subiendo archivos...
                                                </div>

                                                <!-- Previsualización -->
                                                @if(!empty($tempEvidences[$index]))
                                                    <div class="text-[10px] font-bold text-gray-500">Archivos cargados: {{ count($tempEvidences[$index]) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                            Cargando productos de la OP...
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-4">
                            <button wire:click="close" class="px-6 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                                Guardar Garantía
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
