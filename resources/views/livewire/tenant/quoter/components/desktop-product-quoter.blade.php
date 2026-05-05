{{-- Establecer el header --}}
@php
$header = 'Seleccionar productos';
@endphp

<div>
    <div class="flex {{ $hideQuoter ? 'flex-col' : 'pr-96' }}">
        <!-- Área principal de productos -->
        <div class="flex-1 p-6">
            <!-- Cabecera con Botón de regresar y Switch de Modo Copia -->
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <a href="{{ route('tenant.quoter') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium" wire:navigate.hover>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Regresar cotizaciones
                </a>

                @include('livewire.tenant.components.copy-mode-switch')

                @if(!$hideQuoter)
                    @include('livewire.tenant.parameters.dynamic-buttons', ['buttons' => $this->dynamicButtons])
                @endif
            </div>

            <!-- Barra de búsqueda y filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <!-- Búsqueda + botón producto genérico -->
                        <div class="flex-1 flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input wire:model.live.debounce.300ms="search"
                                    type="text"
                                    placeholder="Buscar productos..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <!-- Dropdown de Acciones Rápidas -->
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button @click="open = !open"
                                    type="button"
                                    class="flex items-center gap-1.5 px-3 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-all duration-200 whitespace-nowrap shadow-sm active:scale-95 shadow-indigo-500/20">
                                    <span>Opciones</span>
                                    <svg class="w-4 h-4 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Menú desplegable -->
                                <div x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    @click.away="open = false"
                                    x-cloak
                                    class="absolute left-0 mt-2 w-56 rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none z-[60] border border-gray-100 dark:border-gray-700 py-1 overflow-hidden">
                                     @if(!$hideQuoter)
                                     <!-- Producto generico -->
                                    <button @click="open = false; $wire.set('showGenericProductModal', true)"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors group">
                                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col items-start leading-tight">
                                            <span class="font-semibold">Producto Genérico</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Crear item sin catálogo</span>
                                        </div>
                                    </button>
                                    @endif
                                    <!-- Calcular Potencia -->
                                    <button @click="open = false; $dispatch('openPowerCalculator')"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors group">
                                        <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col items-start leading-tight">
                                            <span class="font-semibold">Calcular Potencia</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Cálculo de amperaje y watts</span>
                                        </div>
                                    </button>

                                    <!-- Detalles de Corte -->
                                    <button @click="open = false; $dispatch('openCutDetails')"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors group border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758l5.758-5.758"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col items-start leading-tight">
                                            <span class="font-semibold">Detalles de Corte</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Planificación de cortes de ítems</span>
                                        </div>
                                    </button>

                                    <!-- Otras opciones futuras pueden ir aquí -->

                                    

                                </div>
                            </div>
                        </div>

                        <!-- Filtro de Categorías -->
                        <div class="flex items-center gap-3">
                            <!-- Filtro de Categorías -->
                            <select wire:model.live="selectedCategory"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Todas las categorías</option>
                                @foreach($this->getCategories() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Controles -->
                        <div class="flex items-center gap-3">
                            <!-- Alternar modo de vista -->
                            <div x-data="{}"
                                x-init="
                                    const saved = localStorage.getItem('quoter_view_mode');
                                    if (saved && saved !== '{{ $viewMode }}') {
                                        $wire.set('viewMode', saved);
                                    }
                                ">
                                <button wire:click="toggleViewMode"
                                        @click="localStorage.setItem('quoter_view_mode', '{{ $viewMode }}' === 'grid' ? 'table' : 'grid')"
                                        class="flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    @if($viewMode === 'grid')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm">Tabla</span>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                        <span class="text-sm">Grid</span>
                                    @endif
                                </button>
                            </div>

                            <!-- Registros por página -->
                            <select wire:model.live="perPage"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="12">12 por página</option>
                                <option value="24">24 por página</option>
                                <option value="48">48 por página</option>
                            </select>
                    </div>
                </div>
            </div>
        </div>

        @if($viewMode === 'grid')
                <!-- Modo Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    @forelse($products as $product)
                        @php
                            $quantity = $this->getProductQuantity($product->id);
                            $isSelected = $quantity > 0;
                            $imgContext = $hideQuoter ? 'BODEGA' : 'COMERCIAL';
                            $imgUrl = $product->getPrincipalImageUrl($imgContext);
                            $hasImage = $imgUrl !== asset('images/placeholder-item.png');
                        @endphp

                        <div @if(!$hideQuoter && $isSelected) wire:click="increaseQuantity({{ $product->id }})" @endif
                            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all duration-200
                                        hover:shadow-lg hover:shadow-indigo-100 dark:hover:shadow-gray-900/30 hover:-translate-y-1 hover:border-indigo-300 dark:hover:border-indigo-500
                                        {{ $isSelected && !$hideQuoter ? 'ring-2 ring-indigo-500 shadow-lg border-indigo-300 dark:border-indigo-500 cursor-pointer' : '' }}">

                            <!-- Menú 3 puntos (Grid) -->
                            <div x-data="{ open: false }" class="absolute top-0 left-0 z-10">
                                <button @click.stop="open = !open"
                                    class="p-2 bg-white/95 dark:bg-gray-800/95 text-gray-600 dark:text-gray-300 rounded-br-xl shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" @click.stop x-cloak
                                    class="absolute left-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1">
                                    <button @click.stop="$dispatch('openTicketModal', { productId: {{ $product->id }} }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                        Solicitud Soporte
                                    </button>
                                    <button @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $imgContext }}' }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Imagen
                                    </button>

                                     <button @click.stop="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Observaciones
                                    </button>

                                    @if($product->accessories_count > 0)
                                    <button @click.stop="$dispatch('openAccessoriesModal', { itemId: {{ $product->id }} }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        Accesorios
                                    </button>
                                    @endif
 
                                     <button @click.stop="$dispatch('openCalculationModal', { productId: {{ $product->id }} }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" />
                                        </svg>
                                        Cálculos
                                    </button>
 
                                     <button @click.stop="$dispatch('openConfirmationModal', { productId: {{ $product->id }} }); open = false"
                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Sol. Confirmación
                                    </button>
                                </div>
                            </div>

                            <!-- Contador en la esquina superior derecha -->
                            @if($quantity > 0 && !$hideQuoter)
                            <div class="absolute top-2 right-2 bg-indigo-600 dark:bg-indigo-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center z-10">
                                {{ $quantity }}
                            </div>
                            @endif

                            <!-- Imagen del producto -->
                            <div @click.stop="{{ $isCopyMode ? "copyImageToClipboard('" . $imgUrl . "')" : "\$dispatch('openImageModal', { productId: " . $product->id . ", context: '" . $imgContext . "' })" }}" 
                                 class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center p-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                @if($hasImage)
                                <img class="w-full h-full object-cover rounded-lg"
                                    src="{{ $imgUrl }}"
                                    alt="{{ $product->display_name }}">
                                @else
                                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-400 dark:text-gray-500">
                                        {{ strtoupper(substr($product->name, 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            <div class="text-center flex-1">
                                <!-- Información del producto -->
                                <div class="p-3 flex flex-col h-full">
                                    <!-- Nombre -->
                                    <div class="text-sm font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 h-10 flex items-center justify-center">
                                        {{ $product->display_name }}
                                    </div>
                                    <!-- SKU -->
                                    <div class="text-xs text-gray-500 dark:text-gray-500 mb-2 h-4 flex items-center justify-center">
                                        @if($product->sku && trim($product->sku) !== '')
                                        SKU: {{ $product->sku }}
                                        @endif
                                    </div>

                                    <!-- Bodegas disponibles -->
                                    @if($product->store_stock_details)
                                    <div class="mb-2 px-2 h-12 overflow-y-auto custom-scrollbar">
                                        <div class="flex flex-wrap gap-1 justify-center">
                                            @foreach(explode(', ', $product->store_stock_details) as $storeDetail)
                                                @php
                                                    $parts = explode(':', $storeDetail);
                                                    $storeName = $parts[0] ?? '';
                                                    $stock = $parts[1] ?? '0';
                                                @endphp
                                                @if($storeName)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-medium
                                                    {{ $stock > 0
                                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-700'
                                                        : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-100 dark:border-red-700'
                                                    }}">
                                                    {{ $storeName }}: {{ number_format($stock, 0, ',', '.') }}
                                                </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    @if($hideQuoter)
                                        <!-- Información Logística (Modo Bodega) -->
                                        <div class="grid grid-cols-2 gap-2 mt-auto">
                                            <div class="bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg border border-gray-100 dark:border-gray-700 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-gray-500 dark:text-gray-400">Stock</span>
                                                <span class="text-xs font-black text-gray-900 dark:text-white">{{ number_format($product->stock_bodega, 0) }}</span>
                                            </div>
                                            <div class="bg-blue-50 dark:bg-blue-900/10 p-2 rounded-lg border border-blue-100 dark:border-blue-800 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-blue-500 dark:text-blue-400">Tránsito</span>
                                                <span class="text-xs font-black text-blue-700 dark:text-blue-300">{{ number_format($product->in_transit, 0) }}</span>
                                            </div>
                                            <div class="bg-orange-50 dark:bg-orange-900/10 p-2 rounded-lg border border-orange-100 dark:border-orange-800 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-orange-500 dark:text-orange-400">Reserva</span>
                                                <span class="text-xs font-black text-orange-700 dark:text-orange-300">{{ number_format($product->reserved, 0) }}</span>
                                            </div>
                                            <div class="bg-indigo-50 dark:bg-indigo-900/10 p-2 rounded-lg border border-indigo-100 dark:border-indigo-800 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-indigo-500 dark:text-indigo-400">Picking</span>
                                                <span class="text-[10px] font-black text-indigo-700 dark:text-indigo-300 truncate w-full text-center">{{ $product->picking }}</span>
                                            </div>
                                            <div class="bg-red-50 dark:bg-red-900/10 p-2 rounded-lg border border-red-100 dark:border-red-800 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-red-500 dark:text-red-400">Mínimo</span>
                                                <span class="text-xs font-black text-red-700 dark:text-red-300">{{ number_format($product->stock_min, 0) }}</span>
                                            </div>
                                            <div class="bg-emerald-50 dark:bg-emerald-900/10 p-2 rounded-lg border border-emerald-100 dark:border-emerald-800 flex flex-col items-center">
                                                <span class="text-[9px] uppercase font-bold text-emerald-500 dark:text-emerald-400">Máximo</span>
                                                <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">{{ number_format($product->stock_max, 0) }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Precios (Modo Comercial) -->
                                        @php
                                        $allPrices = $product->all_prices;
                                        @endphp
                                        @if(!empty($allPrices))
                                        <div class="mb-2 grid grid-cols-2 gap-1 mt-auto">
                                            @foreach($allPrices as $label => $price)
                                            @php
                                                $isThisPriceSelected = $this->isPriceSelected($product->id, $label);
                                                $colorClasses = match($label) {
                                                    'Precio Regular' => [
                                                        'border' => $isThisPriceSelected ? 'border-blue-500' : 'border-red-500/30',
                                                        'bg' => $isThisPriceSelected ? 'bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500/50' : 'bg-red-50/50 dark:bg-red-900/10 active:bg-red-100 dark:active:bg-red-900/30',
                                                        'label_text' => $isThisPriceSelected ? 'text-blue-700 dark:text-blue-300' : 'text-red-600 dark:text-red-400 group-hover:text-red-700',
                                                        'price_text' => $isThisPriceSelected ? 'text-blue-800 dark:text-blue-200' : 'text-red-700 dark:text-red-300',
                                                        'spinner' => 'text-red-600 dark:text-red-400'
                                                    ],
                                                    'Precio Crédito' => [
                                                        'border' => $isThisPriceSelected ? 'border-blue-500' : 'border-yellow-500/30',
                                                        'bg' => $isThisPriceSelected ? 'bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500/50' : 'bg-yellow-50/50 dark:bg-yellow-900/10 active:bg-yellow-100 dark:active:bg-yellow-900/30',
                                                        'label_text' => $isThisPriceSelected ? 'text-blue-700 dark:text-blue-300' : 'text-yellow-600 dark:text-yellow-400 group-hover:text-yellow-700',
                                                        'price_text' => $isThisPriceSelected ? 'text-blue-800 dark:text-blue-200' : 'text-yellow-700 dark:text-yellow-300',
                                                        'spinner' => 'text-yellow-600 dark:text-yellow-400'
                                                    ],
                                                    default => [
                                                        'border' => $isThisPriceSelected ? 'border-blue-500' : 'border-emerald-500/30',
                                                        'bg' => $isThisPriceSelected ? 'bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500/50' : 'bg-emerald-50/50 dark:bg-emerald-900/10 active:bg-emerald-100 dark:active:bg-emerald-900/30',
                                                        'label_text' => $isThisPriceSelected ? 'text-blue-700 dark:text-blue-300' : 'text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700',
                                                        'price_text' => $isThisPriceSelected ? 'text-blue-800 dark:text-blue-200' : 'text-emerald-700 dark:text-emerald-300',
                                                        'spinner' => 'text-emerald-600 dark:text-emerald-400'
                                                    ]
                                                };
                                            @endphp
                                            <button
                                                @if($isCopyMode)
                                                    @click.stop="copyProductToClipboard('{{ $product->sku }}', {{ $price }}, '{{ addslashes($product->display_name) }}', '{{ $product->id }}')"
                                                @else
                                                    wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                                @endif
                                                class="relative w-full py-1 px-2 rounded-lg border transition-colors overflow-hidden group {{ $colorClasses['border'] }} {{ $colorClasses['bg'] }}">

                                                <!-- Contenido Normal -->
                                                <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')">
                                                    <div class="text-[9px] uppercase font-bold truncate transition-colors {{ $colorClasses['label_text'] }}">
                                                        {{ $label }}
                                                        @if($isThisPriceSelected)
                                                            <span class="ml-1">✓</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[12px] font-black {{ $colorClasses['price_text'] }}">
                                                        ${{ number_format($price) }}
                                                    </div>
                                                </div>

                                                <!-- Spinner de Carga -->
                                                <div wire:loading wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" class="flex items-center justify-center py-1">
                                                    <svg class="animate-spin h-4 w-4 {{ $colorClasses['spinner'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                            </button>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="font-bold text-lg text-gray-400 dark:text-gray-500 mb-1 mt-auto text-center py-4">
                                            Sin precio
                                        </div>
                                        @endif
                                    @endif

                                    <!-- Overlay de selección -->
                                    @if($isSelected && !$hideQuoter)
                                    <div class="absolute inset-0 bg-indigo-500 bg-opacity-10 dark:bg-indigo-400 dark:bg-opacity-10 pointer-events-none"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay productos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($search)
                                    No se encontraron productos que coincidan con "{{ $search }}".
                                    @else
                                    No hay productos disponibles en este momento.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            @else
                <!-- Modo Tabla -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto overflow-y-visible min-h-[300px]">
                        <table class="w-full overflow-visible">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Imagen</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código</th>
                                    @if($hideQuoter)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Existencias</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tránsito</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reservas</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cant. x caja</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Picking</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Maximo</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Minimo</th>
                                    @else
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cant.</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lista</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">3%</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">5%</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">7%</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Regular</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Crédito</th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 overflow-visible">
                                @forelse($products as $product)
                                    @php
                                        $quantity = $this->getProductQuantity($product->id);
                                        $isSelected = $quantity > 0;
                                        $allPrices = $product->all_prices;
                                    @endphp

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                        <!-- Imagen -->
                                        <td class="px-4 py-4 text-center">
                                            @php
                                                $imgContext = $hideQuoter ? 'BODEGA' : 'COMERCIAL';
                                                $imgUrl = $product->getPrincipalImageUrl($imgContext);
                                                $hasImage = $product->getPrincipalImageUrl($imgContext) !== asset('images/placeholder-item.png');
                                            @endphp
                                            <div class="flex justify-center">
                                                @if($hasImage)
                                                    <img @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $imgContext }}' })" 
                                                        class="w-12 h-12 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                                                        src="{{ $imgUrl }}"
                                                        alt="{{ $product->display_name }}">
                                                @else
                                                    <div @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $imgContext }}' })"
                                                        class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                                        <span class="text-sm font-bold text-gray-400 dark:text-gray-500">
                                                            {{ strtoupper(substr($product->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Nombre -->
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs" title="{{ $product->display_name }}">
                                                {{ $product->display_name }}
                                            </div>
                                            @if($product->store_stock_details)
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach(explode(', ', $product->store_stock_details) as $storeDetail)
                                                        @php
                                                            $parts = explode(':', $storeDetail);
                                                            $storeName = $parts[0] ?? '';
                                                            $stock = $parts[1] ?? '0';
                                                        @endphp
                                                        @if($storeName)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                                            {{ $stock > 0
                                                                ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300'
                                                                : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300'
                                                            }}">
                                                            {{ $storeName }}: {{ number_format($stock, 0, ',', '.') }}
                                                        </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Código/SKU -->
                                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product->sku ?: 'N/A' }}
                                        </td>

                                        @if($hideQuoter)
                                            <!-- Información Logística (Modo Bodega) -->
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ number_format($product->stock_bodega, 0) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                                                    {{ number_format($product->in_transit, 0) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-sm text-orange-600 dark:text-orange-400 font-medium">
                                                    {{ number_format($product->reserved, 0) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center text-sm text-gray-600 dark:text-gray-400">
                                                {{ $product->qty_per_box }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="text-xs font-mono bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800">
                                                    {{ $product->picking }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center text-sm text-green-600 dark:text-green-400 font-medium">
                                                {{ number_format($product->stock_max, 0) }}
                                            </td>
                                            <td class="px-4 py-4 text-center text-sm text-red-600 dark:text-red-400 font-medium">
                                                {{ number_format($product->stock_min, 0) }}
                                            </td>
                                        @else
                                            <!-- Cantidad -->
                                            <td class="px-4 py-4 text-center">
                                                @if($isSelected)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 bg-indigo-600 dark:bg-indigo-500 text-white text-sm font-bold rounded-full">
                                                        {{ $quantity }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">0</span>
                                                @endif
                                            </td>
                                        @endif

                                        @if(!$hideQuoter)
                                            <!-- Precios individuales -->
                                            @php
                                                // Detectar automáticamente la estructura de precios
                                                $priceKeys = array_keys($allPrices);
                                                $pricesByType = [];

                                                // Detectar si usa estructura p1, p2, p3... o nombres descriptivos
                                                $usesNumericKeys = count(array_filter($priceKeys, fn($key) => preg_match('/^[pP]\d+$/', $key))) > 0;

                                                if ($usesNumericKeys) {
                                                    // Estructura tipo: p3, p4, P5, p6, p1, p2
                                                    $pricesByType = [
                                                        'Lista' => $allPrices['p3'] ?? null,
                                                        '3%' => $allPrices['p4'] ?? null,
                                                        '5%' => $allPrices['P5'] ?? $allPrices['p5'] ?? null,
                                                        '7%' => $allPrices['p6'] ?? $allPrices['P6'] ?? null,
                                                        'Regular' => $allPrices['p1'] ?? null,
                                                        'Crédito' => $allPrices['p2'] ?? null
                                                    ];
                                                } else {
                                                    // Estructura tipo: Lista, 3%, 5%, 7%, Regular, Crédito
                                                    $columnMapping = ['Lista', '3%', '5%', '7%', 'Regular', 'Crédito'];

                                                    foreach($columnMapping as $column) {
                                                        $pricesByType[$column] = null;

                                                        // Buscar coincidencias flexibles
                                                        foreach($allPrices as $priceKey => $priceValue) {
                                                            $keyLower = strtolower($priceKey);
                                                            $columnLower = strtolower($column);

                                                            if ($keyLower === $columnLower ||
                                                                str_contains($keyLower, $columnLower) ||
                                                                ($column === 'Regular' && str_contains($keyLower, 'regular')) ||
                                                                ($column === 'Crédito' && (str_contains($keyLower, 'crédito') || str_contains($keyLower, 'credito'))) ||
                                                                ($column === 'Lista' && str_contains($keyLower, 'lista')) ||
                                                                ($priceKey === $column)) {
                                                                    $pricesByType[$column] = $priceValue;
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @foreach($pricesByType as $priceType => $price)
                                                <td class="px-2 py-4 text-center">
                                                    @if($price)
                                                        @php
                                                            // Encontrar el key real del precio en el array original
                                                            $priceKey = null;
                                                            foreach($allPrices as $key => $value) {
                                                                if ($value == $price) {
                                                                    // Si hay múltiples precios iguales, usar el primero que coincida con el tipo
                                                                    if ($usesNumericKeys) {
                                                                        $expectedKey = match($priceType) {
                                                                            'Regular' => 'p1',
                                                                            'Crédito' => 'p2',
                                                                            'Lista' => 'p3',
                                                                            '3%' => 'p4',
                                                                            '5%' => 'P5',
                                                                            '7%' => 'p6',
                                                                            default => null
                                                                        };
                                                                        if ($key === $expectedKey || ($expectedKey === 'P5' && $key === 'p5')) {
                                                                            $priceKey = $key;
                                                                            break;
                                                                        }
                                                                    } else {
                                                                        // Para estructura descriptiva, buscar coincidencia
                                                                        $keyLower = strtolower($key);
                                                                        $typeLower = strtolower($priceType);

                                                                        if ($keyLower === $typeLower ||
                                                                            str_contains($keyLower, $typeLower) ||
                                                                            ($priceType === 'Regular' && str_contains($keyLower, 'regular')) ||
                                                                            ($priceType === 'Crédito' && (str_contains($keyLower, 'crédito') || str_contains($keyLower, 'credito'))) ||
                                                                            ($priceType === 'Lista' && str_contains($keyLower, 'lista'))) {
                                                                            $priceKey = $key;
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            // Fallback: usar el priceType como key si no se encuentra
                                                            $priceKey = $priceKey ?: $priceType;

                                                            $isThisPriceSelected = $this->isPriceSelected($product->id, $priceKey);
                                                        @endphp
                                                        <button
                                                         @if($isCopyMode)
                                                                @click.stop="copyProductToClipboard('{{ $product->sku }}', {{ $price }}, '{{ addslashes($product->display_name) }}', '{{ $product->id }}')"
                                                            @elseif(!$hideQuoter)
                                                                wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $priceKey }}')"
                                                                wire:loading.attr="disabled"
                                                                wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $priceKey }}')"
                                                            @endif
                                                            class="px-2 py-1 text-xs rounded-lg border-2 transition-colors font-medium min-w-20
                                                                {{ $isThisPriceSelected
                                                                    ? 'border-blue-500 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                                    : ($priceType === 'Regular'
                                                                        ? 'border-red-300 bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/10 dark:text-red-400 dark:border-red-800'
                                                                        : ($priceType === 'Crédito'
                                                                            ? 'border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:bg-yellow-900/10 dark:text-yellow-400 dark:border-yellow-800'
                                                                            : 'border-green-300 bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/10 dark:text-green-400 dark:border-green-800'))
                                                                }}">
                                                            <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $priceKey }}')">
                                                                <div class="text-xs font-black">
                                                                    ${{ number_format($price) }}
                                                                    @if($isThisPriceSelected) ✓ @endif
                                                                </div>
                                                            </div>
                                                            <div wire:loading wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $priceKey }}')" class="flex items-center justify-center">
                                                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    @else
                                                        <span class="text-gray-400 text-xs">N/A</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endif
                                        <td class="px-4 py-4 text-center">
                                            <div x-data="{ open: false }" class="relative inline-block">
                                                <button @click.stop="open = !open"
                                                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" @click.stop x-cloak
                                                    class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1">
                                                    <button @click.stop="$dispatch('openTicketModal', { productId: {{ $product->id }} }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                                        </svg>
                                                        Solicitud Soporte
                                                    </button>
                                                    <button @click.stop="$dispatch('openImageModal', { productId: {{ $product->id }}, context: '{{ $hideQuoter ? 'BODEGA' : 'COMERCIAL' }}' }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        Imagen
                                                    </button>

                                                    <button @click.stop="$dispatch('openObservationsModal', { itemId: {{ $product->id }} }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Observaciones
                                                    </button>

                                                    @if($product->accessories_count > 0)
                                                    <button @click.stop="$dispatch('openAccessoriesModal', { itemId: {{ $product->id }} }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                        </svg>
                                                        Accesorios
                                                    </button>
                                                    @endif

                                                    <button @click.stop="$dispatch('openCalculationModal', { productId: {{ $product->id }} }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25v-.008zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007v-.008zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" />
                                                        </svg>
                                                        Cálculos
                                                    </button>

                                                    <button @click.stop="$dispatch('openConfirmationModal', { productId: {{ $product->id }} }); open = false"
                                                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2.5 transition-colors whitespace-nowrap">
                                                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                       Sol. Confirmación
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay productos</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($search)
                                                No se encontraron productos que coincidan con "{{ $search }}".
                                                @else
                                                No hay productos disponibles en este momento.
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Paginación -->
            @if($products->hasPages())
            <div class="mt-6">
                {{ $products->links() }}
            </div>
            @endif
        </div>

        @if(!$hideQuoter)
        <!-- Sidebar del cotizador -->
        <div class="w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-screen fixed top-0 right-0 overflow-hidden z-50">
            @include('livewire.tenant.quoter.components.partials.quoter-sidebar')
        </div>
        @endif
    </div>

    <!-- Modal de Métodos de Pago -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50"
         x-data="{
            currentMethod: @entangle('currentPaymentMethod'),
            methods: @entangle('paymentMethods'),
            updateValue(method, value) {
                this.methods[method].value = Math.max(0, parseFloat(value) || 0);
                $wire.updatePaymentMethodValue(method, value);
            }
         }">

        <!-- Modal Principal -->
        <div class="w-full max-w-6xl max-h-[95vh] lg:max-h-[90vh] bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col mx-4">

            <!-- Header Responsivo -->
            <div class="bg-gray-800 text-white p-4 lg:p-6 flex-shrink-0">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg lg:text-2xl font-bold">MÉTODOS DE PAGO</h1>
                        <p class="text-gray-300 text-sm lg:text-base mt-1">
                            @if($selectedCustomer)
                                <span class="block lg:inline">{{ $selectedCustomer['businessName'] ?? $selectedCustomer['firstName'] ?? 'Cliente' }}</span>
                                <span class="hidden lg:inline"> - </span>
                                <span class="block lg:inline">{{ $selectedCustomer['cityName'] ?? '' }}</span>
                                <span class="hidden lg:inline"> - </span>
                                <span class="block lg:inline">{{ $selectedCustomer['address'] ?? '' }}</span>
                                <span class="hidden lg:inline"> - </span>
                            @endif
                            <span class="block lg:inline">Total: ${{ number_format($totalAmount, 2, ',', '.') }}</span>
                        </p>
                    </div>
                    <button wire:click="closePaymentModal"
                            class="text-gray-300 hover:text-white p-2 ml-2 flex-shrink-0">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="flex flex-1 overflow-hidden flex-col lg:flex-row">

                <!-- Panel Izquierdo - Resumen -->
                <div class="w-full lg:w-1/3 bg-gray-100 p-4 lg:p-6 lg:border-r border-gray-300 overflow-y-auto flex-shrink-0">
                    <!-- Layout responsivo para el resumen -->
                    <div class="space-y-4 lg:space-y-6">

                        <!-- Selección de Sede/Sucursal -->
                        @if(!empty($branches))
                        <div class="bg-white rounded-lg p-4 lg:p-6 shadow border-l-4 border-indigo-500">
                            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4 text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                DATOS DE ENVÍO
                            </h3>
                            <div class="space-y-3">
                                @if(count($branches) > 1)
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Seleccionar Agencia/Sucursal</label>
                                    <select 
                                        wire:model.live="selectedBranchId"
                                        wire:change="selectBranch($event.target.value)"
                                        class="block w-full text-sm border-gray-300 rounded-md bg-white text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    >
                                        <option value="">-- Seleccione una sucursal --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch['id'] }}">
                                                {{ $branch['name'] }} {{ !empty($branch['city']['name']) ? '('.$branch['city']['name'].')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                @if($selectedBranchId || count($branches) === 1)
                                <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded border border-gray-200">
                                    <p class="font-bold text-indigo-700 mb-1">
                                        {{ count($branches) === 1 ? $branches[0]['name'] : collect($branches)->firstWhere('id', $selectedBranchId)['name'] ?? '' }}
                                    </p>
                                    <p><span class="font-bold">Dirección:</span> {{ $selectedCustomer['address'] ?? 'N/A' }}</p>
                                    <p><span class="font-bold">Ciudad:</span> {{ $selectedCustomer['cityName'] ?? 'N/A' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Total de la Venta -->
                        <div class="bg-white rounded-lg p-4 lg:p-6 shadow">
                            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4 text-gray-800">TOTAL VENTA</h3>
                            <div class="text-center">
                                <div class="text-2xl lg:text-4xl font-bold text-green-600">
                                    ${{ number_format($totalAmount, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Retenciones (solo si hay retenciones) -->
                        @if($showRetentions)
                        <div class="bg-white rounded-lg p-4 lg:p-6 shadow border-l-4 border-orange-500">
                            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4 text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                RETENCIONES
                            </h3>
                            <div class="space-y-2 text-sm lg:text-base">
                                @if($retentions['retention_fuente'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Ret. Fuente (2.5%):</span>
                                    <span class="font-semibold text-orange-600">-${{ number_format($retentions['retention_fuente'], 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($retentions['retention_ica'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Ret. ICA (11.04‰):</span>
                                    <span class="font-semibold text-orange-600">-${{ number_format($retentions['retention_ica'], 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @if($retentions['retention_iva'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Ret. IVA (15%):</span>
                                    <span class="font-semibold text-orange-600">-${{ number_format($retentions['retention_iva'], 2, ',', '.') }}</span>
                                </div>
                                @endif
                                <div class="border-t pt-2 mt-3">
                                    <div class="flex justify-between items-center font-bold">
                                        <span class="text-gray-800">Total con Retenciones:</span>
                                        <span class="text-green-600 text-lg">${{ number_format($totalWithRetentions, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Estado del Pago -->
                        <div class="bg-white rounded-lg p-4 lg:p-6 shadow">
                            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4 text-gray-800">ESTADO</h3>
                            <div class="space-y-2 lg:space-y-3 text-base lg:text-lg">
                                <div class="flex justify-between">
                                    <span>Pagado:</span>
                                    <span class="font-bold text-blue-600">${{ number_format($totalPaid, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 lg:pt-3">
                                    <span>Falta:</span>
                                    <span class="font-bold text-red-600">${{ number_format($remainingBalance, 2, ',', '.') }}</span>
                                </div>
                                @if($changeAmount > 0)
                                <div class="flex justify-between border-t pt-2 lg:pt-3">
                                    <span>Cambio:</span>
                                    <span class="font-bold text-green-600">${{ number_format($changeAmount, 2, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Panel Derecho - Métodos de Pago -->
                <div class="flex-1 p-4 lg:p-6 overflow-y-auto">
                    <h2 class="text-lg lg:text-2xl font-bold mb-4 lg:mb-6 text-center text-gray-800 uppercase tracking-wider">Forma de Pago</h2>

                    <!-- Nueva sección: Selección de Sede/Sucursal en panel principal -->
                    @if(!empty($branches) && count($branches) > 1)
                    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl shadow-sm">
                        <label class="block text-xs font-black text-indigo-700 uppercase mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Confirmar Sede del Cliente
                        </label>
                        <select 
                            wire:model.live="selectedBranchId"
                            wire:change="selectBranch($event.target.value)"
                            class="block w-full text-lg font-bold border-indigo-300 rounded-lg bg-white text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm py-3"
                        >
                            @foreach($branches as $branch)
                                <option value="{{ $branch['id'] }}">
                                    {{ $branch['name'] }} {{ !empty($branch['city']['name']) ? '('.$branch['city']['name'].')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[10px] text-indigo-600 font-medium italic">
                            * Seleccione la sede que se asignará legalmente a la factura.
                        </p>
                    </div>
                    @endif

                    <!-- Tabla de Métodos de Pago Responsiva -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                        <!-- Header de la Tabla -->
                        <div class="bg-gray-800 text-white p-3 lg:p-4">
                            <div class="grid grid-cols-3 gap-2 lg:gap-4">
                                <div class="text-sm lg:text-xl font-bold text-center">MÉTODO</div>
                                <div class="text-sm lg:text-xl font-bold text-center">VALOR</div>
                                <div class="text-sm lg:text-xl font-bold text-center">ACCIÓN</div>
                            </div>
                        </div>

                        <!-- Filas de Métodos de Pago -->
                        <div class="divide-y divide-gray-200">
                            @foreach($paymentMethods as $key => $method)
                            <div class="grid grid-cols-3 gap-2 lg:gap-4 p-3 lg:p-6 items-center
                                @if($currentPaymentMethod === $key) bg-yellow-100 border-l-4 border-yellow-500 @endif
                                @if($method['value'] > 0 && $currentPaymentMethod !== $key) bg-blue-50 border-l-4 border-blue-400 @endif">

                                <!-- Nombre del Método -->
                                <div class="text-sm lg:text-2xl font-semibold text-gray-800 flex items-center cursor-pointer"
                                     wire:click="selectPaymentMethod('{{ $key }}')">
                                    @if($currentPaymentMethod === $key)
                                        <span class="mr-1 lg:mr-3 text-yellow-500">▶</span>
                                    @elseif($method['value'] > 0)
                                        <span class="mr-1 lg:mr-3 text-blue-500">●</span>
                                    @endif
                                    <span class="truncate">{{ $method['name'] }}</span>
                                </div>

                                <!-- Valor Pagado -->
                                <div class="text-center">
                                    @php
                                    $availableForMethod = $this->getAvailableBalanceForMethod($key);
                                    $isEffectivo = $key === 'efectivo';
                                    @endphp

                                    <input type="number"
                                           wire:model.live="paymentMethods.{{ $key }}.value"
                                           wire:change="calculatePaymentBalances"
                                           class="w-full text-lg lg:text-2xl font-bold text-center py-2 lg:py-3 px-2 lg:px-4 border-2 rounded-lg
                                               @if($currentPaymentMethod === $key) border-yellow-500 bg-yellow-50 @else border-gray-300 @endif
                                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="@if($isEffectivo)$0@else Máx: ${{ number_format($availableForMethod, 0, ',', '.') }} @endif"
                                           min="0"
                                           @if(!$isEffectivo && $availableForMethod != PHP_FLOAT_MAX) max="{{ $availableForMethod }}" @endif
                                           step="1000"
                                           inputmode="numeric">

                                    @if(!$isEffectivo && $availableForMethod > 0 && $availableForMethod != PHP_FLOAT_MAX)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Disponible: ${{ number_format($availableForMethod, 0, ',', '.') }}
                                    </div>
                                    @elseif($isEffectivo)
                                    <div class="text-xs text-blue-500 mt-1">
                                        Sin límite (puede dar cambio)
                                        @if($cashAutoAdjusted && $method['value'] > 0)
                                        <div class="text-xs text-orange-500 mt-1">
                                            ⚡ Ajustado automáticamente
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Botón de Acción -->
                                <div class="text-center">
                                    <button wire:click="selectAndPayTotal('{{ $key }}')"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 lg:py-3 px-3 lg:px-6 rounded-lg transition-all text-xs lg:text-sm">
                                        <span class="hidden lg:inline">PAGAR TODO</span>
                                        <span class="lg:hidden">TODO</span>
                                    </button>
                                </div>

                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botones de Acción Responsivos -->
                    <div class="mt-4 lg:mt-8 flex flex-col lg:flex-row justify-center gap-3 lg:gap-4">
                        <button wire:click="closePaymentModal"
                                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 lg:py-4 px-6 lg:px-8 rounded-lg text-base lg:text-lg">
                            CANCELAR
                        </button>

                        @if($canProceedToPayment && ($remainingBalance == 0 || $changeAmount > 0))
                        <button wire:click="confirmPayment"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 lg:py-4 px-6 lg:px-8 rounded-lg text-base lg:text-lg">
                            @if($changeAmount > 0)
                                <span class="hidden lg:inline">FACTURAR (Cambio: ${{ number_format($changeAmount, 2, ',', '.') }})</span>
                                <span class="lg:hidden">FACTURAR (Cambio: ${{ number_format($changeAmount, 2, ',', '.') }})</span>
                            @else
                                <span class="hidden lg:inline">CONFIRMAR PAGO Y FACTURAR</span>
                                <span class="lg:hidden">FACTURAR</span>
                            @endif
                        </button>
                        @else
                        <button disabled
                                class="bg-gray-400 text-gray-200 font-bold py-3 lg:py-4 px-6 lg:px-8 rounded-lg text-base lg:text-lg cursor-not-allowed">
                            @if($remainingBalance > 0)
                                FALTA ${{ number_format($remainingBalance, 2, ',', '.') }}
                            @else
                                INGRESE PAGO
                            @endif
                        </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Selección de Tipo de Entrega -->
    <div x-data="{ show: @entangle('showDeliveryModal') }"
         x-show="show"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" aria-hidden="true" @click="show = false"></div>

        <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left shadow-2xl transform transition-all w-full max-w-md border border-gray-200 dark:border-slate-700"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                        </svg>
                        Tipo de Entrega y Método de Pago
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Selecciona el tipo de entrega y método de pago para la remisión
                    </p>
                </div>
                <button wire:click="closeDeliveryModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-slate-300 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @if($selectedCustomer)
            @php
                $businessName = trim($selectedCustomer['businessName'] ?? '');
                $displayName = !empty($businessName) ? $businessName : ($selectedCustomer['firstName'] . ' ' . $selectedCustomer['lastName']);
            @endphp
            <div class="px-6 py-3 bg-blue-50/50 dark:bg-blue-900/20 border-b border-gray-200 dark:border-slate-700 flex flex-wrap gap-x-4 gap-y-2 items-center text-sm shadow-inner">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $displayName }}</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-gray-700 dark:text-slate-300 font-medium">{{ $selectedCustomer['cityName'] ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-gray-700 dark:text-slate-300 font-medium">{{ $selectedCustomer['address'] ?? 'N/A' }}</span>
                </div>
            </div>
            @endif

            <!-- Body -->
            <div class="p-6">
                <!-- Selección de Sede/Sucursal -->
                @if(!empty($branches) && count($branches) > 1)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                        Sede/Sucursal del Cliente <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedBranchId"
                            wire:change="selectBranch($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona una sucursal</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch['id'] }}">{{ $branch['name'] }} {{ !empty($branch['city']['name']) ? '('.$branch['city']['name'].')' : '' }} - {{ $branch['address'] ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Selección de Tipo de Entrega -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                        Tipo de Entrega <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedDeliveryType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un tipo de entrega</option>
                        @foreach($deliveryTypes as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selección de Método de Pago -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                        Método de Pago <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="selectedMethodPayment"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un método de pago</option>
                        @foreach($methodPayments as $method)
                            <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Campo para especificar "Otro" tipo de entrega -->
                @if($showOtherDeliveryInput)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                        Especifica el tipo de entrega <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="otherDeliveryDetails"
                           placeholder="Ej: Envío por mensajería, Recogida personalizada, etc."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @endif

                <!-- Campo de Detalles (si es requerido) -->
                @if($requiresDeliveryDetails)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                        Detalles de Entrega <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="deliveryDetails"
                              rows="3"
                              placeholder="Ingresa los detalles específicos para este tipo de entrega..."
                              class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                @endif

                <!-- Información del tipo seleccionado -->
                @if($selectedDeliveryType)
                    @php
                        $selectedType = collect($deliveryTypes)->firstWhere('id', $selectedDeliveryType);
                    @endphp
                    @if($selectedType && !empty($selectedType['detail']))
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Información</h4>
                                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">{{ $selectedType['detail'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end space-x-3">
                <button wire:click="closeDeliveryModal"
                        class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors font-medium">
                    Cancelar
                </button>
                <button wire:click="proceedWithRemissionCreation"
                        wire:loading.attr="disabled"
                        wire:target="proceedWithRemissionCreation"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium flex items-center transition-colors disabled:opacity-50">
                    <svg wire:loading.remove wire:target="proceedWithRemissionCreation" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg wire:loading wire:target="proceedWithRemissionCreation" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="proceedWithRemissionCreation">Crear OP</span>
                    <span wire:loading wire:target="proceedWithRemissionCreation">Creando...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Producto Genérico -->
    @if($showGenericProductModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showGenericProductModal', false)"></div>

        <!-- Modal -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Producto genérico</h3>
                <button wire:click="$set('showGenericProductModal', false)"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-5 py-4 space-y-4">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del producto <span class="text-red-500">*</span></label>
                    <input wire:model="genericProductName"
                        type="text"
                        placeholder="Ej: Servicio de instalación"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    @error('genericProductName')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input wire:model="genericProductPrice"
                            type="number"
                            min="0"
                            step="1"
                            placeholder="0"
                            class="block w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>
                    @error('genericProductPrice')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Impuesto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Impuesto <span class="text-red-500">*</span></label>
                    <select wire:model="genericProductTaxId"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Seleccionar impuesto...</option>
                        @foreach(\App\Models\Tenant\CnfTaxes::where('status', 1)->get() as $tax)
                            <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->percentage }}%)</option>
                        @endforeach
                    </select>
                    @error('genericProductTaxId')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="$set('showGenericProductModal', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
                <button wire:click="saveGenericProduct"
                    wire:loading.attr="disabled"
                    wire:target="saveGenericProduct"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 rounded-lg transition-colors flex items-center gap-2">
                    <div wire:loading wire:target="saveGenericProduct">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    Crear y agregar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    function copyProductToClipboard(sku, price, name, id) {
        const priceFormatted = new Intl.NumberFormat().format(Math.round(price));
        const link = `https://www.fervicom.com/producto/${sku.toLowerCase()}`;
        
        // Formato solicitado: Código - $Precio incluido iva \n Descripción \n Detalles en: [Link]
        const textToCopy = `${sku} - $${priceFormatted} incluido iva\n${name}\nDetalles en:\n${link}`;

        // Usar la API moderna de portapapeles
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopyFeedback();
            }).catch(err => {
                console.error('Error al copiar:', err);
                fallbackCopyToClipboard(textToCopy);
            });
        } else {
            fallbackCopyToClipboard(textToCopy);
        }
    }

    function fallbackCopyToClipboard(text) {
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
            showCopyFeedback();
        } catch (err) {
            console.error('Fallback error:', err);
        }
        document.body.removeChild(textArea);
    }

    function showCopyFeedback() {
        // Usar el sistema de brindado por el componente para consistencia
        if (window.Livewire) {
            Livewire.dispatch('show-toast', {
                type: 'success',
                message: '¡Texto copiado al portapapeles!'
            });
        }
        console.log('Copiado exitosamente');
    }
</script>