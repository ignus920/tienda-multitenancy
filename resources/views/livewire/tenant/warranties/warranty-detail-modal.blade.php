<div x-data="{ open: @entangle('isOpen'), activeImage: null }">
    <template x-teleport="body">
        <div x-show="open" 
             class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 bg-slate-900 bg-opacity-60 backdrop-blur-sm" 
             style="display: none;"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Modal Panel -->
            <div x-show="open" 
                 class="bg-white dark:bg-slate-800 rounded-3xl overflow-hidden shadow-2xl transform transition-all w-full max-w-4xl max-h-[90vh] flex flex-col"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-white dark:bg-slate-800">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Detalle de Garantía</h3>
                        @if($warranty)
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $warranty->status_color ?? 'bg-gray-200' }}">
                                {{ $warranty->status_label ?? '' }}
                            </span>
                        @endif
                    </div>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50 dark:bg-slate-900/30">
                    @if($warranty)                        <!-- metadata block (inspired by mockup) -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-gray-100 dark:border-slate-700 shadow-sm">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div>
                                    <span class="text-gray-400 font-medium block">Consecutivo</span>
                                    <span class="text-gray-900 dark:text-white font-bold text-sm">{{ $warranty->consecutive }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-medium block">Número OP</span>
                                    <span class="text-gray-950 dark:text-slate-200 font-semibold">{{ $warranty->remission->consecutive ?? $warranty->remission->id ?? 'N/A' }}</span>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <span class="text-gray-400 font-medium block">Cliente</span>
                                    <span class="text-gray-900 dark:text-slate-200 font-semibold truncate block max-w-[150px]">{{ $warranty->remission->quote->customer_name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-medium block">Fecha OP</span>
                                    <span class="text-gray-900 dark:text-slate-200 font-semibold">{{ $warranty->remission->created_at ? \Carbon\Carbon::parse($warranty->remission->created_at)->format('Y-m-d') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Content Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            
                            <!-- Product list (2 cols) -->
                            <div class="lg:col-span-2 space-y-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Productos en Reclamación</h4>
                                
                                @foreach($warranty->items as $item)
                                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm p-4 space-y-3">
                                        <!-- Header Item -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-slate-700 pb-2">
                                            <div class="flex items-center flex-wrap gap-2 flex-1">
                                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-md whitespace-nowrap">
                                                    {{ $item->item->internal_code ?? 'N/A' }}
                                                </span>
                                                <h5 class="text-sm font-bold text-gray-900 dark:text-white break-words">{{ $item->item->name ?? $item->item->description ?? 'Producto sin nombre' }}</h5>
                                            </div>
                                            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-700/30 px-2.5 py-1 rounded-lg self-start sm:self-center">
                                                <span class="text-[9px] text-gray-400 font-bold uppercase">Cant:</span>
                                                <span class="text-xs font-black text-gray-900 dark:text-white">{{ (float)$item->quantity }}</span>
                                            </div>
                                        </div>

                                        <!-- Details grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs bg-slate-50 dark:bg-slate-700/20 p-3 rounded-xl">
                                            <div>
                                                <span class="text-gray-400 font-bold block mb-0.5">Falla reportada:</span>
                                                <p class="text-gray-700 dark:text-gray-300 italic">"{{ $item->failure_description }}"</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-bold block mb-0.5">Solicitud del cliente:</span>
                                                <p class="text-gray-700 dark:text-gray-300 italic">"{{ $item->client_request }}"</p>
                                            </div>
                                        </div>

                                        <!-- Evidences Row (Thumbnails w-12) -->
                                        @if($item->evidences->count() > 0)
                                            <div class="space-y-1">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">Archivos de Evidencia ({{ $item->evidences->count() }}):</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($item->evidences as $evidence)
                                                        @php
                                                            $evidenceUrl = str_starts_with($evidence->file_path, 'http') ? $evidence->file_path : Storage::url($evidence->file_path);
                                                        @endphp
                                                        @if($evidence->file_type === 'video')
                                                            <div class="relative w-12 h-12 bg-indigo-50 rounded-xl overflow-hidden border border-gray-150 dark:border-slate-600 flex items-center justify-center">
                                                                <video src="{{ $evidenceUrl }}" class="w-full h-full object-cover"></video>
                                                                <a href="{{ $evidenceUrl }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-40 hover:bg-opacity-20 transition-all flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 11a1 1 0 112 0 1 1 0 01-2 0zm5 0a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <button type="button" @click="activeImage = '{{ $evidenceUrl }}'" class="block w-12 h-12 rounded-xl overflow-hidden border border-gray-200 dark:border-slate-600 hover:scale-105 transition-transform focus:outline-none">
                                                                <img src="{{ $evidenceUrl }}" class="w-full h-full object-cover" />
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Technical Concepts inside product card -->
                                        <div class="pt-2 space-y-2">
                                            <!-- Concepto de Laboratorio -->
                                            <div>
                                                <span class="text-[9px] font-bold text-red-500 uppercase block">Concepto Laboratorio:</span>
                                                @if($warranty->status === 2)
                                                    <textarea wire:model.blur="labConcepts.{{ $item->id }}" placeholder="Escriba aquí el concepto técnico de Laboratorio..." 
                                                              class="w-full text-xs border-gray-200 dark:border-slate-600 rounded-lg mt-1 bg-white dark:bg-gray-800 focus:ring-red-500"></textarea>
                                                @else
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-0.5 font-medium {{ $item->lab_concept ? 'bg-red-50/50 dark:bg-red-950/20 text-red-700 dark:text-red-300 p-2 rounded-lg' : 'italic text-gray-400' }}">
                                                        {{ $item->lab_concept ?: 'Sin registrar' }}
                                                    </p>
                                                @endif
                                            </div>

                                            <!-- Concepto de Importaciones -->
                                            <div>
                                                <span class="text-[9px] font-bold text-indigo-500 uppercase block">Concepto Importaciones:</span>
                                                @if($warranty->status === 3)
                                                    <textarea wire:model.blur="importsConcepts.{{ $item->id }}" placeholder="Escriba aquí el concepto del fabricante/importación..." 
                                                              class="w-full text-xs border-gray-200 dark:border-slate-600 rounded-lg mt-1 bg-white dark:bg-gray-800 focus:ring-indigo-500"></textarea>
                                                @else
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-0.5 font-medium {{ $item->imports_concept ? 'bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-300 p-2 rounded-lg' : 'italic text-gray-400' }}">
                                                        {{ $item->imports_concept ?: 'Sin registrar' }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Decisions Panel (1 col) -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700 shadow-sm h-fit space-y-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Panel de Decisiones</h4>
                                
                                <!-- Si está en estado 1 (Pendiente Admin) -->
                                @if($warranty->status === 1)
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Nota/Concepto General Admin</label>
                                            <textarea wire:model="adminConcept" placeholder="Escriba notas internas..." 
                                                      class="w-full text-xs border-gray-200 dark:border-slate-600 rounded-lg bg-gray-50 dark:bg-gray-900 focus:ring-indigo-500 h-16"></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Concepto de Gerencia (*)</label>
                                            <select wire:model.live="resolutionType" class="w-full text-xs border-gray-200 dark:border-slate-600 rounded-lg bg-gray-50 dark:bg-gray-900 focus:ring-indigo-500 mb-2">
                                                <option value="">Seleccione una resolución...</option>
                                                <option value="Mal uso (Garantía denegada)">Mal uso (Garantía denegada)</option>
                                                <option value="Defecto de fábrica">Defecto de fábrica</option>
                                                <option value="Generar cobro al cliente">Generar cobro al cliente</option>
                                                <option value="Reponer por garantía">Reponer por garantía</option>
                                            </select>
                                            @error('resolutionType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Alerta Dinámica -->
                                        @if($resolutionType === 'Defecto de fábrica')
                                        <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg p-3 text-xs text-indigo-700 dark:text-indigo-400">
                                            <strong>¡Atención!</strong> Al guardar, se creará un Ticket de Proveedor automático con todas las evidencias adjuntas.
                                        </div>
                                        @elseif($resolutionType === 'Generar cobro al cliente')
                                        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 text-xs text-yellow-700 dark:text-yellow-500">
                                            Se notificará al área encargada para que proceda a realizar el cobro al cliente.
                                        </div>
                                        @elseif($resolutionType === 'Reponer por garantía')
                                        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 text-xs text-emerald-700 dark:text-emerald-500">
                                            Se generará la orden para reposición del artículo o saldo a favor.
                                        </div>
                                        @endif

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Comentario detallado de la solución (*)</label>
                                            <textarea wire:model="adminSolution" placeholder="Escriba los detalles de la solución o mensaje para el cliente..." 
                                                      class="w-full text-xs border-gray-200 dark:border-slate-600 rounded-lg bg-gray-50 dark:bg-gray-900 focus:ring-indigo-500 h-20" required></textarea>
                                        </div>

                                        <div class="pt-2 space-y-2">
                                            <button wire:click="resolveDefinitively" 
                                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
                                                Solución Definitiva
                                            </button>
                                            <button wire:click="sendToLab" 
                                                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 rounded-xl text-xs uppercase shadow transition-all">
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
                                    <div class="space-y-3">
                                        <p class="text-xs text-gray-500">Por favor escribe el concepto técnico de cada ítem a la izquierda antes de guardar.</p>
                                        <button wire:click="saveLabConcept" 
                                                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase shadow transition-all">
                                            Guardar y Retornar a Admin
                                        </button>
                                    </div>

                                <!-- Si está en estado 3 (En Importaciones) -->
                                @elseif($warranty->status === 3)
                                    <div class="space-y-3">
                                        <p class="text-xs text-gray-500">Por favor escribe la nota del fabricante en cada ítem a la izquierda antes de guardar.</p>
                                        <button wire:click="saveImportsConcept" 
                                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase shadow transition-all">
                                            Guardar y Retornar a Admin
                                        </button>
                                    </div>

                                <!-- Si está resuelta (Estado 4) -->
                                @elseif($warranty->status === 4)
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-1.5 text-emerald-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-xs font-bold uppercase">Garantía Resuelta</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 block -mt-2">Resuelto: {{ $warranty->resolved_at ? \Carbon\Carbon::parse($warranty->resolved_at)->format('Y-m-d H:i') : '' }}</span>

                                        <div class="space-y-3 text-xs">
                                            <div>
                                                <span class="font-bold text-gray-400 block mb-0.5">Nota General Admin:</span>
                                                <p class="text-gray-700 dark:text-gray-300 bg-slate-50 dark:bg-slate-900 p-2.5 rounded-xl border border-gray-100 dark:border-slate-700">
                                                    {{ $warranty->admin_concept ?: 'Sin observaciones.' }}
                                                </p>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-400 block mb-0.5">Solución Definitiva:</span>
                                                <p class="text-emerald-800 dark:text-emerald-300 bg-emerald-50/50 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-950 font-semibold">
                                                    {{ $warranty->admin_solution }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 flex justify-end bg-white dark:bg-slate-800">
                    <button wire:click="close" class="bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-slate-700 dark:text-gray-200 px-6 py-2 rounded-xl text-xs font-bold transition-all">
                        Cerrar Ventana
                    </button>
                </div>

            <!-- Visualizador de Imagen Flotante (Lightbox) -->
            <div x-show="activeImage" 
                 class="fixed inset-0 z-[110] bg-black bg-opacity-90 flex items-center justify-center p-4" 
                 style="display: none;"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="activeImage = null"
                 @keydown.escape.window="activeImage = null">
                
                <button type="button" @click="activeImage = null" class="absolute top-4 right-4 text-white bg-slate-800 hover:bg-slate-700 p-2.5 rounded-full transition-colors focus:outline-none shadow-md">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <img :src="activeImage" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl object-contain" @click.stop>
            </div>

        </div>
    </template>
</div>
