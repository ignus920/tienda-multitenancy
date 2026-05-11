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
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $mode === 'process' ? 'Procesar Devolución' : 'Detalle de Devolución' }} #{{ $vntReturn->id }}
                            </h3>
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

                        <!-- Historial de Observaciones y Evidencias -->
                        <div class="space-y-4 mb-6">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Historial de Proceso</h4>
                            
                            <!-- 1. Comercial -->
                            <div class="bg-gray-50 dark:bg-gray-900/30 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold text-yellow-600 uppercase">1. Comercial</span>
                                    <span class="text-[10px] text-gray-400">{{ $vntReturn->requested_at ? $vntReturn->requested_at->format('d/m/Y H:i') : '' }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $vntReturn->obs_commercial ?? 'Sin observaciones' }}"</p>
                                
                                @php 
                                    $commercialEvidences = $vntReturn->evidences->filter(fn($ev) => str_contains($ev->file_path, 'commercial')); 
                                @endphp

                                @if($commercialEvidences->count() > 0)
                                    <div class="flex gap-2 mt-3 overflow-x-auto pb-2">
                                        @foreach($commercialEvidences as $ev)
                                            <a href="{{ \Storage::url($ev->file_path) }}" target="_blank" class="flex-shrink-0">
                                                <img src="{{ \Storage::url($ev->file_path) }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:scale-105 transition-transform">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- 2. Laboratorio -->
                            @if($vntReturn->obs_lab)
                                <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-indigo-600 uppercase">2. Laboratorio</span>
                                        <span class="text-[10px] text-gray-400">{{ $vntReturn->lab_processed_at ? $vntReturn->lab_processed_at->format('d/m/Y H:i') : '' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Cant. Aceptada:</span>
                                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-400">{{ $vntReturn->lab_qty }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $vntReturn->obs_lab }}"</p>
                                    
                                    @php 
                                        $labEvidences = $vntReturn->evidences->filter(fn($ev) => str_contains($ev->file_path, 'lab')); 
                                    @endphp

                                    @if($labEvidences->count() > 0)
                                        <div class="flex gap-2 mt-3 overflow-x-auto pb-2">
                                            @foreach($labEvidences as $ev)
                                                <a href="{{ \Storage::url($ev->file_path) }}" target="_blank" class="flex-shrink-0">
                                                    <img src="{{ \Storage::url($ev->file_path) }}" class="w-16 h-16 object-cover rounded-lg border border-indigo-200 dark:border-indigo-700 hover:scale-105 transition-transform">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- 3. Bodega -->
                            @if($vntReturn->obs_warehouse)
                                <div class="bg-green-50 dark:bg-green-900/10 rounded-2xl p-4 border border-green-100 dark:border-green-800/50">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-green-600 uppercase">3. Bodega</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $vntReturn->obs_warehouse }}"</p>
                                </div>
                            @endif

                            <!-- 4. Contabilidad -->
                            @if($vntReturn->obs_accounting)
                                <div class="bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-4 border border-blue-100 dark:border-blue-800/50">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-blue-600 uppercase">4. Contabilidad</span>
                                        <span class="text-[10px] text-gray-400">{{ $vntReturn->accounting_processed_at ? $vntReturn->accounting_processed_at->format('d/m/Y H:i') : '' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Nota de Crédito:</span>
                                        <span class="text-sm font-bold text-blue-700 dark:text-blue-400">#{{ $vntReturn->nc_number }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $vntReturn->obs_accounting }}"</p>
                                    
                                    @if($vntReturn->nc_file)
                                        <div class="mt-3">
                                            <a href="{{ asset('storage/'.$vntReturn->nc_file) }}" target="_blank" 
                                               class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-700 uppercase tracking-tighter bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-900 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Ver Soporte NC
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Formulario Dinámico según Estado (Solo en modo PROCESO) -->
                        <div class="space-y-6">
                            @if($mode === 'process')
                                <!-- Sección Laboratorio (Solo si está en estado Comercial o Lab) -->
                                @if($vntReturn->status <= 2)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">L</div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Acción de Laboratorio</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cantidad Aceptada (*)</label>
                                            <input type="number" wire:model="labQty" step="0.01" placeholder="0.00"
                                                   class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm">
                                            @error('labQty') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Observación Técnica (*)</label>
                                            <textarea wire:model="labObs" rows="3" placeholder="Detalle técnico de la revisión..."
                                                      class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm"></textarea>
                                            @error('labObs') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Galería de Evidencias (Fotos)</label>
                                            
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                                                <!-- Imágenes ya cargadas (Historial se muestra arriba, pero si quisieramos editarlas irían aquí) -->
                                                
                                                <!-- Previsualización de imágenes nuevas -->
                                                @foreach($tempImages as $index => $img)
                                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 shadow-sm group">
                                                        <img src="{{ $img['url'] }}" class="w-full h-full object-cover">
                                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                            <button type="button" wire:click="removeTempImage({{ $index }})" class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div class="absolute bottom-0 inset-x-0 bg-indigo-600 text-white text-[8px] font-black uppercase text-center py-0.5">
                                                            Nuevo
                                                        </div>
                                                    </div>
                                                @endforeach

                                                <!-- Botón Agregar -->
                                                <label class="relative aspect-square rounded-xl border-2 border-dashed border-indigo-300 dark:border-indigo-800 bg-indigo-50/50 dark:bg-indigo-900/10 hover:bg-indigo-100 dark:hover:bg-indigo-900/20 cursor-pointer flex flex-col items-center justify-center gap-1 transition-all hover:border-indigo-500 group">
                                                    <input type="file" wire:model.live="labFiles" multiple accept="image/*" class="hidden">
                                                    <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm group-hover:scale-110 transition-transform">
                                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tighter">Agregar</span>
                                                    
                                                    <div wire:loading wire:target="labFiles" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center rounded-xl">
                                                        <div class="w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            <p class="text-[10px] text-gray-500 mt-3 italic flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Formatos: JPG, PNG. Máx 2MB por archivo.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end">
                                        <button wire:click="processLab" wire:loading.attr="disabled"
                                                class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none flex items-center gap-2">
                                            <span wire:loading.remove wire:target="processLab">Completar Revisión y Enviar a Bodega</span>
                                            <span wire:loading wire:target="processLab">Procesando...</span>
                                            <svg wire:loading.remove wire:target="processLab" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Sección Bodega (Visible si está en estado Bodega/3) -->
                            @if($vntReturn->status == 3)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 font-bold text-sm">B</div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Acción de Bodega</h4>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Comentario de Recepción (*)</label>
                                        <textarea wire:model="warehouseObs" rows="3" placeholder="Confirmación de ingreso a bodega..."
                                                  class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm"></textarea>
                                        @error('warehouseObs') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <button wire:click="processWarehouse" 
                                                class="bg-green-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-green-700 transition-all shadow-lg shadow-green-200 dark:shadow-none flex items-center gap-2">
                                            <span>Enviar a Contabilidad</span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Sección Contabilidad (Visible si está en estado Contabilidad/4) -->
                            @if($vntReturn->status == 4)
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">C</div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Acción de Contabilidad</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Número de Nota de Crédito (*)</label>
                                            <input type="text" wire:model="ncNumber" placeholder="NC-0001"
                                                   class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm">
                                            @error('ncNumber') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Soporte (PDF/Imagen)</label>
                                            <input type="file" wire:model="ncFile" 
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            @error('ncFile') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Observaciones Finales (*)</label>
                                            <textarea wire:model="ncObs" rows="3" placeholder="Detalle final de la nota de crédito..."
                                                      class="block w-full border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 text-sm"></textarea>
                                            @error('ncObs') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button wire:click="processAccounting" 
                                                class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 dark:shadow-none flex items-center gap-2">
                                            <span>Finalizar Devolución</span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
