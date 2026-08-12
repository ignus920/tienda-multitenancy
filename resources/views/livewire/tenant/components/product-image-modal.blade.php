<div x-data="{ 
         isOpen: @entangle('isOpen').live,
         zoomedImage: null,
         currentPreview: null
     }" 
     x-init="$watch('isOpen', value => { 
         if(value) { 
             $nextTick(() => { 
                const container = $refs.principalUrlContainer;
                if(container && container.dataset.url) {
                    currentPreview = container.dataset.url;
                }
             });
         } else {
             currentPreview = null;
         }
     })"
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
                Imágenes: <span class="text-indigo-600 dark:text-indigo-400">@if($productCode){{ $productCode }} - @endif{{ $productName }}</span>
            </h3>
            
            <button @click="$wire.close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
            
            @php
                $isAdmin = in_array($userProfileId, [1, 2]);
            @endphp

            @if($isAdmin && !$isContextForced)
                <div class="flex p-1 bg-gray-100 dark:bg-gray-900 rounded-xl mb-6">
                    <button wire:click="$set('activeTab', 'COMERCIAL')" 
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'COMERCIAL' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        COMERCIAL
                    </button>
                    <button wire:click="$set('activeTab', 'BODEGA')" 
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'BODEGA' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        BODEGA
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 mb-6 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                        MODO: {{ $activeTab }}
                    </span>
                </div>
            @endif

            <div class="space-y-4">
                @php $principal = collect($images)->firstWhere('type', 'PRINCIPAL'); @endphp
                <div x-ref="principalUrlContainer" data-url="{{ $principal ? $principal->getImageUrl() : '' }}" class="hidden"></div>

                <div class="relative aspect-video sm:aspect-[16/9] w-full rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-900 shadow-inner border border-gray-200 dark:border-gray-700 group">
                    
                    <template x-if="currentPreview && !currentPreview.includes('.pdf')">
                        <img :src="currentPreview" class="w-full h-full object-contain transition-all duration-300 cursor-zoom-in" @click="zoomedImage = currentPreview">
                    </template>
                    
                    <template x-if="currentPreview && currentPreview.includes('.pdf')">
                        <div class="w-full h-full flex flex-col items-center justify-center bg-red-50 dark:bg-red-900/10 gap-4">
                            <svg class="w-20 h-20 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-7-7A2 2 0 0010.414 1H5a2 2 0 00-2 2v16a2 2 0 002 2h2m0-18v4a1 1 0 001 1h4m-6 4h6m-6 4h6m-6 4h6" />
                            </svg>
                            <a :href="currentPreview" target="_blank" class="px-6 py-2 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-lg">
                                Abrir Documento PDF
                            </a>
                        </div>
                    </template>

                    <template x-if="!currentPreview">
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-4 opacity-50">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-widest">Selecciona una imagen</span>
                        </div>
                    </template>
                </div>

                <!-- Miniaturas -->
                <div class="relative group/slider" x-data="{ 
                    scrollNext() { this.$refs.carousel.scrollBy({ left: 200, behavior: 'smooth' }) },
                    scrollPrev() { this.$refs.carousel.scrollBy({ left: -200, behavior: 'smooth' }) }
                }">
                    <button @click="scrollPrev()" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 p-2 bg-white/90 dark:bg-gray-800/90 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 opacity-0 group-hover/slider:opacity-100 transition-opacity -ml-2 text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div x-ref="carousel" class="flex items-center gap-3 overflow-x-auto pb-4 pt-1 px-1 custom-scrollbar scroll-smooth snap-x">
                        @foreach($images as $img)
                            <button type="button" 
                                    @click="currentPreview = '{{ $img->getImageUrl() }}'"
                                    class="relative flex-none w-20 h-20 rounded-xl overflow-hidden border-2 transition-all snap-start {{ $img->type === 'PRINCIPAL' ? 'ring-2 ring-indigo-500 ring-offset-2' : '' }}"
                                    :class="currentPreview === '{{ $img->getImageUrl() }}' ? 'border-indigo-500 scale-105 shadow-md' : 'border-transparent opacity-70 hover:opacity-100'">
                                @if($img->type === 'PDF' || str_ends_with(strtolower($img->img_path), '.pdf'))
                                    <div class="w-full h-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-7-7A2 2 0 0010.414 1H5a2 2 0 00-2 2v16a2 2 0 002 2h2m0-18v4a1 1 0 001 1h4m-6 4h6m-6 4h6m-6 4h6" />
                                        </svg>
                                    </div>
                                @else
                                    <img src="{{ $img->getImageUrl() }}" class="w-full h-full object-cover">
                                @endif
                                @if($img->type === 'PRINCIPAL')
                                    <div class="absolute top-0 right-0 bg-indigo-500 text-white text-[8px] px-1 rounded-bl-lg font-bold">PRINCIPAL</div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <button @click="scrollNext()" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 p-2 bg-white/90 dark:bg-gray-800/90 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 opacity-0 group-hover/slider:opacity-100 transition-opacity -mr-2 text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex {{ $activeTab === 'COMERCIAL' ? 'justify-between' : 'justify-end' }} items-center">
            @if($activeTab === 'COMERCIAL')
                @if($wpProductUrl)
                    <a href="{{ $wpProductUrl }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Ir a la tienda
                    </a>
                @else
                    <div class="px-4 py-2 bg-red-600 text-white font-bold rounded-xl shadow-md text-xs sm:text-sm uppercase tracking-wider">
                        No esta en página WEB
                    </div>
                @endif
            @endif
            <button @click="$wire.close()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl transition-all shadow-sm">
                Cerrar
            </button>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 20px; }
    </style>

    <!-- Overlay de Zoom -->
    <div x-show="zoomedImage" @click="zoomedImage = null" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md cursor-zoom-out" x-cloak>
        <div class="relative max-w-5xl w-full h-full flex items-center justify-center" @click.stop>
            <img :src="zoomedImage" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
            <button @click="zoomedImage = null" class="absolute top-4 right-4 text-white p-3 bg-white/10 hover:bg-white/20 rounded-full backdrop-blur-md">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    @if($isOpen)
        <livewire:tenant.components.word-press-sync-modal />
    @endif
</div>
