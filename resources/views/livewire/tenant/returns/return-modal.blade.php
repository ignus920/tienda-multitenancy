<div x-data="{ open: @entangle('isOpen') }">
    <template x-teleport="body">
        <div x-show="open" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;">
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
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
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    @if($vntReturn)
                    <div class="bg-white dark:bg-gray-800 px-6 py-6 sm:px-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Procesar Devolución #{{ $vntReturn->id }}</h3>
                            <button wire:click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Info General -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 mb-6 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remisión / Consecutivo</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">#{{ $vntReturn->remission->id ?? 'N/A' }} / {{ $vntReturn->remission->consecutive ?? 'S/C' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Actual</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $vntReturn->status_color }} text-white">
                                    {{ $vntReturn->status_label }}
                                </span>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $vntReturn->item->name ?? $vntReturn->item->description ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Formulario Dinámico según Estado -->
                        <div class="space-y-6">
                            <!-- Sección Laboratorio (Visible si estado es 1 o 2) -->
                            @if($vntReturn->status <= 2)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase mb-4">Etapa: Laboratorio</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad Aceptada</label>
                                            <input type="number" wire:model="labQty" step="0.01" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observación Técnica</label>
                                            <textarea wire:model="labObs" rows="3" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evidencias Fotográficas</label>
                                            <input type="file" wire:model="labFiles" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button wire:click="processLab" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none">
                                            Enviar a Bodega
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Sección Bodega (Visible si estado es 2 o 3) -->
                            @if($vntReturn->status == 2 || $vntReturn->status == 3)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <h4 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase mb-4">Etapa: Bodega</h4>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentario de Recepción</label>
                                        <textarea wire:model="warehouseObs" rows="2" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500"></textarea>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button wire:click="processWarehouse" class="bg-green-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-700 transition-colors shadow-lg shadow-green-200 dark:shadow-none">
                                            Enviar a Contabilidad
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Sección Contabilidad (Visible si estado es 3 o 4) -->
                            @if($vntReturn->status >= 3)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <h4 class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase mb-4">Etapa: Contabilidad</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Nota de Crédito</label>
                                            <input type="text" wire:model="ncNumber" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observación Contable</label>
                                            <textarea wire:model="ncObs" rows="2" class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Soporte (PDF/JPG)</label>
                                            <input type="file" wire:model="ncFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button wire:click="processAccounting" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 dark:shadow-none">
                                            Finalizar Devolución
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
