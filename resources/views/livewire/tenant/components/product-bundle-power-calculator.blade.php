<div>
    <template x-teleport="body">
        <div x-data="{ 
            show: @entangle('isOpen').live 
        }" 
        x-show="show" 
        x-cloak 
        style="display:none;"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4">

            @if($isOpen)
            <!-- Backdrop con desenfoque Premium Reforzado -->
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-lg transition-opacity" 
                 x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
                 @click="show = false"></div>

            <!-- Modal Panel -->
            <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
                 @click.stop>

                <!-- Header Premium Style -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-900">
                    <h3 class="text-base font-bold text-white uppercase tracking-wider">
                        Cálculo potencia de conjunto de productos
                    </h3>
                    <button @click="show = false" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6" 
                     x-data="{}" 
                     x-on:copy-to-clipboard.window="
                        navigator.clipboard.writeText($event.detail.message).then(() => {
                            $dispatch('show-toast', { type: 'success', message: 'Mensaje copiado en el portapapeles' });
                        });
                     ">
                    
                    <div class="flex flex-col gap-6">
                        <!-- Custom Searchable Select (Select2 Style) -->
                        <div class="w-full space-y-2" 
                             x-data="{ 
                                open: false,
                                filter: ''
                             }" 
                             @click.away="open = false">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Producto:</label>
                            
                            <div class="relative">
                                <!-- Trigger (Looks like a Select) -->
                                <button type="button" 
                                        @click="open = !open; if(open) setTimeout(() => $refs.searchInput.focus(), 100)"
                                        class="relative w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg pl-4 pr-10 py-2.5 text-left text-sm focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white cursor-pointer shadow-sm transition-all overflow-hidden whitespace-nowrap overflow-ellipsis">
                                    <span x-text="$wire.selectedProductLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl overflow-hidden">
                                    
                                    <!-- Search Input inside dropdown -->
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                            <input type="text" 
                                                   x-ref="searchInput"
                                                   wire:model.live.debounce.300ms="searchProduct" 
                                                   placeholder="Buscar producto..."
                                                   class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-800 border-none rounded-md text-xs focus:ring-1 focus:ring-indigo-500 outline-none dark:text-white shadow-sm"
                                                   @keydown.escape="open = false">
                                        </div>
                                    </div>

                                    <!-- Options List -->
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar py-1">
                                        @forelse($products as $product)
                                            @php $label = "(" . ($product->sku ?: $product->internal_code) . ") - " . $product->name; @endphp
                                            <button type="button" 
                                                    wire:click="selectProduct({{ $product->id }}, '{{ addslashes($label) }}')"
                                                    @click="open = false"
                                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-indigo-600 hover:text-white transition-colors flex flex-col gap-0.5"
                                                    :class="$wire.selectedProductId == {{ $product->id }} ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300'">
                                                <span class="font-medium">{{ $product->name }}</span>
                                                <span class="text-[10px] opacity-70 uppercase">{{ $product->sku ?: $product->internal_code }}</span>
                                            </button>
                                        @empty
                                            <div class="px-4 py-8 text-center text-gray-400 text-xs font-bold uppercase italic">
                                                No se encontraron resultados
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cantidad y Botón (Fila separada para evitar solapamientos) -->
                        <div class="flex flex-col sm:flex-row items-end gap-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="w-full sm:w-1/3">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5 tracking-wider">Cantidad:</label>
                                <input type="number" wire:model.live="quantity" 
                                       class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white text-center font-bold shadow-sm">
                            </div>
                            <div class="w-full sm:w-2/3">
                                <button wire:click="calculatePower" 
                                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg transition-all shadow-md active:scale-95 uppercase text-sm border-b-4 border-emerald-800">
                                    Calcular Potencia
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cuadro de Resultados -->
                    <div class="mt-2">
                        <textarea readonly
                                  class="w-full min-h-[140px] bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-600 dark:text-gray-300 shadow-inner focus:outline-none resize-none leading-relaxed"
                                  placeholder="ESPERANDO DATOS DE CÁLCULO...">{{ $calculationResult ? $formattedMessage : '' }}</textarea>
                    </div>
                </div>

                <!-- Footer opcional -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button @click="show = false" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold rounded-lg transition-colors uppercase shadow-sm">
                        Cerrar
                    </button>
                </div>

            </div>
            @endif

        </div>
    </template>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
