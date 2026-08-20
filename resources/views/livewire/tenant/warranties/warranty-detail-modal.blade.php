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
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $warranty->status_color ?? 'bg-gray-200' }} mb-2 inline-block">
                                    {{ $warranty->status_label ?? '' }}
                                </span>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Garantía {{ $warranty->consecutive ?? '' }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    OP Relacionada: #{{ $warranty->remission->consecutive ?? $warranty->remission->id ?? 'N/A' }} | 
                                    Cliente: {{ $warranty->remission->quote->customer_name ?? 'Desconocido' }} 
                                    ({{ $warranty->remission->quote->city ?? 'N/A' }})
                                </p>
                            </div>
                            <button wire:click="close" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Content Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            
                            <!-- Lista de Ítems en Garantía (Ocupa 2 de 3 columnas) -->
                            <div class="lg:col-span-2 space-y-4">
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Productos en Reclamación</h4>
                                
                                @if($warranty)
                                    @foreach($warranty->items as $item)
                                        <div class="p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700 rounded-2xl">
                                            <div class="flex flex-col sm:flex-row justify-between gap-2 mb-3">
                                                <div>
                                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">Cod: {{ $item->item->internal_code ?? 'N/A' }}</span>
                                                    <h5 class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $item->item->name ?? $item->item->description ?? 'Producto sin nombre' }}</h5>
                                                </div>
                                                <div class="text-right sm:text-right">
                                                    <span class="text-xs text-gray-400">Cantidad:</span>
                                                    <span class="text-sm font-black text-gray-800 dark:text-gray-200">{{ number_format($item->quantity, 2) }}</span>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mb-3">
                                                <div>
                                                    <span class="font-bold text-gray-500 block">Falla reportada:</span>
                                                    <p class="text-gray-700 dark:text-gray-300 italic">"{{ $item->failure_description }}"</p>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-500 block">Solicitud del cliente:</span>
                                                    <p class="text-gray-700 dark:text-gray-300 italic">"{{ $item->client_request }}"</p>
                                                </div>
                                            </div>

                                            <!-- Evidencias del ítem -->
                                            @if($item->evidences->count() > 0)
                                                <div class="mb-3">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Archivos de Evidencia:</span>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($item->evidences as $evidence)
                                                            @if($evidence->file_type === 'video')
                                                                <video src="{{ Storage::url($evidence->file_path) }}" controls class="w-24 h-24 object-cover rounded-xl border border-gray-200 dark:border-gray-600"></video>
                                                            @else
                                                                <a href="{{ Storage::url($evidence->file_path) }}" target="_blank" class="block">
                                                                    <img src="{{ Storage::url($evidence->file_path) }}" class="w-24 h-24 object-cover rounded-xl border border-gray-200 dark:border-gray-600 hover:scale-105 transition-transform" />
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Conceptos Técnicos por Ítem -->
                                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 space-y-3">
                                                <!-- Concepto de Laboratorio -->
                                                <div>
                                                    <span class="text-[10px] font-bold text-red-500 uppercase block">Concepto Laboratorio:</span>
                                                    @if($warranty->status === 2)
                                                        <textarea wire:model.blur="labConcepts.{{ $item->id }}" placeholder="Escriba aquí el concepto técnico de Laboratorio..." 
                                                                  class="w-full text-xs border-gray-200 dark:border-gray-600 rounded-lg mt-1 bg-white dark:bg-gray-800 focus:ring-red-500"></textarea>
                                                    @else
                                                        <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 {{ $item->lab_concept ? 'bg-red-50/20 dark:bg-red-900/10 p-2 rounded' : 'italic text-gray-400' }}">
                                                            {{ $item->lab_concept ?: 'Sin registrar' }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <!-- Concepto de Importaciones -->
                                                <div>
                                                    <span class="text-[10px] font-bold text-indigo-500 uppercase block">Concepto Importaciones:</span>
                                                    @if($warranty->status === 3)
                                                        <textarea wire:model.blur="importsConcepts.{{ $item->id }}" placeholder="Escriba aquí el concepto del fabricante/importación..." 
                                                                  class="w-full text-xs border-gray-200 dark:border-gray-600 rounded-lg mt-1 bg-white dark:bg-gray-800 focus:ring-indigo-500"></textarea>
                                                    @else
                                                        <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 {{ $item->imports_concept ? 'bg-indigo-50/20 dark:bg-indigo-900/10 p-2 rounded' : 'italic text-gray-400' }}">
                                                            {{ $item->imports_concept ?: 'Sin registrar' }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Panel de Decisiones (Ocupa 1 de 3 columnas) -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                                @if($warranty)
                                    <!-- Si está en estado 1 (Pendiente Admin) -->
                                    @if($warranty->status === 1)
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Panel de Decisiones</h4>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Nota/Concepto General Admin</label>
                                                <textarea wire:model="adminConcept" placeholder="Escriba notas internas o aclaraciones del caso..." 
                                                          class="w-full text-xs border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 focus:ring-indigo-500"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Resolución / Solución Definitiva (*)</label>
                                                <textarea wire:model="adminSolution" placeholder="Escriba la solución que se le dará al cliente..." 
                                                          class="w-full text-xs border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 focus:ring-indigo-500" required></textarea>
                                            </div>

                                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
                                                <button wire:click="resolveDefinitively" 
                                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                                    Solución Definitiva
                                                </button>
                                                <button wire:click="sendToLab" 
                                                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                                    Remitir a Laboratorio
                                                </button>
                                                <button wire:click="sendToImports" 
                                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                                    Remitir a Importaciones
                                                </button>
                                            </div>
                                        </div>
                                    
                                    <!-- Si está en estado 2 (En Laboratorio) -->
                                    @elseif($warranty->status === 2)
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Inspección de Laboratorio</h4>
                                        <p class="text-xs text-gray-500 mb-4">Por favor escribe el concepto técnico de cada ítem a la izquierda antes de guardar.</p>
                                        <button wire:click="saveLabConcept" 
                                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                            Guardar Concepto y Retornar
                                        </button>

                                    <!-- Si está en estado 3 (En Importaciones) -->
                                    @elseif($warranty->status === 3)
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Evaluación de Importación</h4>
                                        <p class="text-xs text-gray-500 mb-4">Por favor escribe la nota del fabricante en cada ítem a la izquierda antes de guardar.</p>
                                        <button wire:click="saveImportsConcept" 
                                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                            Guardar Concepto y Retornar
                                        </button>

                                    <!-- Si está resuelta -->
                                    @elseif($warranty->status === 4)
                                        <h4 class="text-sm font-bold text-green-600 mb-2 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Garantía Resuelta
                                        </h4>
                                        <p class="text-[10px] text-gray-400 mb-4">Resuelto el: {{ $warranty->resolved_at->format('Y-m-d H:i') }}</p>

                                        <div class="space-y-3 text-xs">
                                            <div>
                                                <span class="font-bold text-gray-500 block">Nota General Admin:</span>
                                                <p class="text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 p-2.5 rounded border border-gray-100 dark:border-gray-700">
                                                    {{ $warranty->admin_concept ?: 'Sin observaciones generales.' }}
                                                </p>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-500 block">Solución Definitiva Final:</span>
                                                <p class="text-gray-900 dark:text-white bg-green-50/20 dark:bg-green-900/10 p-2.5 rounded border border-green-100 dark:border-green-900/50 font-semibold">
                                                    {{ $warranty->admin_solution }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button wire:click="close" class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 px-6 py-2 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors">
                                Cerrar Ventana
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
