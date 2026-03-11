<div x-data="{ isOpen: @entangle('isOpen').live }" 
     x-show="isOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     x-cloak>
    
    @if($isOpen)
    <div @click.away="$wire.close()" 
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all border border-gray-200 dark:border-gray-700">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Imágenes: <span class="text-indigo-600 dark:text-indigo-400">{{ $productName }}</span>
            </h3>
            
            <div class="flex items-center gap-2">
                <!-- Botón de Sincronización WordPress -->
                @if($productId && $hasWpProduct)
                    <button @click="$dispatch('openWordPressSync', { itemId: {{ $productId }} })" 
                            class="flex items-center gap-2 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-bold hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-all border border-indigo-200 dark:border-indigo-800">
                        <i class="fab fa-wordpress text-lg"></i>
                        Sincronizar WP
                    </button>
                @endif

                <button @click="$wire.close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
            
            <!-- Sección Imagen Principal -->
            <div class="bg-indigo-50/30 dark:bg-indigo-900/10 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z" />
                    </svg>
                    <h4 class="font-bold text-gray-900 dark:text-white">Imagen Principal</h4>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <!-- Preview Actual -->
                    <div class="relative w-32 h-32 rounded-xl border-4 border-indigo-200 dark:border-indigo-800 overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-lg group">
                        @php $principal = collect($images)->firstWhere('type', 'PRINCIPAL'); @endphp
                        @if($principal)
                            <img src="{{ $principal->getImageUrl() }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-indigo-600/20 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-[10px] font-bold text-white uppercase tracking-wider">Actual</span>
                        </div>
                    </div>

                    <!-- Input Carga -->
                    <div class="flex-1 space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cambiar o establecer la imagen que se verá en el listado principal.</p>
                        <div class="relative group">
                            <input type="file" wire:model.live="mainImage" id="main_image_input" class="hidden">
                            <label for="main_image_input" class="flex items-center justify-center gap-3 px-6 py-3 bg-white dark:bg-gray-700 border-2 border-dashed border-indigo-300 dark:border-indigo-800 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-600 transition-all hover:bg-gray-50 dark:hover:bg-gray-600 shadow-sm group">
                                <svg class="w-5 h-5 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Seleccionar nuevo archivo</span>
                            </label>
                            <div wire:loading wire:target="mainImage" class="mt-2 flex items-center gap-2 text-indigo-600 dark:text-indigo-400 text-xs italic font-semibold">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Subiendo imagen principal...
                            </div>
                        </div>
                        @error('mainImage') <span class="text-xs text-red-500 font-bold ml-1 italic">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección Galería -->
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h4 class="font-bold text-gray-900 dark:text-white">Galería de Archivos ({{ collect($images)->whereIn('type', ['GALERIA', 'PDF'])->count() }})</h4>
                    </div>
                </div>

                <!-- Grid de Galería -->
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    @foreach(collect($images)->whereIn('type', ['GALERIA', 'PDF']) as $galImage)
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-sm transition-transform hover:scale-105 group flex flex-col">
                            <!-- Enlace para ver/abrir archivo -->
                            <a href="{{ $galImage->getImageUrl() }}" target="_blank" class="flex-1 min-h-0 relative flex items-center justify-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                @if($galImage->type === 'PDF' || str_ends_with(strtolower($galImage->img_path), '.pdf'))
                                    <div class="flex flex-col items-center gap-2">
                                        <!-- Icono SVG de PDF -->
                                        <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            <path d="M3 8a2 2 0 012-2h2v10H5a2 2 0 01-2-2V8z" />
                                        </svg>
                                        <span class="text-[9px] font-bold text-gray-500 dark:text-gray-400 truncate w-24 text-center px-1" title="{{ basename($galImage->img_path) }}">
                                            {{ basename($galImage->img_path) }}
                                        </span>
                                    </div>
                                @else
                                    <img src="{{ $galImage->getImageUrl() }}" class="w-full h-full object-cover rounded-md">
                                    <!-- Overlay de lupa al hacer hover -->
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 flex items-center justify-center transition-all">
                                        <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transform scale-50 group-hover:scale-100 transition-all"></i>
                                    </div>
                                @endif
                            </a>
                            
                            <!-- Footer de la imagen: Checkbox y Delete -->
                            <div class="p-2 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between gap-1">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" 
                                           wire:click="toggleSyncToWp({{ $galImage->id }})"
                                           {{ $galImage->sync_to_wp ? 'checked' : '' }}
                                           class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 bg-white dark:bg-gray-700 transition-colors">
                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase leading-none">Web</span>
                                </label>

                                <button wire:confirm="¿Eliminar este archivo?" wire:click="deleteImage({{ $galImage->id }})" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <!-- Botón Agregar a Galería -->
                    <label class="relative aspect-square rounded-xl border-2 border-dashed border-emerald-300 dark:border-emerald-800 bg-emerald-50/20 dark:bg-emerald-900/5 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 cursor-pointer flex flex-col items-center justify-center gap-2 transition-all hover:border-emerald-500">
                        <input type="file" wire:model.live="galleryImages" multiple accept=".pdf,image/*" class="hidden">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-tighter">Agregar</span>
                        <div wire:loading wire:target="galleryImages" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center rounded-xl">
                            <svg class="animate-spin h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </label>
                </div>
                @error('galleryImages.*') <span class="text-xs text-red-500 font-bold ml-1 italic">{{ $message }}</span> @enderror
            </div>

            <p class="text-[10px] text-gray-500 dark:text-gray-500 italic flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Nota: Formatos permitidos: JPG, PNG, WEBP, PDF. Tamaño máximo: 2MB por archivo.
            </p>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button @click="$wire.close()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl transition-all shadow-sm">
                Cerrar
            </button>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
    </style>

    <!-- Componente de Sincronización WordPress -->
    @if($isOpen)
        <livewire:tenant.components.word-press-sync-modal />
    @endif
</div>
