<div>
    @if($isOpen)
        <!-- Modal Principal -->
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300" x-data="{ show: true }">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-3xl w-full border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh] transition-transform duration-300"
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Encabezado -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Gestión Especial de Inventario: 
                            <span class="text-blue-600 dark:text-blue-400 font-mono">{{ $productCode }}</span>
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $productName }}</p>
                    </div>
                    <button wire:click="close" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors duration-150 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    
                    <!-- Tarjetas de Información de Stock (4 columnas con tooltips Alpine) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <!-- DISPONIBLES -->
                        <div class="relative p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800 text-center" x-data="{ showTooltip: false }">
                            <div class="flex justify-center items-center gap-1">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold uppercase">DISPONIBLES</span>
                                <button type="button" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false" @click="showTooltip = !showTooltip" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="showTooltip" x-transition x-cloak class="absolute z-30 top-full left-1/2 transform -translate-x-1/2 mt-2 w-48 p-2 text-[11px] text-white bg-slate-900 dark:bg-slate-950 rounded-lg shadow-xl text-center leading-tight">
                                Cantidad total disponibles para venta (incluyendo las reservadas)
                            </div>
                            <div class="text-lg font-bold text-slate-800 dark:text-white mt-1">{{ number_format($stock_disponible, 0) }}</div>
                        </div>

                        <!-- RESERVADO -->
                        <div class="relative p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800 text-center" x-data="{ showTooltip: false }">
                            <div class="flex justify-center items-center gap-1">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold uppercase">Reservado</span>
                                <button type="button" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false" @click="showTooltip = !showTooltip" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="showTooltip" x-transition x-cloak class="absolute z-30 top-full left-1/2 transform -translate-x-1/2 mt-2 w-48 p-2 text-[11px] text-white bg-slate-900 dark:bg-slate-950 rounded-lg shadow-xl text-center leading-tight">
                                Total Unidades reservadas por asesores comerciales
                            </div>
                            <div class="text-lg font-bold text-red-500 mt-1">{{ number_format($reserved_stock, 0) }}</div>
                        </div>

                        <!-- CUARENTENA -->
                        <div class="relative p-3 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-900/30 text-center" x-data="{ showTooltip: false }">
                            <div class="flex justify-center items-center gap-1">
                                <span class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase">Cuarentena</span>
                                <button type="button" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false" @click="showTooltip = !showTooltip" class="text-blue-400 hover:text-blue-600 dark:hover:text-blue-350 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="showTooltip" x-transition x-cloak class="absolute z-30 top-full left-1/2 transform -translate-x-1/2 mt-2 w-48 p-2 text-[11px] text-white bg-slate-900 dark:bg-slate-950 rounded-lg shadow-xl text-center leading-tight">
                                Unidades en revisión técnica, no están incluidas en las Disponibles
                            </div>
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($quarantine_stock, 0) }}</div>
                        </div>

                        <!-- EN VITRINA -->
                        <div class="relative p-3 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30 text-center" x-data="{ showTooltip: false }">
                            <div class="flex justify-center items-center gap-1">
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold uppercase">En Vitrina</span>
                                <button type="button" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false" @click="showTooltip = !showTooltip" class="text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-350 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="showTooltip" x-transition x-cloak class="absolute z-30 top-full left-1/2 transform -translate-x-1/2 mt-2 w-48 p-2 text-[11px] text-white bg-slate-900 dark:bg-slate-950 rounded-lg shadow-xl text-center leading-tight">
                                De las cantidades disponibles para venta, estas se encuentran en vitrina
                            </div>
                            <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($showroom_stock, 0) }}</div>
                        </div>
                    </div>

                    @if(!$isReadOnly)
                        <!-- Pestañas (Tabs) de selección de inventario -->
                        <div class="flex border-b border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('activeTab', 'quarantine')" class="flex-1 pb-2.5 text-center text-sm font-bold border-b-2 transition-all {{ $activeTab === 'quarantine' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                                Cuarentena
                            </button>
                            <button type="button" wire:click="$set('activeTab', 'showroom')" class="flex-1 pb-2.5 text-center text-sm font-bold border-b-2 transition-all {{ $activeTab === 'showroom' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                                Vitrina / Exhibición
                            </button>
                        </div>

                        <!-- Formulario de Registro para Administradores -->
                        <form wire:submit.prevent="save" class="space-y-4">
                            
                            <!-- Tipo de Acción -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Movimiento *</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center justify-center p-3 rounded-lg border cursor-pointer transition-all duration-155 select-none text-sm font-semibold
                                        {{ $action_type === 'add' 
                                            ? ($activeTab === 'quarantine' ? 'bg-blue-50 border-blue-400 text-blue-700 dark:bg-blue-950/30 dark:border-blue-700 dark:text-blue-300' : 'bg-indigo-50 border-indigo-400 text-indigo-700 dark:bg-indigo-950/30 dark:border-indigo-700 dark:text-indigo-300')
                                            : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'
                                        }}">
                                        <input type="radio" wire:model.live="action_type" value="add" class="sr-only">
                                        Enviar a {{ $activeTab === 'quarantine' ? 'Cuarentena' : 'Vitrina' }}
                                    </label>
                                    <label class="flex items-center justify-center p-3 rounded-lg border cursor-pointer transition-all duration-155 select-none text-sm font-semibold
                                        {{ $action_type === 'release' 
                                            ? 'bg-emerald-50 border-emerald-400 text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-700 dark:text-emerald-300' 
                                            : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'
                                        }}">
                                        <input type="radio" wire:model.live="action_type" value="release" class="sr-only">
                                        Liberar de {{ $activeTab === 'quarantine' ? 'Cuarentena' : 'Vitrina' }}
                                    </label>
                                </div>
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Cantidad de unidades *</label>
                                <input type="number" wire:model="quantity" min="1" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-slate-850 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm" placeholder="Ej: 5">
                                @error('quantity') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Justificación -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Justificación del movimiento *</label>
                                <textarea wire:model="justification" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-slate-850 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm" placeholder="Escribe el motivo detalladamente..."></textarea>
                                @error('justification') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Acciones finales -->
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                                <button type="button" wire:click="close" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all active:scale-97 flex items-center justify-center gap-2">
                                    <span wire:loading wire:target="save" class="inline-block">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span>Registrar Movimiento</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Vista de Solo Lectura para Comerciales: Sin campos ni botones de registro -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <button type="button" wire:click="close" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-650 rounded-lg text-sm font-semibold text-slate-800 dark:text-white transition-colors">
                                Cerrar
                            </button>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    @endif
</div>
