<div>
    <template x-teleport="body">
        <div x-data="{ 
            show: @entangle('isOpen').live 
        }" 
        x-show="show" 
        x-cloak 
        style="display:none;"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

            @if($isOpen)
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>

            <!-- Modal Panel -->
            <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-5xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[95vh]"
                 @click.stop>

                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-indigo-900/5">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase">
                            {{ $productCode }}-{{ $productName }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-4">
                        <button wire:click="saveSettings" 
                                class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-md hover:bg-blue-700 transition-colors uppercase">
                            Actualizar
                        </button>
                        <span class="text-white font-mono text-sm bg-gray-800 px-2 py-0.5 rounded">{{ number_format($weight, 3) }}</span>
                        <button @click="show = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
                    
                    @if($errorEXW || $errorWeight)
                        <div class="flex flex-col gap-3">
                            @if($errorEXW)
                                <div class="bg-red-500 text-white p-3 rounded-lg text-sm font-bold text-center uppercase">
                                    La fuente no tiene precio EXW para hacer el cálculo
                                </div>
                            @endif
                            @if($errorWeight)
                                <div class="bg-red-500 text-white p-3 rounded-lg text-sm font-bold text-center uppercase">
                                    El producto debe tener peso para los cálculos
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Top Inputs Row -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="flex items-center">
                            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-xs font-bold text-gray-500 uppercase">TRM $</span>
                            <input type="number" wire:model.live="trm" class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-r-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white">
                        </div>
                        
                        <div class="flex items-center">
                            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Precio x kilo $</span>
                            <input type="number" wire:model.live="pKilo" class="w-full bg-white dark:bg-gray-900 border-y border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white">
                            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-lg text-xs font-bold text-gray-500 uppercase">usd</span>
                        </div>

                        <div class="flex items-center">
                            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-xs font-bold text-gray-500 uppercase">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/></svg>
                            </span>
                            <input type="text" value="{{ number_format($exw, 2) }}" readonly class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-r-lg px-3 py-2 text-sm outline-none dark:text-white font-bold text-center">
                        </div>

                        <div class="flex items-center">
                            <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Flete aereo $</span>
                            <input type="text" value="{{ number_format($freight, 0, ',', '.') }}" readonly class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-r-lg px-3 py-2 text-sm outline-none dark:text-white font-bold text-center">
                        </div>
                    </div>

                    @if(!$errorEXW && !$errorWeight)
                    <!-- Maritimo Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-4">
                        <div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase">Precio maritimo iva incluido</label>
                                    <div class="flex items-center">
                                        <span class="bg-gray-100 dark:bg-gray-700 px-3 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-xs font-bold text-gray-500">$</span>
                                        <input type="text" value="{{ number_format($maritimePrice, 0, ',', '.') }}" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-r-lg px-3 py-2 text-sm text-center font-bold w-40 dark:text-white" readonly>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-3">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        -Puede cerrar negociación con el precio minimo del ERP si este es mayor al precio marítimo, pero también puede cotizar con este precio marítimo.
                                    </p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        -Se debe pedir anticipo del 75%, como mínimo un 30%
                                    </p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        -Entrega: 75 días a partir del anticipo, puede llegar en 55 días pero no es seguro.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl border border-dashed border-indigo-200 dark:border-indigo-800 self-center">
                            <p class="text-xs text-indigo-700 dark:text-indigo-300 leading-relaxed font-semibold italic">
                                Por favor verifique que la TRM este por lo menos $100 por encima de la TRM del dia. Y que éste precio marítimo sea parecido al precio mínimo del ERP. Si alguno de estos dos datos no esta bien, pida confirmación a Importaciones o gerencia.
                            </p>
                        </div>
                    </div>

                    <hr class="border-t-2 border-indigo-900/20 dark:border-indigo-500/20">

                    <!-- Aereo Section -->
                    <div>
                        <h4 class="text-center text-lg font-bold text-gray-800 dark:text-white mb-6 uppercase tracking-wider">Precio Aéreo Iva Incluido</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Precio unitario, pedido minimo $1'000.000:</span>
                                    <div class="flex items-center">
                                        <span class="bg-gray-50 dark:bg-gray-700 px-2 py-1 border border-r-0 border-gray-200 dark:border-gray-600 rounded-l text-xs">$</span>
                                        <input type="text" value="{{ number_format($unitPrice, 0, ',', '.') }}" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-r px-2 py-1 text-xs font-bold text-center w-32 dark:text-white" readonly>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Si pedido supera 12 millones</span>
                                        <span class="text-[10px] text-blue-600 font-bold uppercase">Descuento del 4%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="bg-gray-50 dark:bg-gray-700 px-2 py-1 border border-r-0 border-gray-200 dark:border-gray-600 rounded-l text-xs">$</span>
                                        <input type="text" value="{{ number_format($discount4, 0, ',', '.') }}" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-r px-2 py-1 text-xs font-bold text-center w-32 dark:text-white" readonly>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Si pedido supera 20 millones</span>
                                        <span class="text-[10px] text-blue-600 font-bold uppercase">Descuento del 6%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="bg-gray-50 dark:bg-gray-700 px-2 py-1 border border-r-0 border-gray-200 dark:border-gray-600 rounded-l text-xs">$</span>
                                        <input type="text" value="{{ number_format($discount6, 0, ',', '.') }}" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-r px-2 py-1 text-xs font-bold text-center w-32 dark:text-white" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    -Entrega en 3 semanas, se puede lograr en 2 semanas, pero no es seguro
                                </p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    -Confirmación de disponibilidad en fabrica se hace una vez el cliente confirme que el precio le sirve.
                                </p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    -Se debe pedir anticipo del 75%, minimo 50%
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button @click="show = false" 
                            class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2 uppercase tracking-wider shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Cerrar
                    </button>
                </div>

            </div>
            @endif

        </div>
    </template>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
    </style>
</div>
