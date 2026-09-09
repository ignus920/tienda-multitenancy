<div class="pb-8"
     x-data="{ 
        cart: [],
        viewMode: localStorage.getItem('portal_view_mode') || 'list',
        showPromoModal: false,
        promoModalImg: '',
        init() {
            let savedPerPage = localStorage.getItem('portal_per_page');
            if (savedPerPage) {
                $wire.set('perPage', parseInt(savedPerPage));
            }
        },
        savePerPage(val) {
            localStorage.setItem('portal_per_page', val);
        },
        toggleViewMode() {
            this.viewMode = this.viewMode === 'list' ? 'grid' : 'list';
            localStorage.setItem('portal_view_mode', this.viewMode);
        },
        addToCart(id, code, sku, name, priceCash, priceCredit, scale1Qty, scale1Discount, scale2Qty, scale2Discount, boxQty, boxDiscount) {
            let exists = this.cart.find(item => item.id === id);
            if (exists) {
                exists.qty++;
            } else {
                this.cart.push({ 
                    id, 
                    code, 
                    sku, 
                    name, 
                    priceCash, 
                    priceCredit, 
                    scale1Qty: parseInt(scale1Qty) || 0, 
                    scale1Discount: parseFloat(scale1Discount) || 0, 
                    scale2Qty: parseInt(scale2Qty) || 0, 
                    scale2Discount: parseFloat(scale2Discount) || 0, 
                    boxQty: parseInt(boxQty) || 0, 
                    boxDiscount: parseFloat(boxDiscount) || 0, 
                    qty: 1 
                });
            }
        },
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        updateQty(index, delta) {
            this.cart[index].qty += delta;
            if (this.cart[index].qty < 1) this.cart[index].qty = 1;
        },
        getItemPrice(item) {
            let filter = $wire.paymentFilter;
            let basePrice = filter === 'credito' ? item.priceCredit : item.priceCash;
            let discount = 0;
            if (item.boxQty > 0 && item.qty >= item.boxQty) {
                discount = item.boxDiscount;
            } else if (item.scale2Qty > 0 && item.qty >= item.scale2Qty) {
                discount = item.scale2Discount;
            } else if (item.scale1Qty > 0 && item.qty >= item.scale1Qty) {
                discount = item.scale1Discount;
            }
            return basePrice * (1 - (discount / 100));
        },
        get total() {
            return this.cart.reduce((sum, item) => {
                return sum + (this.getItemPrice(item) * item.qty);
            }, 0);
        },
        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        }
     }"
>
    <x-portal-nav active="catalog" />

    <!-- Modal de Visualización Ampliada de Promoción (Lightbox) -->
    <div x-show="showPromoModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;"
         @click="showPromoModal = false"
         @keydown.escape.window="showPromoModal = false">
        
        <div class="relative max-w-5xl max-h-[90vh] bg-transparent rounded-2xl overflow-hidden shadow-2xl"
             @click.stop
             x-transition:enter="transition ease-out duration-350 transform scale-95"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-250 transform scale-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Botón de Cerrar -->
            <button @click="showPromoModal = false" 
                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/60 hover:bg-black/80 text-white transition-colors border border-white/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Imagen Ampliada -->
            <img :src="promoModalImg" alt="Promoción ampliada" class="max-w-full max-h-[85vh] object-contain rounded-xl">
        </div>
    </div>

    <!-- Layout con sidebar sticky -->
    <div class="flex gap-0">
        <div class="flex-1 min-w-0 px-4 sm:px-6 pt-6">



            <!-- Slider Promocional Premium -->
            @if(isset($sliders) && $sliders->count() > 0)
                <div x-data="{
                        activeSlide: 0,
                        slidesCount: {{ $sliders->count() }},
                        autoPlayInterval: null,
                        progress: 0,
                        progressInterval: null,
                        startAutoPlay() {
                            this.resetProgress();
                            this.autoPlayInterval = setInterval(() => {
                                this.next();
                            }, 6000);
                            this.progressInterval = setInterval(() => {
                                this.progress += (100 / 60);
                                if (this.progress >= 100) this.progress = 100;
                            }, 100);
                        },
                        stopAutoPlay() {
                            clearInterval(this.autoPlayInterval);
                            clearInterval(this.progressInterval);
                        },
                        resetProgress() {
                            this.progress = 0;
                            clearInterval(this.progressInterval);
                            this.progressInterval = setInterval(() => {
                                this.progress += (100 / 60);
                                if (this.progress >= 100) this.progress = 100;
                            }, 100);
                        },
                        next() {
                            this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                            this.resetProgress();
                        },
                        prev() {
                            this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount;
                            this.resetProgress();
                        }
                     }"
                     x-init="startAutoPlay()"
                     @mouseenter="stopAutoPlay()"
                     @mouseleave="startAutoPlay()"
                     style="height: 180px;"
                     class="relative w-full max-w-5xl mx-auto overflow-hidden rounded-2xl mb-6 group shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                >
                    <!-- Slides -->
                    <div class="relative w-full h-full">
                        @foreach($sliders as $index => $slider)
                            <div x-show="activeSlide === {{ $index }}"
                                 x-transition:enter="transition ease-out duration-700"
                                 x-transition:enter-start="opacity-0 translate-x-8"
                                 x-transition:enter-end="opacity-100 translate-x-0"
                                 x-transition:leave="transition ease-in duration-400"
                                 x-transition:leave-start="opacity-100 translate-x-0"
                                 x-transition:leave-end="opacity-0 -translate-x-8"
                                 @click="promoModalImg = '{{ $slider->image_path }}'; showPromoModal = true"
                                 class="absolute inset-0 w-full h-full flex items-center"
                            >
                                <!-- Imagen con efecto parallax en hover -->
                                <img src="{{ $slider->image_path }}" alt="{{ $slider->title }}" 
                                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                                
                                <!-- Gradiente premium con tinte de marca dinámico -->
                                @php
                                    $hex = $slider->overlay_color ?: '#1e1b4b';
                                    $showOverlay = ($hex !== 'transparent');
                                    if ($showOverlay) {
                                        $r = hexdec(substr($hex, 1, 2) ?: '1e');
                                        $g = hexdec(substr($hex, 3, 2) ?: '1b');
                                        $b = hexdec(substr($hex, 5, 2) ?: '4b');
                                    }
                                @endphp
                                @if($showOverlay)
                                    <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba({{ $r }},{{ $g }},{{ $b }},0.90) 0%, rgba({{ $r }},{{ $g }},{{ $b }},0.55) 40%, rgba(0,0,0,0.15) 100%);"></div>
                                @endif

                                <!-- Contenido del slide -->
                                <div class="relative z-10 px-10 sm:px-16 max-w-2xl text-white flex flex-col h-full justify-center 
                                    {{ $slider->text_position === 'center' ? 'items-center text-center mx-auto' : ($slider->text_position === 'right' ? 'items-end text-right ml-auto' : 'items-start text-left') }}">
                                    
                                    <!-- Badge decorativo -->
                                    @if($slider->badge_text)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest mb-2"
                                             style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.20);">
                                            <span class="w-1 h-1 rounded-full bg-emerald-400 animate-pulse"></span>
                                            {{ $slider->badge_text }}
                                        </div>
                                    @endif
                                    
                                    <!-- Título -->
                                    <h3 class="text-lg sm:text-xl md:text-2xl font-extrabold leading-tight mb-1"
                                        style="text-shadow: 0 2px 20px rgba(0,0,0,0.3); {{ $slider->text_color ? 'color: ' . $slider->text_color . ' !important;' : 'color: #ffffff;' }}">
                                        {{ $slider->title }}
                                    </h3>

                                    <!-- Subtítulo -->
                                    @if($slider->subtitle)
                                        <p class="text-xs sm:text-sm mb-3 max-w-md" 
                                           style="text-shadow: 0 1px 8px rgba(0,0,0,0.2); {{ $slider->text_color ? 'color: ' . $slider->text_color . ' !important; opacity: 0.85;' : 'color: rgba(255,255,255,0.8);' }}">
                                            {{ $slider->subtitle }}
                                        </p>
                                    @endif
                                    
                                    <!-- Botón CTA Premium -->
                                    @if($slider->action_button_text && $slider->action_url)
                                        @php
                                            $portalBtnStyle = "";
                                            if ($slider->button_color) {
                                                $portalBtnStyle .= "background-color: " . $slider->button_color . " !important; ";
                                            } else {
                                                $portalBtnStyle .= "background: rgba(255,255,255,0.18); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.25); ";
                                            }
                                            if ($slider->button_text_color) {
                                                $portalBtnStyle .= "color: " . $slider->button_text_color . " !important; ";
                                            } else {
                                                $portalBtnStyle .= "color: #ffffff !important; ";
                                            }
                                        @endphp
                                        <a href="{{ $slider->action_url }}" 
                                           @click.stop
                                           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:shadow-xl active:scale-95 group/btn"
                                           style="{{ $portalBtnStyle }}">
                                            {{ $slider->action_button_text }}
                                            <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Navegación Previous con glassmorphism -->
                    <button @click="prev()" 
                            class="absolute left-4 top-1/2 -translate-y-1/2 p-2.5 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 z-20 hover:scale-110 active:scale-95"
                            style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.20);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <!-- Navegación Next con glassmorphism -->
                    <button @click="next()" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 p-2.5 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 z-20 hover:scale-110 active:scale-95"
                            style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.20);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <!-- Indicadores con barra de progreso -->
                    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20 px-4 py-2 rounded-full"
                         style="background: rgba(0,0,0,0.25); backdrop-filter: blur(10px);">
                        @foreach($sliders as $index => $slider)
                            <button @click="activeSlide = {{ $index }}; resetProgress()" 
                                    class="relative h-1.5 rounded-full transition-all duration-500 overflow-hidden"
                                    :class="activeSlide === {{ $index }} ? 'w-8 bg-white/30' : 'w-2 bg-white/40 hover:bg-white/60'"
                            >
                                <div x-show="activeSlide === {{ $index }}"
                                     class="absolute inset-y-0 left-0 bg-white rounded-full transition-all duration-100"
                                     :style="'width: ' + progress + '%'"
                                ></div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$settingsConfigured)
                <div class="mb-4 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-800/50 flex items-center gap-3 text-red-800 dark:text-red-300">
                    <svg class="w-6 h-6 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <span class="font-bold text-sm">Estimado cliente:</span>
                        <span class="text-sm">Su usuario aún no cuenta con una lista de precios asignada en nuestro portal. Por favor, póngase en contacto con el administrador del sistema para que le habilite sus precios de Contado y Crédito.</span>
                    </div>
                </div>
            @endif

            <div class="max-w-5xl">
            <!-- Barra de búsqueda + filtros -->
            @php
                $fpPillOn  = 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm';
                $fpPillOff = 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200';
            @endphp
            <div class="mb-4 space-y-2.5">
                <!-- Buscador -->
                <div class="relative rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z"/></svg>
                    <input wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Buscar por nombre o SKU…"
                        class="block w-full pl-11 pr-4 py-3 border-0 rounded-2xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-0 text-sm">
                </div>

                <!-- Filtros -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Stock -->
                    <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                        <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $stockFilter === 'all' ? $fpPillOn : $fpPillOff }}">
                            <input type="radio" wire:model.live="stockFilter" value="all" class="sr-only">Todos
                        </label>
                        <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $stockFilter === 'in_stock' ? $fpPillOn : $fpPillOff }}">
                            <input type="radio" wire:model.live="stockFilter" value="in_stock" class="sr-only">En stock
                        </label>
                    </div>

                    <!-- Forma de pago -->
                    <div class="inline-flex items-center rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                        <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Pago</span>
                        <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $paymentFilter === 'contado' ? $fpPillOn : $fpPillOff }}">
                            <input type="radio" wire:model.live="paymentFilter" value="contado" class="sr-only">Contado
                        </label>
                        <label class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold transition-colors {{ $paymentFilter === 'credito' ? $fpPillOn : $fpPillOff }}">
                            <input type="radio" wire:model.live="paymentFilter" value="credito" class="sr-only">Crédito
                        </label>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button @click="toggleViewMode()" type="button"
                                class="w-9 h-9 flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                :title="viewMode === 'list' ? 'Ver en cuadrícula' : 'Ver en lista'">
                            <svg x-show="viewMode === 'list'" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <svg x-show="viewMode === 'grid'" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="relative" title="Productos por página">
                            <select wire:model.live="perPage" @change="savePerPage($event.target.value)"
                                    class="appearance-none pl-3 pr-8 h-9 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 text-xs font-bold cursor-pointer">
                                <option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="50">50</option>
                            </select>
                            <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner CTA condicional para seleccionar Pago -->
            @if(empty($paymentFilter))
                <div class="mb-6 p-6 rounded-2xl border border-yellow-250 bg-yellow-50/65 dark:bg-yellow-950/20 dark:border-yellow-800/50 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/40 text-yellow-650 dark:text-yellow-400 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-gray-900 dark:text-white text-base">⚠️ Seleccione su forma de pago</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Para ver los precios correctos y habilitar la compra, seleccione si pagará a Crédito o de Contado.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 flex-shrink-0">
                        <button wire:click="$set('paymentFilter', 'contado')" 
                                style="background-color: #10b981 !important; color: #ffffff !important;"
                                class="px-5 py-2.5 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition-all active:scale-95 text-sm">
                            Pagar de Contado
                        </button>
                        <button wire:click="$set('paymentFilter', 'credito')" 
                                style="background-color: #d97706 !important; color: #ffffff !important;"
                                class="px-5 py-2.5 hover:bg-yellow-700 text-white font-bold rounded-xl shadow-md transition-all active:scale-95 text-sm">
                            Pagar a Crédito
                        </button>
                    </div>
                </div>
            @endif

            <!-- LISTA DE PRODUCTOS -->
            <div x-show="viewMode === 'list'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Encabezado -->
                <div class="hidden sm:flex items-center gap-4 px-5 py-3 border-b border-gray-100 dark:border-gray-700/60 text-[10px] font-bold uppercase tracking-[.08em] text-gray-400 dark:text-gray-500">
                    <div class="w-9 flex-shrink-0"></div>
                    <div class="w-11 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">Producto</div>
                    <div class="w-16 text-center flex-shrink-0">Disp.</div>
                    <div class="w-24 text-right flex-shrink-0">Contado</div>
                    <div class="w-24 text-right flex-shrink-0">Crédito</div>
                </div>

                <!-- Filas de productos -->
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($products as $product)
                        @php
                            $prices = $product->all_prices;
                            $taxPercentage = 0;
                            if ($product->tax) {
                                $taxPercentage = $product->tax->percentage / 100;
                            }
                            
                            $basePriceRecord = $product->invValues
                                ->where('type', 'precio')
                                ->where('label', 'Precio Base')
                                ->sortByDesc('date')
                                ->sortByDesc('created_at')
                                ->first();
                            $basePrice = $basePriceRecord ? (float)$basePriceRecord->values : 0.0;

                            if ($settingsConfigured && $cashPricelist && $basePrice > 0) {
                                $priceWithoutIva = $basePrice * $cashPricelist->value;
                                $priceCash = round($priceWithoutIva * (1 + $taxPercentage), 2);
                            } else {
                                $priceCash = 0;
                            }

                            if ($settingsConfigured && $creditPricelist && $basePrice > 0) {
                                $priceWithoutIva = $basePrice * $creditPricelist->value;
                                $priceCredit = round($priceWithoutIva * (1 + $taxPercentage), 2);
                            } else {
                                $priceCredit = null;
                            }
                            
                            $realStock = ($product->total_stock ?? 0) - ($product->reserved_stock ?? 0);
                            $visibleStock = round($realStock * 0.30);
                            if ($realStock > 100) { $visibleStock = 30; }
                            if ($visibleStock < 0) { $visibleStock = 0; }
                            
                            $imageUrl = $product->getPrincipalThumbnailUrl('COMERCIAL');
                            
                            $scale1Qty = $product->dimensions ? $product->dimensions->scale_1_qty : 0;
                            $scale1Discount = $product->dimensions ? $product->dimensions->scale_1_discount : 0;
                            $scale2Qty = $product->dimensions ? $product->dimensions->scale_2_qty : 0;
                            $scale2Discount = $product->dimensions ? $product->dimensions->scale_2_discount : 0;
                            $boxQty = $product->dimensions ? $product->dimensions->quntityxbox : 0;
                            $boxDiscount = $product->dimensions ? $product->dimensions->box_discount : 0;
                        @endphp
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-x-3 gap-y-2.5 sm:gap-4 px-4 sm:px-5 py-3 sm:py-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors group"
                             x-data="{ showDetail: false }">

                            <!-- Menú de acciones (columna izquierda) -->
                            <div class="hidden sm:flex w-8 flex-shrink-0 relative items-center justify-start" x-data="{ open: false }">
                                <button @click.stop="open = !open" @click.away="open = false" title="Ver opciones disponibles para este producto" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1" style="display: none;">
                                    <button @click="$dispatch('openImageModal', { productId: {{ $product->id }}, context: 'COMERCIAL' }); open = false" title="Abrir galería de imágenes y fichas gráficas" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Imagen
                                    </button>
                                    <button @click="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); open = false" title="Ver comentarios, notas técnicas y observaciones de este producto" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Observaciones
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Miniatura -->
                            <div class="w-11 h-11 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 cursor-pointer hover:ring-2 hover:ring-indigo-400 transition-all"
                                 @click="$dispatch('openImageModal', { productId: {{ $product->id }}, context: 'COMERCIAL' })">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                            </div>

                            <!-- Nombre y código -->
                            <div class="flex-1 basis-[calc(100%-6rem)] sm:basis-0 min-w-0 flex flex-col justify-center">
                                <div class="text-[13px] sm:text-[13.5px] font-semibold text-gray-900 dark:text-white leading-snug line-clamp-2">{{ $product->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($product->sku)
                                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500">SKU {{ $product->sku }}</span>
                                    @endif
                                    {{-- stock inline en móvil --}}
                                    <span class="sm:hidden inline-flex items-center gap-1 text-[10px] font-bold {{ $visibleStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $visibleStock > 0 ? $visibleStock : 'Agotado' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Stock disponible (columna, solo desktop) -->
                            <div class="hidden sm:flex w-16 flex-shrink-0 justify-center">
                                @if($visibleStock > 0)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $visibleStock }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-gray-400 dark:text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Agotado
                                    </span>
                                @endif
                            </div>

                            <!-- Precio Contado -->
                            <div class="w-[calc(50%-1.5rem)] sm:w-24 flex-shrink-0 flex justify-end">
                                @if($priceCash > 0)
                                    @if($paymentFilter === 'contado')
                                        <button
                                            @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ $product->sku }}', '{{ addslashes($product->name) }}', {{ $priceCash }}, {{ $priceCredit ?: 0 }}, {{ $scale1Qty ?: 0 }}, {{ $scale1Discount ?: 0 }}, {{ $scale2Qty ?: 0 }}, {{ $scale2Discount ?: 0 }}, {{ $boxQty ?: 0 }}, {{ $boxDiscount ?: 0 }})"
                                            class="inline-flex items-center gap-1.5 py-1.5 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white active:scale-95 transition-all"
                                            title="Agregar a tu pedido">
                                            <span class="text-[12px] font-extrabold">${{ number_format($priceCash, 0, ',', '.') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                                        </button>
                                    @else
                                        <span class="text-[12px] font-bold text-gray-400 dark:text-gray-500">${{ number_format($priceCash, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-gray-300 dark:text-gray-600">N/A</span>
                                @endif
                            </div>

                            <!-- Precio Crédito -->
                            <div class="w-[calc(50%-1.5rem)] sm:w-24 flex-shrink-0 flex justify-end">
                                @if($priceCredit && $priceCredit > 0)
                                    @if($paymentFilter === 'credito')
                                        <button
                                            @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ $product->sku }}', '{{ addslashes($product->name) }}', {{ $priceCash }}, {{ $priceCredit ?: 0 }}, {{ $scale1Qty ?: 0 }}, {{ $scale1Discount ?: 0 }}, {{ $scale2Qty ?: 0 }}, {{ $scale2Discount ?: 0 }}, {{ $boxQty ?: 0 }}, {{ $boxDiscount ?: 0 }})"
                                            class="inline-flex items-center gap-1.5 py-1.5 px-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white active:scale-95 transition-all"
                                            title="Agregar a tu pedido">
                                            <span class="text-[12px] font-extrabold">${{ number_format($priceCredit, 0, ',', '.') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                                        </button>
                                    @else
                                        <span class="text-[12px] font-bold text-gray-400 dark:text-gray-500">${{ number_format($priceCredit, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-gray-300 dark:text-gray-600">N/A</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No se encontraron productos</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Intenta con otros términos de búsqueda o categorías</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- VISTA GRID (CUADRÍCULA DE PRODUCTOS) -->
            <div x-show="viewMode === 'grid'" style="display: none;" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @forelse($products as $product)
                    @php
                        $prices = $product->all_prices;
                        $taxPercentage = 0;
                        if ($product->tax) {
                            $taxPercentage = $product->tax->percentage / 100;
                        }
                        
                        $basePriceRecord = $product->invValues
                            ->where('type', 'precio')
                            ->where('label', 'Precio Base')
                            ->sortByDesc('date')
                            ->sortByDesc('created_at')
                            ->first();
                        $basePrice = $basePriceRecord ? (float)$basePriceRecord->values : 0.0;

                        if ($settingsConfigured && $cashPricelist && $basePrice > 0) {
                            $priceWithoutIva = $basePrice * $cashPricelist->value;
                            $priceCash = round($priceWithoutIva * (1 + $taxPercentage), 2);
                        } else {
                            $priceCash = 0;
                        }

                        if ($settingsConfigured && $creditPricelist && $basePrice > 0) {
                            $priceWithoutIva = $basePrice * $creditPricelist->value;
                            $priceCredit = round($priceWithoutIva * (1 + $taxPercentage), 2);
                        } else {
                            $priceCredit = null;
                        }
                        
                        $realStock = ($product->total_stock ?? 0) - ($product->reserved_stock ?? 0);
                        $visibleStock = round($realStock * 0.30);
                        if ($realStock > 100) { $visibleStock = 30; }
                        if ($visibleStock < 0) { $visibleStock = 0; }
                        
                        $imageUrl = $product->getPrincipalThumbnailUrl('COMERCIAL');
                            
                        $scale1Qty = $product->dimensions ? $product->dimensions->scale_1_qty : 0;
                        $scale1Discount = $product->dimensions ? $product->dimensions->scale_1_discount : 0;
                        $scale2Qty = $product->dimensions ? $product->dimensions->scale_2_qty : 0;
                        $scale2Discount = $product->dimensions ? $product->dimensions->scale_2_discount : 0;
                        $boxQty = $product->dimensions ? $product->dimensions->quntityxbox : 0;
                        $boxDiscount = $product->dimensions ? $product->dimensions->box_discount : 0;
                    @endphp
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-500 flex flex-col">
                        
                        <!-- Menú de opciones (Tres puntos) -->
                        <div class="absolute top-1 left-1 z-10" x-data="{ open: false }">
                            <button @click.stop="open = !open" @click.away="open = false" title="Ver opciones disponibles para este producto" class="bg-white/95 dark:bg-gray-800/95 rounded-lg shadow-md p-1.5 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-lg transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-20 py-1" style="display: none;">
                                <button @click="$dispatch('openImageModal', { productId: {{ $product->id }}, context: 'COMERCIAL' }); open = false" title="Abrir galería de imágenes y fichas gráficas" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Imagen
                                </button>
                                <button @click="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); open = false" title="Ver comentarios, notas técnicas y observaciones de este producto" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Observaciones
                                </button>
                            </div>
                        </div>

                        <!-- Imagen -->
                        <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                             @click="$dispatch('openImageModal', { productId: {{ $product->id }}, context: 'COMERCIAL' })">
                            <img class="w-full h-full object-cover" src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
                        </div>
                        <!-- Info -->
                        <div class="p-2.5 flex flex-col flex-1 justify-between">
                            <div>
                                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase mb-1 line-clamp-2 text-center min-h-[2rem]">{{ $product->name }}</div>
                                <div class="flex items-center justify-center mb-1">
                                    @if($product->sku)
                                        <span class="text-[9px] font-mono font-bold text-indigo-500 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded border border-indigo-200 dark:border-indigo-700">SKU: {{ $product->sku }}</span>
                                    @endif
                                </div>
                                <div class="mb-2 flex justify-center">
                                    @if($visibleStock > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-medium bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-700">Disp: {{ $visibleStock }}</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-medium bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-100 dark:border-red-700">Agotado</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Precios -->
                            <div class="grid grid-cols-2 gap-1 mt-auto">
                                <div>
                                    @if($priceCash > 0)
                                        @if($paymentFilter === 'contado')
                                            <button @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ $product->sku }}', '{{ addslashes($product->name) }}', {{ $priceCash }}, {{ $priceCredit ?: 0 }}, {{ $scale1Qty ?: 0 }}, {{ $scale1Discount ?: 0 }}, {{ $scale2Qty ?: 0 }}, {{ $scale2Discount ?: 0 }}, {{ $boxQty ?: 0 }}, {{ $boxDiscount ?: 0 }})"
                                                    class="w-full py-1 px-1 rounded-md border border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-900/10 hover:scale-[1.02] active:scale-95 transition-all text-center">
                                                <span class="block text-[7px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Contado</span>
                                                <span class="block text-[11px] font-black text-emerald-700 dark:text-emerald-300">$ {{ number_format($priceCash, 0, ',', '.') }}</span>
                                            </button>
                                        @else
                                            <div class="w-full py-1 px-1 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-40 cursor-not-allowed text-center">
                                                <span class="block text-[7px] font-bold text-gray-400 uppercase">Contado</span>
                                                <span class="block text-[11px] font-black text-gray-400 dark:text-gray-500">$ {{ number_format($priceCash, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center text-[10px] text-gray-400 py-1">N/A</div>
                                    @endif
                                </div>
                                <div>
                                    @if($priceCredit && $priceCredit > 0)
                                        @if($paymentFilter === 'credito')
                                            <button @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ $product->sku }}', '{{ addslashes($product->name) }}', {{ $priceCash }}, {{ $priceCredit ?: 0 }}, {{ $scale1Qty ?: 0 }}, {{ $scale1Discount ?: 0 }}, {{ $scale2Qty ?: 0 }}, {{ $scale2Discount ?: 0 }}, {{ $boxQty ?: 0 }}, {{ $boxDiscount ?: 0 }})"
                                                    class="w-full py-1 px-1 rounded-md border border-yellow-500/30 bg-yellow-50/50 dark:bg-yellow-900/10 hover:scale-[1.02] active:scale-95 transition-all text-center">
                                                <span class="block text-[7px] font-bold text-yellow-600 dark:text-yellow-400 uppercase">Crédito</span>
                                                <span class="block text-[11px] font-black text-yellow-700 dark:text-yellow-300">$ {{ number_format($priceCredit, 0, ',', '.') }}</span>
                                            </button>
                                        @else
                                            <div class="w-full py-1 px-1 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 opacity-40 cursor-not-allowed text-center">
                                                <span class="block text-[7px] font-bold text-gray-400 uppercase">Crédito</span>
                                                <span class="block text-[11px] font-black text-gray-400 dark:text-gray-500">$ {{ number_format($priceCredit, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center text-[10px] text-gray-400 py-1">N/A</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-12 text-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No se encontraron productos</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Intenta con otros términos de búsqueda o categorías</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>
            </div>{{-- /max-w-5xl --}}
        </div>

        <!-- SIDEBAR (estilo cotizador) -->
        <div class="hidden lg:block w-[28rem] flex-shrink-0 sticky top-[112px] h-[calc(100vh-112px)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 overflow-y-auto self-start">
            <div class="p-5">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span x-text="cart.length + (cart.length === 1 ? ' producto' : ' productos') + ' (' + totalItems + (totalItems === 1 ? ' unidad' : ' unidades') + ') seleccionados'">0 Productos seleccionados</span>
                    </h2>
                </div>

                <!-- Items del carrito -->
                <div class="space-y-2 mb-4 max-h-[40vh] overflow-y-auto pr-1">
                    <template x-if="cart.length === 0">
                        <div class="text-center py-10 text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-xs font-semibold">Agregar items</p>
                            <p class="text-[10px] mt-1 max-w-[200px] mx-auto">Selecciona productos de la lista para agregarlos a tu cotización</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                            <!-- Fila Superior: Código - Nombre y Botón Eliminar -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold text-gray-900 dark:text-white line-clamp-2" x-text="item.code + ' - ' + item.name"></p>
                                </div>
                                <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0 p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Fila Intermedia: Recuadros interactivos de escala de precios por volumen -->
                             <div class="flex flex-wrap items-center gap-1 mt-2">
                                 <!-- Rango Base (Precio de Lista Cliente) -->
                                 <div class="px-1.5 py-0.5 text-[9px] font-bold rounded transition-all"
                                      :class="(item.scale1Qty === 0 || item.qty < item.scale1Qty) && (item.scale2Qty === 0 || item.qty < item.scale2Qty) && (item.boxQty === 0 || item.qty < item.boxQty)
                                              ? 'text-white bg-indigo-600 border border-indigo-600 shadow-sm scale-105' 
                                              : 'text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-100/50 dark:bg-gray-800/10 opacity-60'"
                                      x-text="'$' + Math.round($wire.paymentFilter === 'credito' ? item.priceCredit : item.priceCash).toLocaleString('es-CO')">
                                 </div>
                                 
                                 <!-- Escala 1 (+20 unidades) -->
                                 <template x-if="item.scale1Qty > 0">
                                     <div class="px-1.5 py-0.5 text-[9px] font-bold rounded transition-all"
                                          :class="item.qty >= item.scale1Qty && (item.scale2Qty === 0 || item.qty < item.scale2Qty) && (item.boxQty === 0 || item.qty < item.boxQty)
                                                  ? 'text-white bg-indigo-600 border border-indigo-600 shadow-sm scale-105' 
                                                  : 'text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-100/50 dark:bg-gray-800/10 opacity-60'"
                                          x-text="'+' + item.scale1Qty + ' $' + Math.round(($wire.paymentFilter === 'credito' ? item.priceCredit : item.priceCash) * (1 - (item.scale1Discount / 100))).toLocaleString('es-CO')">
                                     </div>
                                 </template>

                                 <!-- Escala 2 (+40 unidades) -->
                                 <template x-if="item.scale2Qty > 0">
                                     <div class="px-1.5 py-0.5 text-[9px] font-bold rounded transition-all"
                                          :class="item.qty >= item.scale2Qty && (item.boxQty === 0 || item.qty < item.boxQty)
                                                  ? 'text-white bg-indigo-600 border border-indigo-600 shadow-sm scale-105' 
                                                  : 'text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-100/50 dark:bg-gray-800/10 opacity-60'"
                                          x-text="'+' + item.scale2Qty + ' $' + Math.round(($wire.paymentFilter === 'credito' ? item.priceCredit : item.priceCash) * (1 - (item.scale2Discount / 100))).toLocaleString('es-CO')">
                                     </div>
                                 </template>

                                 <!-- Caja Cerrada -->
                                 <template x-if="item.boxQty > 0">
                                     <div class="px-1.5 py-0.5 text-[9px] font-bold rounded transition-all"
                                          :class="item.qty >= item.boxQty
                                                  ? 'text-white bg-indigo-600 border border-indigo-600 shadow-sm scale-105' 
                                                  : 'text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-100/50 dark:bg-gray-800/10 opacity-60'"
                                          x-text="'Caja x' + item.boxQty + ' $' + Math.round(($wire.paymentFilter === 'credito' ? item.priceCredit : item.priceCash) * (1 - (item.boxDiscount / 100))).toLocaleString('es-CO')">
                                     </div>
                                 </template>
                             </div>

                             <!-- Fila Inferior: Controles de cantidad, P. Unitario y Total de fila -->
                             <div class="flex items-center justify-between mt-2.5">
                                 <div class="flex items-center gap-0.5 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-600">
                                     <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-gray-800 dark:hover:text-white text-sm font-bold">−</button>
                                     <span class="w-7 text-center text-xs font-bold text-gray-900 dark:text-white" x-text="item.qty"></span>
                                     <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-gray-800 dark:hover:text-white text-sm font-bold">+</button>
                                 </div>

                                 <div class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400"
                                      x-text="'P. Unitario $' + Math.round(getItemPrice(item)).toLocaleString('es-CO')">
                                 </div>

                                 <span class="text-sm font-extrabold text-gray-800 dark:text-gray-200" 
                                       x-text="'$' + (Math.round(getItemPrice(item)) * item.qty).toLocaleString('es-CO')"></span>
                             </div>
                        </div>
                    </template>
                </div>

                <!-- Totales y acciones -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400">Total Estimado</span>
                        <span class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400" x-text="'$' + total.toLocaleString('es-CO')"></span>
                    </div>

                    <!-- Dirección / Sucursales -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-gray-700 dark:text-gray-300">Dirección de Envío / Sucursal</label>
                            @if(auth()->user() && auth()->user()->tenant_company_id)
                                <button type="button" wire:click="$set('showWarehouseModal', true)" title="Administrar, editar o crear nuevas sucursales y direcciones de despacho de tu empresa" class="text-[9px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 underline">
                                    Gestionar Sucursales
                                </button>
                            @endif
                        </div>
                        
                        @if(auth()->user() && auth()->user()->tenant_company_id && count($branches) > 0)
                            <select wire:model.live="selectedBranchId" wire:change="changeBranch"
                                title="Selecciona una sucursal registrada para cargar su dirección de despacho automáticamente"
                                class="w-full px-3 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white mb-2 cursor-pointer font-medium">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch['id'] }}">
                                        {{ $branch['name'] }} · {{ $branch['address'] }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        
                        <input type="text" wire:model="shippingAddress" placeholder="Ej. Calle 45 Sur #78 B 16 - Bogotá" 
                            title="Dirección final de envío donde se despachará este pedido"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                    </div>

                    <!-- Comprobante -->
                    <div title="Adjunta el comprobante de pago de tu transferencia para proceder con la verificación de tu pedido">
                        <label class="text-[10px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Comprobante de Pago (Obligatorio)</label>
                        <div class="border-2 border-dashed {{ $errors->has('proofPaymentFile') || !$proofPaymentFile ? 'border-red-400 bg-red-50/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50' }} rounded-lg p-3 text-center cursor-pointer hover:border-indigo-400 transition-colors"
                             title="Haz clic o arrastra tu archivo aquí (formatos PDF, JPG, PNG de hasta 5MB)">
                            <input type="file" wire:model="proofPaymentFile" class="hidden" id="receipt-upload">
                            <label for="receipt-upload" class="cursor-pointer block">
                                @if ($proofPaymentFile)
                                    <svg class="w-6 h-6 text-green-500 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-[10px] font-bold text-green-600 dark:text-green-400">Comprobante cargado:</span>
                                    <span class="text-[8px] text-gray-500 block truncate">{{ $proofPaymentFile->getClientOriginalName() }}</span>
                                @else
                                    <svg class="w-6 h-6 text-red-400 mx-auto mb-1 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">Arrastrar o seleccionar comprobante</span>
                                    <span class="text-[8px] text-red-500 block font-semibold">¡Archivo Obligatorio! (Max 5MB)</span>
                                @endif
                            </label>
                        </div>
                    </div>

                    <!-- Botón enviar -->
                    <button 
                        :disabled="cart.length === 0"
                        title="Enviar el pedido actual y su respectivo comprobante de pago para la verificación de un auxiliar comercial"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-sm text-sm flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="submitOrder"
                        @click="$wire.submitOrder(cart.map(item => ({ id: item.id, code: item.code, name: item.name, price: $wire.paymentFilter === 'credito' ? item.priceCredit : item.priceCash, label: $wire.paymentFilter === 'credito' ? 'Crédito' : 'Contado', qty: item.qty }))).then(res => { if (res) { cart = []; } })"
                    >
                        <!-- Icono dinámico -->
                        <span wire:loading.remove wire:target="submitOrder">🚀</span>
                        <svg wire:loading wire:target="submitOrder" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        <!-- Texto dinámico -->
                        <span wire:loading.remove wire:target="submitOrder">Enviar Pedido para Verificación</span>
                        <span wire:loading wire:target="submitOrder">Guardando Pedido...</span>
                    </button>
                    <p class="text-[9px] text-gray-400 text-center leading-relaxed">
                        Al enviar el pedido, queda en estado <strong>Por verificar</strong> hasta que un auxiliar comercial apruebe y genere la OP correspondiente.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Imágenes del Producto -->
    <livewire:tenant.components.product-image-modal />

    <!-- Modal de Observaciones -->
    <livewire:tenant.items.item-observation />

    <!-- Modal de Gestión de Sucursales Exclusivo del Portal -->
    @if($showWarehouseModal && auth()->user() && auth()->user()->tenant_company_id)
        @livewire('tenant.portal.portal-warehouse-modal', [
            'companyId' => auth()->user()->tenant_company_id
        ], key('portal-warehouse-modal-' . auth()->user()->tenant_company_id))
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                Swal.fire({
                    title: data.title || '',
                    text: data.text || '',
                    icon: data.icon || 'info',
                    confirmButtonColor: '#4f46e5'
                });
            });
        });
    </script>
</div>
