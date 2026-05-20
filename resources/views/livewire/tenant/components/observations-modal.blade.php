<div x-data="{ isOpen: @entangle('isOpen') }" 
     x-show="isOpen" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    <!-- Overlay -->
    <div class="fixed inset-0 transition-opacity" aria-hidden="true" x-on:click="isOpen = false">
        <div class="absolute inset-0 bg-gray-500 dark:bg-slate-900 opacity-75 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Content -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-slate-700">
            
            <!-- Header -->
            <div class="bg-gray-50 dark:bg-slate-700/50 px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $title }} <span class="text-indigo-600 dark:text-indigo-400">#{{ $consecutive ?? $referenceId }}</span>
                    </h3>
                </div>
                <button x-on:click="isOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto text-gray-900 dark:text-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Justificación de Flete -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-red-500 dark:text-red-400 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Justificación de Flete (Manual)
                        </label>
                        <textarea readonly wire:model.defer="observationData.flete_justification" rows="2" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm italic"
                                  placeholder="Sin observaciones de flete."></textarea>
                    </div>

                    <!-- Motivo de Anulación (Solo si existe) -->
                    @if(!empty($observationData['annulment_reason']))
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Motivo de Anulación
                        </label>
                        <textarea readonly wire:model.defer="observationData.annulment_reason" rows="2" 
                                  class="w-full rounded-lg border-amber-200 dark:border-amber-900/30 bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 transition-all text-sm font-medium"
                                  placeholder=""></textarea>
                    </div>
                    @endif

                    <!-- Observaciones del Pedido -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Observaciones del Pedido
                        </label>
                        <textarea readonly wire:model="orderObservations" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm italic"
                                  placeholder="Sin observaciones de pedido."></textarea>
                    </div>

                    <!-- Tipo de Entrega (Info) -->
                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Tipo de entrega</label>
                        <div class="bg-gray-100 dark:bg-slate-700 px-4 py-2.5 rounded-lg text-gray-700 dark:text-gray-300 font-medium border border-gray-200 dark:border-slate-600">
                            {{ $deliveryType }}
                        </div>
                    </div>

                    <!-- Observaciones de entrega -->
                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones de entrega</label>
                        <textarea readonly wire:model="deliveryObservations" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observaciones de impresión -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones de impresión</label>
                        <textarea readonly wire:model.defer="observationData.reprint" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observaciones de entregado -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones de entregado</label>
                        <textarea readonly wire:model.defer="observationData.delivered_obs" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observaciones de imposibilidad -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones de imposibilidad</label>
                        <textarea readonly wire:model.defer="observationData.impossibility_obs" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observaciones de forma de pago -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones de forma de pago</label>
                        <textarea readonly wire:model.defer="observationData.payment_obs" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observaciones items sin saldo -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observaciones items sin saldo</label>
                        <textarea readonly wire:model.defer="observationData.no_stock_obs" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                    <!-- Observación de Cartera -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-2">Observación de Cartera</label>
                        <textarea readonly wire:model.defer="observationData.cartera_justificacion" rows="3" 
                                  class="w-full rounded-lg border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 dark:text-gray-300 transition-all text-sm"
                                  placeholder="N/A"></textarea>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-slate-700/50 px-6 py-4 border-t border-gray-200 dark:border-slate-700 flex justify-end space-x-3">
                <button x-on:click="isOpen = false" type="button" 
                        class="px-6 py-2 bg-indigo-600 text-sm font-bold text-white rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all">
                    Cerrar Vista
                </button>
            </div>
        </div>
    </div>
</div>
