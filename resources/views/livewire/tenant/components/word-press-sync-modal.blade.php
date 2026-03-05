<div x-data="{ syncActive: @entangle('isOpen').live, viewMode: 'sync' }"
     x-init="$watch('syncActive', val => { if(!val) viewMode = 'sync' })">

    <!-- UN SOLO MODAL -->
    <div x-show="syncActive"
         x-cloak
         wire:ignore.self
         wire:key="modal-wp-unified-v4"
         class="fixed inset-0 z-[60] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Overlay -->
            <div x-show="syncActive"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" 
                 @click="syncActive = false"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Panel Principal -->
            <div x-show="syncActive"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-gray-700"
                 @click.stop>

                <!-- ========== HEADER (cambia según la vista) ========== -->
                <div x-show="viewMode === 'sync'" class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                            <i class="fab fa-wordpress fa-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Sincronización WordPress (Fervicom)
                        </h3>
                    </div>
                    <button @click="syncActive = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div x-show="viewMode === 'comparison'" class="bg-indigo-600 px-6 py-4 border-b border-indigo-700 flex justify-between items-center">
                    <div class="flex items-center gap-3 text-white">
                        <i class="fas fa-balance-scale fa-lg"></i>
                        <h3 class="text-xl font-bold uppercase tracking-tight">Comparación ERP vs WordPress</h3>
                    </div>
                    <button @click="syncActive = false" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- ========== VISTA 1: SINCRONIZACIÓN ========== -->
                <div x-show="viewMode === 'sync'" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="p-6">
                        @if($message)
                            <div class="mb-6 p-4 rounded-xl flex items-center gap-3 {{ $statusType === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-100 dark:border-green-900/30' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30' }}">
                                <span class="text-sm font-medium">{{ $message }}</span>
                            </div>
                        @endif

                        <!-- Stats -->
                        <div class="mb-8 grid grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 text-center shadow-sm">
                                <span class="block text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ collect($localImages)->where('type', 'GALERIA')->count() }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Imágenes ERP</span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 text-center shadow-sm">
                                <span class="block text-2xl font-black text-emerald-500">{{ $wpProduct ? count($wpProduct['images'] ?? []) : 0 }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Imágenes WP</span>
                            </div>
                            <div class="bg-indigo-600 p-4 rounded-2xl text-center shadow-lg shadow-indigo-200 dark:shadow-none">
                                <span class="block text-2xl font-black text-white">{{ collect($localImages)->where('type', 'GALERIA')->where('sync_to_wp', true)->count() }}</span>
                                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest">Seleccionadas Web</span>
                            </div>
                        </div>

                        <!-- Botón cambio de vista -->
                        <div class="mb-6 flex justify-center">
                            <button @click="viewMode = 'comparison'" 
                                    class="px-6 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 rounded-xl text-sm font-bold border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all flex items-center gap-2 shadow-sm">
                                <i class="fas fa-layer-group"></i>
                                Ver Comparación Detallada de Inventario
                            </button>
                        </div>

                        <!-- Grid Imágenes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fas fa-image"></i> Imagen Principal (ERP vs WP)
                                </h4>
                                <div class="flex items-center gap-4 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                                    <div class="flex-1 text-center">
                                        @php $mainImg = collect($localImages)->where('type', 'PRINCIPAL')->first(); @endphp
                                        <div class="aspect-square bg-white dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-1">
                                            @if($mainImg)<img src="{{ $mainImg->getImageUrl() }}" class="w-full h-full object-contain">@else <i class="fas fa-image fa-2x text-gray-200 mt-6"></i> @endif
                                        </div>
                                    </div>
                                    <i class="fas fa-arrow-right text-indigo-500"></i>
                                    <div class="flex-1 text-center">
                                        <div class="aspect-square bg-white dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-1">
                                            @if($wpProduct && !empty($wpProduct['images']))<img src="{{ $wpProduct['images'][0]['src'] }}" class="w-full h-full object-contain">@else <i class="fab fa-wordpress fa-2x text-gray-200 mt-6"></i> @endif
                                        </div>
                                    </div>
                                </div>
                                <button wire:click="syncMainImage" wire:loading.attr="disabled" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all disabled:opacity-50">Sincronizar Principal</button>
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider text-right">Galería de Imágenes ERP</h4>
                                <div class="bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 h-[280px] overflow-y-auto custom-scrollbar">
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach(collect($localImages)->where('type', 'GALERIA') as $galImg)
                                            <div class="relative aspect-square bg-white dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-1 flex flex-col" wire:key="gal-v4-{{ $galImg->id }}">
                                                <div class="flex-1 min-h-0 relative">
                                                    <img src="{{ $galImg->getImageUrl() }}" class="w-full h-full object-contain">
                                                    @if($galImg->wp_media_id)
                                                        <div class="absolute top-1 right-1 bg-green-500 text-white p-0.5 rounded-full shadow-lg">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mt-1 flex items-center justify-center py-1 bg-gray-50 dark:bg-gray-700/50 rounded border-t border-gray-100 dark:border-gray-600">
                                                    <input type="checkbox" wire:click="toggleSyncToWp({{ $galImg->id }})" {{ $galImg->sync_to_wp ? 'checked' : '' }} class="w-3.5 h-3.5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                                    <span class="text-[9px] ml-1 font-bold text-gray-500 uppercase">WEB</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <button wire:click="syncGallery" wire:loading.attr="disabled" class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-bold disabled:opacity-50">Sincronizar Galería Web</button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Sync -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end items-center">
                        <button @click="syncActive = false" class="px-6 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition-all">Cerrar</button>
                    </div>
                </div>

                <!-- ========== VISTA 2: COMPARACIÓN (dentro del mismo modal) ========== -->
                <div x-show="viewMode === 'comparison'" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="p-6 bg-gray-50/50 dark:bg-gray-900/50 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="space-y-6">
                            <!-- Solo en ERP -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/30">
                                    <h5 class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest flex items-center gap-2">
                                        <i class="fas fa-server"></i> Solo en ERP ({{ count($onlyInErp) }})
                                    </h5>
                                </div>
                                <div class="p-4 flex flex-wrap gap-3">
                                    @forelse($onlyInErp as $idx => $erpImg)
                                        <a href="{{ Storage::disk('public')->url($erpImg['img_path']) }}" target="_blank" class="w-28 h-28 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 p-1 shadow-xs block cursor-pointer hover:ring-2 hover:ring-indigo-400 transition-all" wire:key="erp-cmp-{{ $idx }}">
                                            <img src="{{ Storage::disk('public')->url($erpImg['img_path']) }}" class="w-full h-full object-contain">
                                        </a>
                                    @empty
                                        <div class="w-full py-6 text-center text-gray-400 text-xs italic">Sincronización completa.</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Solo en WP -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                                <div class="px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-900/30">
                                    <h5 class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest flex items-center gap-2">
                                        <i class="fab fa-wordpress"></i> Solo en WordPress Media ({{ count($onlyInWp) }})
                                    </h5>
                                </div>
                                <div class="p-4 flex flex-wrap gap-3">
                                    @forelse($onlyInWp as $idx => $wpImg)
                                        <a href="{{ $wpImg['src'] }}" target="_blank" class="w-28 h-28 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 p-1 shadow-xs block cursor-pointer hover:ring-2 hover:ring-amber-400 transition-all" wire:key="wp-cmp-{{ $idx }}">
                                            <img src="{{ $wpImg['src'] }}" class="w-full h-full object-contain">
                                        </a>
                                    @empty
                                        <div class="w-full py-6 text-center text-gray-400 text-xs italic">Nada huérfano en la web.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Comparación -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <button @click="viewMode = 'sync'" class="px-5 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition-all flex items-center gap-2">
                            <i class="fas fa-arrow-left text-xs"></i> Volver a Sincronización
                        </button>
                        <button @click="syncActive = false" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold transition-all shadow-md">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style> 
        [x-cloak] { display: none !important; } 
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 20px; }
    </style>
</div>
