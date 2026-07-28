{{-- Establecer el header --}}
@php
$header = 'Seleccionar productos';
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900" x-data @product-copied.window="
    const data = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
    const priceFormatted = new Intl.NumberFormat().format(Math.round(data.price));
    
    let textToCopy = `${data.sku} - $${priceFormatted}\n${data.name}`;
    if (data.hasLink) {
        const link = `https://www.fervicom.com/?s=${data.sku.toLowerCase()}&post_type=product`;
        textToCopy += `\nDetalles en:\n${link}`;
    }

    const performFeedback = () => {
        const el = window.lastClickedCopyButton;
        if (el) {
            const originalBg = el.style.backgroundColor;
            const originalBorder = el.style.borderColor;
            const originalColor = el.style.color;
            const originalTransition = el.style.transition;
            
            el.style.transition = 'all 0.3s ease';
            el.style.backgroundColor = '#fef08a';
            el.style.borderColor = '#facc15';
            el.style.color = '#713f12';
            
            setTimeout(() => {
                el.style.backgroundColor = originalBg;
                el.style.borderColor = originalBorder;
                el.style.color = originalColor;
                setTimeout(() => {
                    el.style.transition = originalTransition;
                }, 300);
            }, 1000);
        }
        if (typeof Swal !== 'undefined') {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            }).fire({
                icon: 'success',
                title: '¡Copiado al portapapeles!'
            });
        } else if (window.Livewire) {
            Livewire.dispatch('show-toast', { type: 'success', message: '¡Texto copiado!' });
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            performFeedback();
        }).catch(err => {
            console.error('Error al copiar:', err);
            fallbackCopyToClipboardMobile(textToCopy, performFeedback);
        });
    } else {
        fallbackCopyToClipboardMobile(textToCopy, performFeedback);
    }
">
    <!-- Header fijo con búsqueda y carrito -->
    <div class="sticky top-16 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3">
            <!-- Barra superior con botón regresar y carrito -->
            <div class="flex items-center justify-between mb-3">
                <a href="{{ route('tenant.quoter') }}" class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-2" wire:navigate.hover>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="text-sm">Regresar</span>
                </a>

                @include('livewire.tenant.components.copy-mode-switch')

                @if(!$hideQuoter)
                <div class="flex items-center gap-1">
                    @if($this->quoterCount > 0)
                    <button @click="Swal.fire({
                                title: '¿Limpiar cotizador?',
                                text: 'Se eliminarán todos los productos seleccionados.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#4f46e5',
                                cancelButtonColor: '#ef4444',
                                confirmButtonText: 'Sí, limpiar',
                                cancelButtonText: 'Cancelar',
                                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.clearQuoter()
                                }
                            })"
                        class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                    @endif

                    <button @click="$wire.toggleCartModal();" class="relative p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 16a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        @if($this->quoterCount > 0)
                        <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $this->quoterCount }}
                        </span>
                        @endif
                    </button>
                </div>
                @endif
            </div>

            <!-- Contenedor principal con flex -->
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar productos..." class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex-1">
                    <select wire:model.live="selectedCategory" class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las categorías</option>
                        @foreach($this->getCategories() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button wire:click="toggleViewMode" class="flex items-center justify-center w-12 h-12 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    @if($viewMode === 'grid')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de productos -->
    <div class="px-4 py-4 {{ $viewMode === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4' : 'space-y-3' }}">
        @forelse($products as $product)
            @php
                $quantity = $this->getProductQuantity($product->id);
                $isSelected = $quantity > 0;
                $imgContext = $hideQuoter ? 'BODEGA' : 'COMERCIAL';
                $imgUrl = $product->getPrincipalImageUrl($imgContext);
                $hasImage = $imgUrl !== asset('images/placeholder-item.png');
            @endphp

            @if($viewMode === 'grid')
                {{-- MODO GRID --}}
                <div x-data="{ openMenu: false }" :class="openMenu ? 'z-[60]' : 'z-10'" @if($isSelected && !$hideQuoter) wire:click="increaseQuantity({{ $product->id }})" @endif class="relative flex flex-col bg-white dark:bg-gray-800 rounded-xl border transition-all duration-200 shadow-sm {{ $isSelected && !$hideQuoter ? 'ring-2 ring-indigo-500 border-indigo-500 scale-[1.02]' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300' }}">
                    
                    {{-- Imagen y Menú --}}
                    <div @click.stop="{{ $isCopyMode ? "copyImageToClipboard('" . $imgUrl . "')" : "\$dispatch('openImageModal', { productId: " . $product->id . ", context: '" . $imgContext . "' })" }}" class="aspect-square w-full relative bg-gray-100 dark:bg-gray-700 cursor-pointer active:opacity-70 transition-opacity">
                        @if($hasImage)
                            <img class="w-full h-full object-cover rounded-t-xl" src="{{ $imgUrl }}" alt="{{ $product->display_name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-3xl font-bold text-gray-400 dark:text-gray-500">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                            </div>
                        @endif

                        @if($quantity > 0)
                            <div class="absolute bottom-2 right-2 bg-indigo-600 text-white text-[10px] font-black rounded-lg px-2 py-0.5 shadow-lg border border-white/20 z-10">
                                {{ $quantity }}
                            </div>
                        @endif

                        <div class="absolute top-2 right-2 z-10">
                            <div class="relative">
                                <button @click.stop="openMenu = !openMenu" class="p-2 bg-white/95 dark:bg-gray-800/95 text-gray-600 dark:text-gray-300 rounded-bl-xl shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="openMenu" @click.away="openMenu = false" x-cloak class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1">
                                    <button @click.stop="$dispatch('openReservationModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Reservas</button>
                                    <button @click.stop="$dispatch('openTicketModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Solicitud Soporte</button>
                                    <button @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $imgContext }}' }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Imagen</button>
                                    <button @click.stop="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Observaciones</button>
                                    <button @click.stop="$dispatch('openCalculationModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Cálculos</button>
                                    <button @click.stop="$dispatch('openConfirmationModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors">Sol. Confirmación</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Info del producto --}}
                    @php
                        $mobileStock = $product->stock_bodega ?? 0;
                        $mobileSalidas = $product->salidas_7_meses ?? 0;
                        $mobileTotal = $mobileStock + $mobileSalidas;
                        $mobilePercentage = $mobileTotal > 0 ? round(($mobileStock * 100) / $mobileTotal) : 0;
                        
                        $hasNvpMobile = strpos(strtoupper($product->display_name), 'NVP') !== false;
                        if ($hasNvpMobile) {
                            $mobileTextClass = 'text-blue-600 dark:text-blue-400 font-bold';
                            $mobileSkuClass = 'text-blue-500/80 dark:text-blue-400/80';
                        } elseif ($mobilePercentage > 60) {
                            $mobileTextClass = 'text-red-600 dark:text-red-400 font-bold';
                            $mobileSkuClass = 'text-red-500/80 dark:text-red-400/80';
                        } elseif ($mobilePercentage >= 50 && $mobilePercentage <= 60) {
                            $mobileTextClass = 'text-gray-950 dark:text-white font-black'; // Mucha negrilla
                            $mobileSkuClass = 'text-gray-700 dark:text-gray-400 font-bold';
                        } else {
                            $mobileTextClass = 'text-gray-900 dark:text-white font-medium';
                            $mobileSkuClass = 'text-gray-500 dark:text-gray-400';
                        }
                    @endphp
                    <div class="p-3 flex-1 flex flex-col">
                        <div class="text-[10px] uppercase tracking-wider {{ $mobileSkuClass }} font-semibold truncate">{{ $product->sku ?: 'SIN SKU' }}</div>
                        <div class="{{ $mobileTextClass }} text-xs leading-tight line-clamp-3 mt-0.5 min-h-[2rem]">{{ $product->display_name }}</div>
                        
                        
                        @if($product->store_stock_details)
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach(explode(', ', $product->store_stock_details) as $storeDetail)
                                    @php $parts = explode(':', $storeDetail); @endphp
                                    @if(isset($parts[0]))
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-[7px] font-medium {{ ($parts[1] ?? 0) > 0 ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-100' }}">{{ $parts[0] }}: {{ number_format($parts[1] ?? 0, 0) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Info Logística Bodega --}}
                        @if($hideQuoter)
                            <div class="grid grid-cols-2 gap-1.5 mt-2 p-1.5 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                                <div class="flex flex-col"><span class="text-[8px] text-gray-500 uppercase leading-none">Exist.</span><span class="text-[10px] font-bold text-gray-900 dark:text-white">{{ number_format($product->stock_bodega, 0) }}</span></div>
                                <div class="flex flex-col"><span class="text-[8px] text-gray-500 uppercase leading-none">Trán.</span><span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">{{ number_format($product->in_transit, 0) }}</span></div>
                                <div class="flex flex-col"><span class="text-[8px] text-gray-500 uppercase leading-none">Res.</span><span class="text-[10px] font-bold text-orange-600 dark:text-orange-400">{{ number_format($product->reserved, 0) }}</span></div>
                                <div class="flex flex-col"><span class="text-[8px] text-gray-500 uppercase leading-none">Pick.</span><span class="text-[10px] font-mono text-indigo-600 dark:text-indigo-400">{{ $product->picking }}</span></div>
                            </div>
                        @endif

                        {{-- Precios / Selector Cantidad --}}
                        <div class="mt-auto pt-2">
                            @if(!$hideQuoter)
                                @if(!$isSelected)
                                    @php $allPrices = $product->all_prices; @endphp
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @foreach($allPrices as $label => $price)
                                            @php $isThisPriceSelected = $this->isPriceSelected($product->id, $label); @endphp
                                            <button @if($isCopyMode) @click.stop="window.lastClickedCopyButton = $event.currentTarget; $wire.copyProduct({{ $product->id }}, {{ $price }})" @else wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" wire:loading.attr="disabled" @endif class="relative w-full py-1.5 px-2 rounded-lg border transition-colors overflow-hidden {{ $isThisPriceSelected ? 'border-blue-500 bg-blue-100 dark:bg-blue-900/30 ring-2' : 'border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-900/10' }}">
                                                <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')">
                                                    <div class="text-[9px] uppercase font-bold truncate text-emerald-600 dark:text-emerald-400">{{ $label }} @if($isThisPriceSelected) ✓ @endif</div>
                                                    <div class="text-[13px] font-black text-emerald-700 dark:text-emerald-300">${{ number_format($price) }}</div>
                                                </div>
                                                <div wire:loading wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" class="flex items-center justify-center py-1">
                                                    <svg class="animate-spin h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    @php $priceInfo = $this->getSelectedPriceInfo($product->id); @endphp
                                    @if($priceInfo)
                                        <div class="mb-2 py-1 px-2 rounded-lg bg-emerald-50/30 text-center border border-emerald-500/20"><div class="text-[8px] uppercase font-bold text-emerald-600">{{ $priceInfo['label'] }}</div><div class="text-[12px] font-black text-emerald-700">${{ number_format($priceInfo['price']) }}</div></div>
                                    @endif
                                    <div class="p-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200">
                                        <div class="flex items-center gap-2">
                                            <button wire:click.stop="decreaseQuantity({{ $product->id }})" class="flex-1 h-12 flex items-center justify-center bg-indigo-600 text-white rounded-lg active:scale-95"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                                            <div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-700 rounded-lg shadow-inner">
                                                @php $itemIndex = $this->findProductInQuoter($product->id); @endphp
                                                <input type="number" wire:model.blur="quoterItems.{{ $itemIndex }}.quantity" wire:change="validateQuantity({{ $itemIndex }})" class="w-full bg-transparent border-none text-center font-black text-indigo-700 dark:text-indigo-300 text-lg focus:ring-0 p-0">
                                            </div>
                                            <button wire:click.stop="increaseQuantity({{ $product->id }})" class="flex-1 h-12 flex items-center justify-center bg-indigo-600 text-white rounded-lg active:scale-95"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- MODO LISTA --}}
                <div x-data="{ openMenu: false }" :class="openMenu ? 'z-[60]' : 'z-10'" class="relative bg-white dark:bg-gray-800 rounded-lg border p-4 transition-all {{ $isSelected && !$hideQuoter ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300' }}">
                    <div class="flex items-center space-x-4">
                        <div @click.stop="{{ $isCopyMode ? "copyImageToClipboard('" . $imgUrl . "')" : "\$dispatch('openImageModal', { productId: " . $product->id . ", context: '" . $imgContext . "' })" }}" class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer">
                            @if($hasImage)
                                <img class="w-full h-full object-cover" src="{{ $imgUrl }}" alt="{{ $product->display_name }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center"><span class="text-lg font-bold text-gray-400">{{ strtoupper(substr($product->name, 0, 1)) }}</span></div>
                            @endif
                        </div>
                                     @php
                                         $mobileListStock = $product->stock_bodega ?? 0;
                                         $mobileListSalidas = $product->salidas_7_meses ?? 0;
                                         $mobileListTotal = $mobileListStock + $mobileListSalidas;
                                         $mobileListPercentage = $mobileListTotal > 0 ? round(($mobileListStock * 100) / $mobileListTotal) : 0;
                                         
                                         $hasNvpMobileList = strpos(strtoupper($product->display_name), 'NVP') !== false;
                                         if ($hasNvpMobileList) {
                                             $mobileListTextClass = 'text-blue-600 dark:text-blue-400 font-bold';
                                             $mobileListSkuClass = 'text-blue-500/80 dark:text-blue-400/80';
                                         } elseif ($mobileListPercentage > 60) {
                                             $mobileListTextClass = 'text-red-600 dark:text-red-400 font-bold';
                                             $mobileListSkuClass = 'text-red-500/80 dark:text-red-400/80';
                                         } elseif ($mobileListPercentage >= 50 && $mobileListPercentage <= 60) {
                                             $mobileListTextClass = 'text-gray-950 dark:text-white font-black'; // Mucha negrilla
                                             $mobileListSkuClass = 'text-gray-700 dark:text-gray-400 font-bold';
                                         } else {
                                             $mobileListTextClass = 'text-gray-900 dark:text-white font-medium';
                                             $mobileListSkuClass = 'text-gray-500 dark:text-gray-400';
                                         }
                                     @endphp
                                     <div class="flex-1 min-w-0">
                                         <h3 class="text-sm font-medium {{ $mobileListTextClass }} truncate">{{ $product->display_name }}</h3>
                                         @if($product->sku)<p class="text-xs {{ $mobileListSkuClass }}">SKU: {{ $product->sku }}</p>@endif

                            <div class="flex flex-wrap gap-1 mt-1 items-center">
                                @if($product->store_stock_details)
                                    @foreach(explode(', ', $product->store_stock_details) as $storeDetail)
                                        @php $parts = explode(':', $storeDetail); @endphp
                                        @if(isset($parts[0]))
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ ($parts[1] ?? 0) > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/20' : 'bg-red-100 text-red-800' }}">{{ $parts[0] }}: {{ $parts[1] ?? 0 }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>               </div>

                        <div class="flex flex-col items-center gap-2 flex-shrink-0">
                            @if($quantity > 0 && !$hideQuoter)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 bg-indigo-600 text-white text-[10px] font-black rounded-lg shadow-sm">{{ $quantity }}</span>
                            @endif
                            <div class="relative">
                                <button @click.stop="openMenu = !openMenu" class="p-2 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-full shadow-sm"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg></button>
                                <div x-show="openMenu" @click.away="openMenu = false" x-cloak class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-lg border z-50 py-1">
                                    <button @click.stop="$dispatch('openReservationModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50">Reservas</button>
                                    <button @click.stop="$dispatch('openTicketModal', { productId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50">Solicitud Soporte</button>
                                    <button @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $imgContext }}' }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50">Imagen</button>
                                    <button @click.stop="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); openMenu = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50">Observaciones</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$hideQuoter)
                        @php $allPrices = $product->all_prices; @endphp
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($allPrices as $label => $price)
                                @php $isThisPriceSelected = $this->isPriceSelected($product->id, $label); @endphp
                                <button @if($isCopyMode) @click.stop="window.lastClickedCopyButton = $event.currentTarget; $wire.copyProduct({{ $product->id }}, {{ $price }})" @else wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" @endif class="flex-1 px-3 py-2 text-xs rounded-lg border transition-colors {{ $isThisPriceSelected ? 'border-blue-500 bg-blue-100 text-blue-700' : 'border-gray-300 bg-white text-gray-700' }}">
                                    <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"><div class="font-bold">${{ number_format($price) }}</div></div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay productos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">@if($search) No se encontraron productos para "{{ $search }}". @else No hay productos disponibles. @endif</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($products->hasPages())<div class="px-4 py-4">{{ $products->links() }}</div>@endif

    <!-- Modal del carrito -->
    @if($showCartModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-black bg-opacity-50" wire:click="toggleCartModal"></div>
        <div class="relative h-full flex items-stretch"><div class="w-full h-full bg-white dark:bg-gray-800 flex flex-col">
            <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0 bg-white dark:bg-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->quoterCount }} Productos seleccionados</h2>
                <button wire:click="toggleCartModal" class="text-gray-400 hover:text-gray-600 p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800">
                @if($selectedCustomer)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-green-800 dark:text-green-200 text-sm">{{ $selectedCustomer['businessName'] ?: trim(($selectedCustomer['firstName'] ?? '') . ' ' . ($selectedCustomer['secondName'] ?? '') . ' ' . ($selectedCustomer['lastName'] ?? '') . ' ' . ($selectedCustomer['secondLastName'] ?? '')) }}</h4>
                                <p class="text-xs text-green-600 dark:text-green-300">Identificación: {{ $selectedCustomer['identification'] }}</p>
                            </div>
                            <div class="flex items-center ml-2">
                                <button wire:click="editCustomer" class="text-blue-600 mr-4"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                <button wire:click="clearCustomer" class="text-green-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </div>
                        </div>
                    </div>
                @elseif($showCreateCustomerForm || $showCreateCustomerButton)
                    <livewire:tenant.vnt-company.vnt-company-form :reusable="true" :simplified="true" :companyId="$editingCustomerId" key="customer-form-{{ $editingCustomerId ?? 'new' }}" />
                @else
                    <div class="space-y-2"><label class="text-xs font-medium">Buscar Cliente</label><input wire:model.live.debounce.300ms="customerSearch" type="text" placeholder="Escribe nombre, NIT o cédula..." class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl">
                    @if(!empty($customerResults))
                        <div class="mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border max-h-60 overflow-y-auto">
                            @foreach($customerResults as $result)
                                <button wire:click="selectCustomer({{ $result['id'] }})" class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b last:border-0"><div class="font-bold">{{ $result['businessName'] ?: trim(($result['firstName'] ?? '') . ' ' . ($result['secondName'] ?? '') . ' ' . ($result['lastName'] ?? '') . ' ' . ($result['secondLastName'] ?? '')) }}</div><div class="text-xs text-gray-500">{{ $result['identification'] }}</div></button>
                            @endforeach
                        </div>
                    @endif
                    </div>
                @endif
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-4 min-h-0">
                @if(empty($quoterItems))<div class="text-center py-8"><p class="text-gray-500">Tu carrito está vacío</p></div>
                @else
                    <div class="space-y-4">
                        @foreach($quoterItems as $index => $item)
                            <div class="relative bg-white dark:bg-gray-800 p-3 border-b border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1"><h4 class="font-medium text-sm">{{ $item['name'] }}</h4><p class="text-[10px] text-indigo-600">Precio: {{ $item['price_label'] ?? 'Regular' }}</p></div>
                                    <button wire:click="removeFromQuoter({{ $index }})" class="text-red-500 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <input type="number" wire:model.blur="quoterItems.{{ $index }}.quantity" wire:change="validateQuantity({{ $index }})" class="w-12 px-1 py-0.5 text-center text-sm font-bold border border-gray-300 rounded bg-gray-50">
                                    </div>
                                    <div class="text-sm font-black text-indigo-700">${{ number_format($item['price'] * $item['quantity']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @if(!empty($quoterItems))
                <div class="px-4 py-4 border-t border-gray-200 space-y-4 flex-shrink-0 bg-white dark:bg-gray-800">
                    <div class="flex justify-between items-center text-lg font-bold"><span>Total:</span><span>${{ number_format($totalAmount, 2, ',', '.') }}</span></div>
                    @if($isEditing || $isEditingRemission)
                        @php $updateMethod = $isEditingRemission ? 'updateRemission' : 'updateQuote'; @endphp
                        <button wire:click="{{ $updateMethod }}" class="w-full bg-green-600 text-white font-medium py-3 rounded-lg">Actualizar</button>
                    @else
                        <button wire:click="saveQuote" class="w-full bg-indigo-600 text-white font-medium py-3 rounded-lg">Guardar Cotización</button>
                    @endif
                </div>
            @endif
        </div></div>
    </div>
    @endif

    {{-- MODAL METODOS PAGO --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-[110] bg-gray-900 bg-opacity-50 flex items-center justify-center p-2">
            <div class="w-full h-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden flex flex-col">
                <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                    <div><h1 class="text-lg font-bold">MÉTODOS DE PAGO</h1><p class="text-gray-300 text-sm">Total: ${{ number_format($totalAmount, 2, ',', '.') }}</p></div>
                    <button wire:click="closePaymentModal" class="text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="flex-1 p-4 overflow-y-auto">
                    <!-- Selección de Sede/Sucursal Destacada -->
                    @if(!empty($branches) && count($branches) > 1)
                    <div class="mb-4 p-4 bg-yellow-50 border-2 border-yellow-200 rounded-xl shadow-sm">
                        <label class="block text-xs font-black text-yellow-800 uppercase mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Confirmar Sede del Cliente
                        </label>
                        <select 
                            wire:model.live="selectedBranchId"
                            wire:change="selectBranch($event.target.value)"
                            class="block w-full text-base font-bold border-yellow-300 rounded-lg bg-white text-gray-900 focus:ring-yellow-500 focus:border-yellow-500 shadow-sm py-3"
                        >
                            @foreach($branches as $branch)
                                <option value="{{ $branch['id'] }}">
                                    {{ $branch['name'] }} {{ !empty($branch['city']['name']) ? '('.$branch['city']['name'].')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Selección de Sede/Sucursal (Panel Informativo) -->
                    @if(!empty($branches))
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-3 mb-4">
                        <h4 class="text-[10px] font-black text-indigo-700 dark:text-indigo-300 uppercase mb-2">Resumen de Envío</h4>
                        @if($selectedBranchId || count($branches) === 1)
                        <div class="text-[11px] text-gray-600 dark:text-gray-400">
                            <p class="font-bold text-indigo-800 dark:text-indigo-200">
                                {{ count($branches) === 1 ? $branches[0]['name'] : (collect($branches)->firstWhere('id', $selectedBranchId)['name'] ?? '') }}
                            </p>
                            <p><span class="font-bold">Dir:</span> {{ $selectedCustomer['address'] ?? 'N/A' }}</p>
                            <p><span class="font-bold">Ciudad:</span> {{ $selectedCustomer['cityName'] ?? 'N/A' }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 mb-4 grid grid-cols-2 gap-4 text-center">
                        <div><div class="text-sm text-gray-600">PAGADO</div><div class="text-xl font-bold text-blue-600">${{ number_format($totalPaid, 2, ',', '.') }}</div></div>
                        <div><div class="text-sm text-gray-600">FALTA</div><div class="text-xl font-bold text-red-600">${{ number_format($remainingBalance, 2, ',', '.') }}</div></div>
                    </div>
                    @foreach($paymentMethods as $key => $method)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 mb-4 {{ $currentPaymentMethod === $key ? 'border-yellow-500' : 'border-gray-200' }}">
                            <div class="flex justify-between items-center mb-3">
                                <button wire:click="selectPaymentMethod('{{ $key }}')" class="font-bold text-lg">{{ $method['name'] }}</button>
                                <button wire:click="selectAndPayTotal('{{ $key }}')" class="bg-green-600 text-white px-3 py-1 rounded-lg text-xs">TODO</button>
                            </div>
                            <input type="number" wire:model.live="paymentMethods.{{ $key }}.value" wire:change="calculatePaymentBalances" class="w-full text-xl font-bold text-center py-3 border rounded-lg dark:bg-gray-700">
                        </div>
                    @endforeach
                </div>
                <div class="p-4 border-t bg-gray-50 dark:bg-gray-900 flex gap-3">
                    <button wire:click="closePaymentModal" class="flex-1 bg-gray-600 text-white font-bold py-3 rounded-lg">CANCELAR</button>
                    <button wire:click="confirmPayment" class="flex-1 bg-green-600 text-white font-bold py-3 rounded-lg {{ (!$canProceedToPayment || $remainingBalance > 0) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ (!$canProceedToPayment || $remainingBalance > 0) ? 'disabled' : '' }}>FACTURAR</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ENTREGA --}}
    @if($showDeliveryModal)
        <div class="fixed inset-0 z-[120] flex items-end justify-center">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="$wire.closeDeliveryModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-t-xl w-full max-h-[90vh] flex flex-col p-6">
                <h3 class="text-lg font-semibold mb-4">Tipo de Entrega</h3>
                <select wire:model.live="selectedDeliveryType" class="w-full py-3 border rounded-lg mb-4 dark:bg-gray-700">
                    <option value="">Selecciona tipo...</option>
                    @foreach($deliveryTypes as $type)<option value="{{ $type['id'] }}">{{ $type['name'] }}</option>@endforeach
                </select>
                <select wire:model.live="selectedMethodPayment" class="w-full py-3 border rounded-lg mb-6 dark:bg-gray-700">
                    <option value="">Selecciona pago...</option>
                    @foreach($methodPayments as $method)<option value="{{ $method['id'] }}">{{ $method['name'] }}</option>@endforeach
                </select>



                <button wire:click="proceedWithRemissionCreation" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium">Crear Remisión</button>
                <button wire:click="closeDeliveryModal" class="w-full py-3 mt-2 text-gray-700 dark:text-gray-300">Cancelar</button>
            </div>
        </div>
    @endif

    <!-- Modal: Completar datos del cliente antes de facturar -->
    @if($showCompleteCustomerModal && $editingCustomerId)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-start justify-center p-4 z-[60] overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg my-6">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        📋 Completar datos del cliente
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Necesitas completar el régimen y responsabilidad fiscal para facturar.
                    </p>
                </div>
                <button wire:click="closeCompleteCustomerModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Warning -->
            <div class="mx-4 mt-3 flex items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs text-amber-800 dark:text-amber-200">
                    Completa el régimen y responsabilidad fiscal. Se sincronizará con Alegra automáticamente.
                </span>
            </div>

            <!-- Formulario cliente completo (no simplificado) -->
            <div class="px-4 pb-4 pt-3">
                <livewire:tenant.vnt-company.vnt-company-form
                    :reusable="true"
                    :simplified="false"
                    :companyId="$editingCustomerId"
                    key="complete-customer-form-mobile-{{ $editingCustomerId }}" />
            </div>
        </div>
    </div>
    @endif
    @livewire('tenant.components.product-reservation-modal')
</div>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-box-justification-modal', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const index = data.index;
            const requestedQuantity = data.requestedQuantity;
            const quntityxbox = data.quntityxbox;

            Swal.fire({
                title: '¿Cotizar cajas incompletas?',
                text: 'Para ofrecer precio x caja se deben cotizar cajas completas, Si esta seguro de querer continuar, cual es la razón para cotizar cajas incompletas ?',
                input: 'textarea',
                inputPlaceholder: 'Escriba la justificación aquí...',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
                preConfirm: (value) => {
                    if (!value || value.trim() === '') {
                        Swal.showValidationMessage('La justificación es obligatoria');
                    }
                    return value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.find('{{ $this->getId() }}').call('applyJustifiedQuantity', index, requestedQuantity, result.value);
                } else {
                    Livewire.find('{{ $this->getId() }}').$refresh();
                }
            });
        });
    });

    function fallbackCopyToClipboardMobile(text, callback) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            if (callback) callback();
        } catch (err) {
            console.error('Fallback error:', err);
        }
        document.body.removeChild(textArea);
    }
</script>