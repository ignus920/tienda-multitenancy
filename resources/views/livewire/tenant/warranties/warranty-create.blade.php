<div x-data="{ previewMediaUrl: null, previewMediaType: null }" class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Registrar Solicitud de Garantía</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pedido / OP #{{ $remission->consecutive ?? '' }} - Cliente: {{ $remission->quote->customer_name ?? '' }} 
                    ({{ $remission->quote->city ?? 'Ciudad No Definida' }})
                </p>
                <p class="text-xs text-gray-400 mt-1">Fecha OP: {{ $remission->created_at ?? 'N/A' }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.remissions') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200 px-4 py-2.5 rounded-xl text-xs font-bold uppercase transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Pedidos
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 transition-colors shadow-sm p-6">
        
        @if(!$remission && $hasChatbotData)
        <div class="max-w-6xl mx-auto px-4 py-8 relative z-10">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border-l-4 border-l-amber-500 shadow-xl max-w-2xl mx-auto animate-fade-in relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl"></div>

                <div class="relative z-10 flex gap-6">
                    <div class="shrink-0">
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-2xl flex items-center justify-center border border-amber-200 dark:border-amber-800/50">
                            <svg class="w-7 h-7 text-amber-600 dark:text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">¡OP No Encontrada!</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                            El cliente reportó la referencia: <strong>{{ $chatbotReferenceNumber }}</strong>, pero el sistema no encontró ninguna OP exacta con ese número. Por favor, busca manualmente el número correcto de la OP y vincúlalo para continuar.
                        </p>
                        
                        <div class="mt-4 flex flex-col sm:flex-row gap-3">
                            <div class="relative w-full sm:w-64">
                                <input wire:model="manualSearchConsecutive" type="text" placeholder="Ej: 56975" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-slate-700 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400">
                            </div>
                            <button wire:click="searchManualRemission" class="inline-flex justify-center items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold text-sm shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar OP y Vincular
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($remission)
            <!-- Tabla normal de productos -->
        <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Selección</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Disponible</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Cant. Reclamo</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detalles del Reclamo (Falla y Solicitud del Cliente)</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Evidencias</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors {{ $item['isSelected'] ? 'bg-indigo-50/20 dark:bg-indigo-900/10' : '' }}">
                        <!-- Checkbox Selección -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <input type="checkbox" wire:model.live="items.{{ $index }}.isSelected"
                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 w-5 h-5 cursor-pointer">
                        </td>
                        <!-- Producto -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                <span class="text-gray-500 dark:text-gray-400 font-semibold mr-2">{{ $item['codigo'] }}</span>
                                <span>{{ $item['description'] }}</span>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1">Total Pedido: {{ (float)$item['original_qty'] }} | Previo Garantía: {{ (float)$item['previously_returned'] }}</div>
                        </td>
                        <!-- Disponible -->
                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            {{ (float)$item['available_qty'] }}
                        </td>
                        <!-- Cant. Reclamo -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <input type="number" wire:model.live="items.{{ $index }}.qty" step="0.01" min="0" max="{{ $item['available_qty'] }}"
                                   class="w-full text-center border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm font-bold focus:ring-indigo-500 {{ $item['isSelected'] && $item['qty'] > 0 ? 'bg-indigo-50 border-indigo-500 dark:bg-indigo-900/20' : '' }}"
                                   {{ !$item['isSelected'] ? 'disabled' : '' }}>
                        </td>
                        <!-- Detalles (Falla y Solicitud) -->
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <input type="text" wire:model.blur="items.{{ $index }}.failure" placeholder="Descripción detallada de la falla física..." 
                                       class="w-full border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm focus:ring-indigo-500"
                                       {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                <input type="text" wire:model.blur="items.{{ $index }}.request" placeholder="¿Qué solución está solicitando el cliente?..." 
                                       class="w-full border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm focus:ring-indigo-500"
                                       {{ !$item['isSelected'] ? 'disabled' : '' }}>
                            </div>
                        </td>
                        <!-- Evidencias -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <button type="button" 
                                    wire:click="openEvidenceUploadModal({{ $index }})"
                                    class="px-4 py-2 text-xs font-bold rounded-xl border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors {{ $item['isSelected'] ? '' : 'opacity-50 cursor-not-allowed' }}"
                                    {{ !$item['isSelected'] ? 'disabled' : '' }}>
                                Evidencias ({{ count($tempEvidences[$index] ?? []) + ($hasChatbotData && !empty($chatbotMediaUrls) ? count($chatbotMediaUrls) : 0) }})
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                            Cargando productos de la OP...
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('tenant.remissions') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors flex items-center">
                Cancelar
            </a>
            <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                Guardar Solicitud de Garantía
            </button>
        </div>
        @endif
    </div>

    <!-- Sub-modal para subir evidencias de un producto específico -->
    @if($isEvidenceModalOpen && $activeItemIndex !== null)
    <div class="fixed inset-0 z-[120] overflow-y-auto flex items-center justify-center p-4 bg-slate-900 bg-opacity-65 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 transition-all transform scale-100">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700 mb-4">
                <div>
                    <h4 class="text-md font-bold text-gray-900 dark:text-white">Subir Evidencias de Garantía</h4>
                    <p class="text-xs text-gray-500 mt-1 truncate max-w-sm">{{ $items[$activeItemIndex]['description'] }}</p>
                </div>
                <button type="button" wire:click="closeEvidenceUploadModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-4">
                <!-- Dropzone / Input -->
                <div class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl p-4 text-center hover:border-indigo-400 dark:hover:border-indigo-400 transition-colors relative">
                    <input type="file" wire:model="evidenceFiles" multiple accept="image/*,video/*"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1">
                        <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-bold text-indigo-600 hover:text-indigo-500">Haz clic para subir</span> o arrastra y suelta
                        </p>
                        <p class="text-[10px] text-gray-400">Imágenes o Videos de hasta 15MB</p>
                    </div>
                </div>

                <!-- Progreso de carga -->
                <div wire:loading wire:target="evidenceFiles" class="text-xs text-indigo-500 font-semibold text-center">
                    Cargando archivos al servidor...
                </div>

                <!-- Lista de archivos cargados temporales con previsualización -->
                <div class="max-h-60 overflow-y-auto space-y-2">
                    <span class="text-xs font-bold text-gray-500 block mb-1">Archivos Seleccionados ({{ count($tempEvidences[$activeItemIndex] ?? []) + ($hasChatbotData && !empty($chatbotMediaUrls) ? count($chatbotMediaUrls) : 0) }})</span>
                    
                    @if($hasChatbotData && !empty($chatbotMediaUrls))
                        @foreach($chatbotMediaUrls as $url)
                            @php
                                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
                                $isVideo = in_array($ext, ['mp4', 'mov', 'avi', '3gp', 'webm']);
                            @endphp
                            <div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-900/30 p-2 rounded-xl border border-emerald-100 dark:border-emerald-800/50">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    @if($isVideo)
                                        <div @click="previewMediaUrl = '{{ $url }}'; previewMediaType = 'video'" class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800/40 rounded-lg flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xs font-bold cursor-pointer hover:bg-emerald-200 transition-colors">
                                            Vid
                                        </div>
                                    @else
                                        <img @click="previewMediaUrl = '{{ $url }}'; previewMediaType = 'image'" src="{{ $url }}" class="w-10 h-10 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity">
                                    @endif
                                    <div class="text-xs truncate max-w-[200px] text-emerald-800 dark:text-emerald-400 font-medium flex flex-col">
                                        <span>Archivo de WhatsApp</span>
                                        <span class="text-[9px] opacity-70">Detectado automáticamente</span>
                                    </div>
                                </div>
                                <div class="text-emerald-500 p-1" title="Vinculado desde WhatsApp">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @forelse($tempEvidences[$activeItemIndex] ?? [] as $fileIndex => $file)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 overflow-hidden">
                                @if(in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', '3gp', 'webm']))
                                    <div @click="previewMediaUrl = '{{ $file->temporaryUrl() }}'; previewMediaType = 'video'" class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 text-xs font-bold cursor-pointer hover:bg-indigo-200 transition-colors">
                                        Vid
                                    </div>
                                @else
                                    <img @click="previewMediaUrl = '{{ $file->temporaryUrl() }}'; previewMediaType = 'image'" src="{{ $file->temporaryUrl() }}" class="w-10 h-10 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity">
                                @endif
                                <div class="text-xs truncate max-w-[200px] text-gray-800 dark:text-gray-200">
                                    {{ $file->getClientOriginalName() }}
                                </div>
                            </div>
                            <button type="button" wire:click="removeEvidenceFile({{ $fileIndex }})" class="text-red-500 hover:text-red-600 p-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        @if(!$hasChatbotData || empty($chatbotMediaUrls))
                            <p class="text-xs italic text-gray-400 text-center py-4">No hay evidencias seleccionadas para este producto.</p>
                        @endif
                    @endforelse
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700 mt-4">
                <button type="button" wire:click="closeEvidenceUploadModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl text-xs font-bold transition-all shadow-md">
                    Listo / Aceptar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Lightbox Pop-out -->
    <div x-show="previewMediaUrl" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/95 backdrop-blur-sm transition-opacity">
        <button type="button" @click="previewMediaUrl = null; previewMediaType = null" class="absolute top-6 right-6 text-white hover:text-red-400 bg-white/10 hover:bg-white/20 rounded-full p-2 transition-all">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <div class="relative w-full max-w-5xl max-h-[85vh] flex items-center justify-center p-4">
            <template x-if="previewMediaType === 'video'">
                <video :src="previewMediaUrl" controls autoplay class="max-w-full max-h-full rounded-xl shadow-2xl object-contain border border-white/20"></video>
            </template>
            <template x-if="previewMediaType === 'image'">
                <img :src="previewMediaUrl" class="max-w-full max-h-full rounded-xl shadow-2xl object-contain border border-white/20" />
            </template>
        </div>
    </div>
</div>
