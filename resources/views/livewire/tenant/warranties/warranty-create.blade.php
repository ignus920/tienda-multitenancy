<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
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
                                Evidencias ({{ count($tempEvidences[$index] ?? []) }})
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
                    <span class="text-xs font-bold text-gray-500 block mb-1">Archivos Seleccionados ({{ count($tempEvidences[$activeItemIndex]) }})</span>
                    @forelse($tempEvidences[$activeItemIndex] as $fileIndex => $file)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 overflow-hidden">
                                @if(in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', '3gp', 'webm']))
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 text-xs font-bold">
                                        Vid
                                    </div>
                                @else
                                    <img src="{{ $file->temporaryUrl() }}" class="w-10 h-10 object-cover rounded-lg">
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
                        <p class="text-xs italic text-gray-400 text-center py-4">No hay evidencias seleccionadas para este producto.</p>
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
</div>
