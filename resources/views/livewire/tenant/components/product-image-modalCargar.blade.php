<div x-data="{ 
         isOpen: @entangle('isOpen').live,
         activeTab: @entangle('activeTab').live,
         zoomedImage: null,
         currentPreview: null,
     }" 
     x-init="
        $watch('isOpen', value => { 
            if(!value) currentPreview = null;
        });
     "
     x-show="isOpen" 
     @keydown.escape.window="zoomedImage ? zoomedImage = null : $wire.close()"
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
         wire:init="loadWpProductStatus"
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
            
            <!-- Pestañas de Navegación -->
            @php
                $isAdmin = in_array($userProfileId, [1, 2]);
                $isAlmacen = $userProfileId == 6;
                $isVendedor = $userProfileId == 4;
            @endphp

            @if($isAdmin)
                <div class="flex p-1 bg-gray-100 dark:bg-gray-900 rounded-xl mb-6">
                    <button wire:click="$set('activeTab', 'COMERCIAL')" 
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'COMERCIAL' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-shopping-cart mr-2"></i> COMERCIAL
                    </button>
                    <button wire:click="$set('activeTab', 'BODEGA')" 
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'BODEGA' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        <i class="fas fa-warehouse mr-2"></i> BODEGA
                    </button>
                </div>
            @else
                <!-- Si no es admin, mostrar solo el indicador de en qué sección está -->
                <div class="flex items-center gap-2 mb-6 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                        MODO: {{ $activeTab }}
                    </span>
                </div>
            @endif
                  <!-- VISOR PRINCIPAL Y CARRUSEL -->
            <div class="space-y-4">
                <!-- Visor Grande -->
                @php $principal = collect($images)->firstWhere('type', 'PRINCIPAL'); @endphp
                
                {{-- Este div oculto fuerza la actualización de la previsualización cuando Livewire refresca el componente --}}
                <div x-init="currentPreview = '{{ $principal ? $principal->getImageUrl() : '' }}'" class="hidden"></div>

                <div class="relative aspect-video sm:aspect-[16/9] w-full rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-900 shadow-inner border border-gray-200 dark:border-gray-700 group cursor-zoom-in"
                     @click="if(currentPreview && !currentPreview.includes('.pdf')) zoomedImage = currentPreview">
                    <template x-if="currentPreview && !currentPreview.includes('.pdf')">
                        <img :src="currentPreview" 
                             class="w-full h-full object-contain transition-all duration-300">
                    </template>
                    <template x-if="currentPreview && currentPreview.includes('.pdf')">
                        <div class="w-full h-full flex flex-col items-center justify-center bg-red-50 dark:bg-red-900/10 gap-4" @click.stop>
                            <svg class="w-24 h-24 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                <path d="M3 8a2 2 0 012-2h2v10H5a2 2 0 01-2-2V8z" />
                            </svg>
                            <a :href="currentPreview" target="_blank" class="px-6 py-2 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors shadow-lg flex items-center gap-2">
                                <i class="fas fa-file-pdf"></i> Abrir Documento PDF
                            </a>
                        </div>
                    </template>
                    <template x-if="!currentPreview">
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-4">
                            <svg class="w-20 h-20 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-widest opacity-50">Selecciona una imagen para verla</span>
                        </div>
                    </template>

                    <!-- Overlay de feedback de click -->
                    <div x-show="currentPreview && !currentPreview.includes('.pdf')" class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                        <div class="bg-black/50 backdrop-blur-md p-3 rounded-full transform scale-50 group-hover:scale-100 transition-all">
                            <i class="fas fa-search-plus text-xl text-white"></i>
                        </div>
                    </div>
                </div>

                <!-- Carrusel de Miniaturas -->
                <div class="relative group/carousel">
                    <div x-ref="carousel" class="flex items-center gap-3 overflow-x-auto pb-4 pt-1 px-1 custom-scrollbar scroll-smooth snap-x">
                        @foreach($images as $img)
                            <button type="button" 
                                    @click="currentPreview = '{{ $img->getImageUrl() }}'"
                                    class="relative flex-none w-20 h-20 rounded-xl overflow-hidden border-2 transition-all snap-start group/thumb
                                           {{ $img->type === 'PRINCIPAL' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-transparent hover:border-indigo-300' }}"
                                    :class="currentPreview === '{{ $img->getImageUrl() }}' ? 'scale-105 shadow-lg border-indigo-500 opacity-100' : 'opacity-60 hover:opacity-100'">
                                
                                @if($img->type === 'PDF' || str_ends_with(strtolower($img->img_path), '.pdf'))
                                    <div class="w-full h-full bg-red-50 dark:bg-red-900/10 flex flex-col items-center justify-center gap-1">
                                        <i class="fas fa-file-pdf text-red-500 text-xl group-hover/thumb:scale-110 transition-transform"></i>
                                        <span class="text-[8px] font-bold text-red-600 dark:text-red-400 uppercase tracking-tighter">PDF</span>
                                    </div>
                                @else
                                    <img src="{{ $img->getImageUrl() }}" class="w-full h-full object-cover group-hover/thumb:scale-110 transition-transform duration-500">
                                @endif
    
                                @if($img->type === 'PRINCIPAL')
                                    <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[7px] px-1.5 py-0.5 rounded-bl-lg font-black uppercase tracking-tighter shadow-sm z-10">
                                        {{ $activeTab }}
                                    </div>
                                @endif

                                <!-- Indicator for selected -->
                                <div x-show="currentPreview === '{{ $img->getImageUrl() }}'" class="absolute inset-x-0 bottom-0 h-1 bg-indigo-500"></div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                <!-- Gestión Imagen Principal (Carga) -->
                <div class="bg-indigo-50/30 dark:bg-indigo-900/10 rounded-2xl p-6 border border-indigo-100/50 dark:border-indigo-800/50 relative overflow-hidden group/portada">
                    <div class="absolute top-0 right-0 p-8 opacity-5 group-hover/portada:scale-110 transition-transform duration-700">
                        <i class="fas fa-image text-8xl text-indigo-600"></i>
                    </div>
                    
                    <div class="relative z-10">
                        <h4 class="font-black text-gray-900 dark:text-white mb-1 flex items-center gap-2 uppercase tracking-tighter">
                            <i class="fas fa-star text-amber-500"></i> Imagen de Portada
                        </h4>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mb-4 font-medium italic">Esta será la imagen principal en el listado de {{ strtolower($activeTab) }}</p>
                        
                        <div class="relative">
                            <input type="file" wire:model.live="mainImage" id="main_image_input" class="hidden">
                            <label for="main_image_input" class="flex items-center justify-center gap-4 px-6 py-4 bg-white dark:bg-gray-800 border-2 border-dashed border-indigo-200 dark:border-indigo-800 rounded-2xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-all hover:bg-gray-50 dark:hover:bg-indigo-900/20 shadow-sm group">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/40 rounded-xl group-hover:scale-110 transition-transform shadow-inner">
                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 uppercase mt-1">Subir Nueva Portada</span>
                                </div>
                            </label>
                            
                            <div wire:loading wire:target="mainImage" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md rounded-2xl flex flex-col items-center justify-center gap-3 z-20">
                                <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase animate-pulse">Procesando Imagen...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Grid de Gestión (Miniaturas inferiores con controles) -->
            <div class="pt-4">
                <h4 class="font-bold text-gray-800 dark:text-white text-sm mb-4 px-2">Galería de Imágenes</h4>
                <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-6 gap-3">
                    @foreach($images as $galImage)
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-sm transition-transform hover:scale-105 group flex flex-col">
                            <div class="flex-1 min-h-0 relative flex items-center justify-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                @if($galImage->type === 'PDF' || str_ends_with(strtolower($galImage->img_path), '.pdf'))
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                                        <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl group-hover:scale-110 transition-transform">
                                            <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                        </div>
                                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-tighter text-center">
                                            Documento PDF
                                        </span>
                                    </div>
                                @else
                                    <div class="w-full h-full cursor-zoom-in relative group/thumb" 
                                         @click="currentPreview = '{{ $galImage->getImageUrl() }}'; zoomedImage = '{{ $galImage->getImageUrl() }}'">
                                        <img src="{{ $galImage->getImageUrl() }}" class="w-full h-full object-cover rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                                        <div class="absolute inset-0 bg-indigo-600/20 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                                            <i class="fas fa-search-plus text-white text-lg"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Footer de la imagen: Checkbox y Delete -->
                            <div class="p-2 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between gap-1">
                                <label class="flex items-center gap-1.5 cursor-pointer group/web">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" 
                                               wire:click="toggleSyncToWp({{ $galImage->id }})"
                                               {{ $galImage->sync_to_wp ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 bg-white dark:bg-gray-700 transition-colors">
                                    </div>
                                    <span class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase leading-none group-hover/web:text-indigo-500 transition-colors">Sinc. Web</span>
                                </label>

                                <button type="button" 
                                        @click="$wire.deleteImage({{ $galImage->id }})"
                                        class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
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

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex {{ $activeTab === 'COMERCIAL' && $wpProductUrl ? 'justify-between' : 'justify-end' }} items-center">
            @if($activeTab === 'COMERCIAL' && $wpProductUrl)
                <a href="{{ $wpProductUrl }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Ir a la tienda
                </a>
            @endif
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

    <!-- Overlay de Zoom (Lightbox) -->
    <div x-show="zoomedImage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md cursor-zoom-out"
         @click="zoomedImage = null"
         x-cloak>
        
        <div class="relative max-w-5xl w-full h-full flex items-center justify-center" @click.stop>
            <img :src="zoomedImage" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl border-2 border-white/10">
            
            <button @click="zoomedImage = null" 
                    class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors p-3 bg-white/10 hover:bg-white/20 rounded-full backdrop-blur-md">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Componente de Sincronización WordPress -->
    @if($isOpen)
        <livewire:tenant.components.word-press-sync-modal />
    @endif
</div>
