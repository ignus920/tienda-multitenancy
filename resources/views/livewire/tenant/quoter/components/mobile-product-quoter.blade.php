{{-- Establecer el header --}}
@php
$header = 'Seleccionar productos';
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header fijo con búsqueda y carrito (Ajustado para no tapar la nav global) -->
    <div class="sticky top-16 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3">
            <!-- Barra superior con botón regresar y carrito -->
            <div class="flex items-center justify-between mb-3">
                <a 
                    href="{{ route('tenant.quoter') }}" 
                    class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 
                        flex items-center gap-2"
                    wire:navigate.hover
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>

                    <span>Regresar cotizaciones</span>
                </a>

                <div class="flex items-center gap-1">
                    @if($this->quoterCount > 0)
                    <button
                        @click="
                            Swal.fire({
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
                            })
                        "
                        class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                        title="Limpiar cotizador">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                    @endif

                    <!-- Carrito flotante -->
                    <button
                        @click="openCart = true; $wire.toggleCartModal();"
                        class="relative p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 16a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>

                        @if($this->quoterCount > 0)
                        <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $this->quoterCount }}
                        </span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Contenedor principal con flex para que queden lado a lado -->
            <div class="flex gap-3">
                <!-- Búsqueda (toma más espacio) -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar productos..."
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Filtro de Categorías -->
                <div class="w-30">
                    <select wire:model.live="selectedCategory"
                        class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las categorías</option>
                        @foreach($this->getCategories() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- Lista de productos en Grid de 2 columnas -->
    <div class="px-4 py-4 grid grid-cols-2 gap-4">
        @forelse($products as $product)
        @php
        $quantity = $this->getProductQuantity($product->id);
        $isSelected = $quantity > 0;
        @endphp

        <div @if($isSelected) wire:click="increaseQuantity({{ $product->id }})" @endif
            class="relative flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 shadow-sm
                        {{ $isSelected ? 'ring-2 ring-indigo-500 border-indigo-500 scale-[1.02]' : 'hover:border-indigo-300' }}">

            <!-- Imagen del producto (Arriba) -->
            <div class="aspect-square w-full relative bg-gray-100 dark:bg-gray-700">
                @if($product->principalImage)
                <img class="w-full h-full object-cover"
                    src="{{ $product->principalImage->getImageUrl() }}"
                    alt="{{ $product->display_name }}">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <span class="text-3xl font-bold text-gray-400 dark:text-gray-500">
                        {{ strtoupper(substr($product->name, 0, 1)) }}
                    </span>
                </div>
                @endif

                <!-- Badge de cantidad (Esquina superior derecha) -->
                @if($quantity > 0)
                <div class="absolute top-2 right-2 flex items-center justify-center w-7 h-7 bg-indigo-600 text-white text-xs font-bold rounded-full shadow-lg">
                    {{ $quantity }}
                </div>
                @endif
            </div>

            <!-- Información del producto (Medio) -->
            <div class="p-3 flex-1 flex flex-col">
                <!-- SKU y Nombre -->
                <div class="mb-3">
                    <div class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold truncate">
                        {{ $product->sku ?: 'SIN SKU' }}
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white text-xs leading-tight line-clamp-2 mt-0.5 h-8">
                        {{ $product->display_name }}
                    </div>
                    
                    <!-- Bodegas disponibles -->
                    @if($product->store_names)
                    <div class="mt-2">
                        <div class="text-[8px] uppercase font-semibold text-indigo-600 dark:text-indigo-400 mb-1">
                            Disponible en:
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach(explode(', ', $product->store_names) as $storeName)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">
                                <svg class="w-2 h-2 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                {{ $storeName }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Parte Inferior: Precios o Selector de Cantidad -->
                <div class="mt-auto">
                    @if(!$isSelected)
                        <!-- Botones de Precio (Cajas verdes como en la imagen) -->
                        @php $allPrices = $product->all_prices; @endphp
                        @if(!empty($allPrices))
                        <div class="space-y-1.5">
                            @foreach($allPrices as $label => $price)
                            <button
                                wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                wire:loading.attr="disabled"
                                wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                class="relative w-full py-1.5 px-2 rounded-lg border border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-900/10 text-center transition-colors active:bg-emerald-100 dark:active:bg-emerald-900/30 overflow-hidden">
                                
                                <!-- Contenido Normal -->
                                <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')">
                                    <div class="text-[9px] uppercase font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ $label }}</div>
                                    <div class="text-[13px] font-black text-emerald-700 dark:text-emerald-300">
                                        ${{ number_format($price) }}
                                    </div>
                                </div>

                                <!-- Spinner de Carga -->
                                <div wire:loading wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" class="flex items-center justify-center py-1">
                                    <svg class="animate-spin h-5 w-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-2 text-xs font-bold text-gray-400">Sin precio</div>
                        @endif
                    @else
                        <!-- Precio seleccionado (Solo visual) -->
                        @php $priceInfo = $this->getSelectedPriceInfo($product->id); @endphp
                        @if($priceInfo)
                        <div class="mb-2 py-1.5 px-2 rounded-lg border border-emerald-500/20 bg-emerald-50/30 dark:bg-emerald-900/5 text-center opacity-80">
                            <div class="text-[8px] uppercase font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ $priceInfo['label'] }}</div>
                            <div class="text-[12px] font-black text-emerald-700 dark:text-emerald-300">
                                ${{ number_format($priceInfo['price']) }}
                            </div>
                        </div>
                        @endif

                        <!-- Selector de Cantidad (Azul/Púrpura como en la imagen) -->
                        <div class="p-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800">
                            <div class="flex items-center gap-2">
                                <!-- Botón Menos -->
                                <button 
                                    wire:click.stop="decreaseQuantity({{ $product->id }})"
                                    class="flex-1 h-12 flex items-center justify-center bg-indigo-600 text-white rounded-lg active:bg-indigo-700 shadow-md transition-transform active:scale-95">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>

                                <!-- Input Cantidad -->
                                @php $itemIndex = $this->findProductInQuoter($product->id); @endphp
                                <div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-700 rounded-lg shadow-inner border border-indigo-100 dark:border-indigo-900/50">
                                    <input 
                                        type="number" 
                                        wire:model.live="quoterItems.{{ $itemIndex }}.quantity"
                                        wire:change="validateQuantity({{ $itemIndex }})"
                                        x-on:click.stop
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        class="w-full bg-transparent border-none text-center font-black text-indigo-700 dark:text-indigo-300 text-lg focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                </div>

                                <!-- Botón Más -->
                                <button 
                                    wire:click.stop="increaseQuantity({{ $product->id }})"
                                    class="flex-1 h-12 flex items-center justify-center bg-indigo-600 text-white rounded-lg active:bg-indigo-700 shadow-md transition-transform active:scale-95">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        @endforelse
    </div>

    <!-- Paginación -->
    @if($products->hasPages())
    <div class="px-4 py-4">
        {{ $products->links() }}
    </div>
    @endif

    <!-- Modal del carrito -->
    @if($showCartModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" wire:click="toggleCartModal"></div>

        <!-- Modal - Pantalla completa -->
        <div class="relative h-full flex items-stretch">
            <div class="w-full h-full bg-white dark:bg-gray-800 flex flex-col">

                <!-- Header del modal -->
                <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0 bg-white dark:bg-gray-800">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->quoterCount }} Productos seleccionados</h2>
                    <button wire:click="toggleCartModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Búsqueda de clientes -->
                <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800">
                    @if($selectedCustomer)
                    <!-- Cliente seleccionado -->
                    <div wire:key="customer-selected-box"
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition.opacity
                        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3 mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-green-800 dark:text-green-200 text-sm">
                                    {{ $selectedCustomer['businessName'] ?: $selectedCustomer['firstName'] . ' ' . $selectedCustomer['lastName'] }}
                                </h4>

                                <p class="text-xs text-green-600 dark:text-green-300">
                                    Identificación: {{ $selectedCustomer['identification'] }}
                                </p>
                            </div>
                            <div class="flex items-center ml-2">
                                <!-- Botón Editar -->
                                <button
                                    wire:click="editCustomer"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 mr-4"
                                    title="Editar cliente">

                                    <!-- Ícono normal -->
                                    <svg wire:loading.remove wire:target="editCustomer" class="w-7 h-7 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>

                                    <!-- Ícono de loading -->
                                    <svg wire:loading wire:target="editCustomer" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                </button>

                                <!-- Botón Limpiar -->
                                <button
                                    x-on:click="show = false"
                                    wire:click="clearCustomer"
                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                                    title="Limpiar cliente">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($showCreateCustomerButton || $showCreateCustomerForm)
                    <!-- Formulario para crear/editar cliente -->

                    @if (!$editingCustomerId)
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Crear Cliente</label>
                        <button
                            x-on:click="show = false"
                            wire:click="clearCustomer"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    @endif

                    @if (!$editingCustomerId)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-2">
                        @endif
                        <livewire:tenant.vnt-company.vnt-company-form
                            :reusable="true"
                            :companyId="$editingCustomerId"
                            key="customer-form-{{ $editingCustomerId ?? 'new' }}" />
                        @if (!$editingCustomerId)
                    </div>
                    @endif


                    @endif

                    @if(!$selectedCustomer && !$showCreateCustomerForm && !$showCreateCustomerButton)
                    <!-- Formulario de búsqueda Predictiva -->
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Buscar Cliente</label>
                        <div class="relative">
                            <!-- Input de búsqueda -->
                            <input
                                wire:model.live.debounce.300ms="customerSearch"
                                type="text"
                                placeholder="Escribe nombre, NIT o cédula..."
                                class="w-full px-4 py-3 text-sm border-2 border-gray-200 focus:border-indigo-500 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all outline-none"
                                @keydown.enter="$wire.searchCustomer()">

                            <!-- Resultados de búsqueda -->
                            @if(!empty($customerResults))
                            <div class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden max-h-60 overflow-y-auto">
                                @foreach($customerResults as $result)
                                <button
                                    wire:click="selectCustomer({{ $result['id'] }})"
                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $result['businessName'] ?: ($result['firstName'] . ' ' . $result['lastName']) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $result['identification'] }} • {{ $result['billingEmail'] }}
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Contenido del carrito -->
                <div class="flex-1 overflow-y-auto px-4 py-4 min-h-0">
                    @if(empty($quoterItems))

                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>

                        <p class="text-gray-500 dark:text-gray-400">Tu carrito está vacío</p>
                    </div>

                    @else

                    <div class="space-y-4">
                        @foreach($quoterItems as $index => $item)

                        <div 
                            x-data="{ 
                                xDown: null, 
                                yDown: null,
                                xDiff: 0,
                                swipeThreshold: 60,
                                isDeleting: false,
                                resetPosition() { this.xDiff = 0; },
                                handleTouchStart(e) {
                                    this.xDown = e.touches[0].clientX;
                                    this.yDown = e.touches[0].clientY;
                                },
                                handleTouchMove(e) {
                                    if (!this.xDown || !this.yDown) return;
                                    let xUp = e.touches[0].clientX;
                                    let yUp = e.touches[0].clientY;
                                    let xDiff = this.xDown - xUp;
                                    let yDiff = this.yDown - yUp;

                                    if (Math.abs(xDiff) > Math.abs(yDiff)) {
                                        // Movimiento horizontal
                                        this.xDiff = -xDiff;
                                        // Bloquear scroll vertical si estamos haciendo swipe
                                        if (Math.abs(xDiff) > 10) {
                                            if (e.cancelable) e.preventDefault();
                                        }
                                    }
                                },
                                handleTouchEnd() {
                                    if (Math.abs(this.xDiff) > this.swipeThreshold) {
                                        this.isDeleting = true;
                                        $wire.removeFromQuoter({{ $index }});
                                    } else {
                                        this.resetPosition();
                                    }
                                    this.xDown = null;
                                    this.yDown = null;
                                }
                            }"
                            class="relative overflow-hidden rounded-lg group"
                        >
                            <!-- Fondo Rojo con Basura (atrás de la tarjeta) -->
                            <div class="absolute inset-0 bg-red-600 flex items-center justify-between px-6 text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>

                            <!-- Tarjeta Principal (la que se desliza) -->
                            <div 
                                @touchstart="handleTouchStart"
                                @touchmove="handleTouchMove"
                                @touchend="handleTouchEnd"
                                :style="`transform: translateX(${xDiff}px)`"
                                class="relative bg-white dark:bg-gray-800 p-3 transition-transform duration-200 ease-out border-b border-gray-100 dark:border-gray-700 shadow-sm"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            @if(isset($item['price_label']))
                                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5">Precio: {{ $item['price_label'] }}</p>
                                            @endif
                                            <p class="text-xs text-indigo-600 dark:text-indigo-400">@Impuesto: {{ $item['tax_label'] }}</p>
                                        </div>
                                            
                                    </div>

                                    <button wire:click="removeFromQuoter({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <label for="quantity-{{ $index }}" class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Cant:</label>
                                        <input
                                            id="quantity-{{ $index }}"
                                            type="number"
                                            wire:model.lazy="quoterItems.{{ $index }}.quantity"
                                            wire:change="validateQuantity({{ $index }})"
                                            min="1"
                                            max="9999"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            class="w-12 px-1 py-0.5 text-center text-sm font-bold border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500"
                                            value="{{ $item['quantity'] }}">
                                    </div>

                                    <div class="text-sm font-black text-indigo-700 dark:text-indigo-300">
                                        ${{ number_format($item['price'] * $item['quantity']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endforeach
                    </div>

                    @endif
                </div>

                <!-- Footer del modal -->
                @if(!empty($quoterItems))
                <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 space-y-4 flex-shrink-0 bg-white dark:bg-gray-800">

                    <!-- Observaciones -->
                    <div x-data="{ open: @entangle('showObservations') }" class="w-full">

                        <button @click="open = !open"
                            class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">

                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Observaciones:
                            </span>

                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 transform transition-transform"
                                :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="mt-3">
                            <textarea
                                wire:model="observaciones"
                                rows="4"
                                placeholder="Escribe observaciones adicionales..."
                                class="block w-full p-2 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300
                               dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900 dark:text-white">
                        <span>Total:</span>
                        <span>${{ number_format($totalAmount, 2, ',', '.') }}</span>
                    </div>

                    <!-- Botones ---->
                    @if($isEditing || $isEditingRemission)
                    
                    @php
                        $updateMethod = $isEditingRemission ? 'updateRemission' : 'updateQuote';
                        $updateText = $isEditingRemission ? 'Actualizar Remisión' : 'Actualizar Cotización';
                    @endphp

                    @if($isEditing)
                    <div class="space-y-2 mb-2">
                        <button wire:click="confirmOrder"
                            wire:loading.attr="disabled"
                            wire:target="confirmOrder"
                            class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center shadow-lg transform transition active:scale-95 disabled:opacity-50 border border-green-500 dark:border-green-600">
                            
                            <svg wire:loading.remove wire:target="confirmOrder" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            
                            <svg wire:loading wire:target="confirmOrder" class="w-6 h-6 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="confirmOrder">Crear remisión</span>
                            <span wire:loading wire:target="confirmOrder">Creando remisión...</span>
                        </button>

                        <!-- Botón Facturar - Solo visible si el módulo está activo -->
                        @if($this->isInvoiceModuleActive)
                        <button wire:click="invoiceOrder"
                            wire:loading.attr="disabled"
                            wire:target="invoiceOrder"
                            class="w-full mt-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center shadow-lg transform transition active:scale-95 disabled:opacity-50 border border-blue-500 dark:border-blue-600">

                            <svg wire:loading.remove wire:target="invoiceOrder" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>

                            <svg wire:loading wire:target="invoiceOrder" class="w-6 h-6 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="invoiceOrder">Facturar</span>
                            <span wire:loading wire:target="invoiceOrder">Facturando...</span>
                        </button>
                        @endif
                    </div>
                    @endif

                    <div class="flex gap-2">
                        <button wire:click="{{ $updateMethod }}"
                            wire:loading.attr="disabled"
                            wire:target="{{ $updateMethod }}"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center disabled:opacity-50 text-sm whitespace-nowrap">

                            <svg wire:loading.remove wire:target="{{ $updateMethod }}" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>

                            <svg wire:loading wire:target="{{ $updateMethod }}" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="{{ $updateMethod }}">{{ $updateText }}</span>
                            <span wire:loading wire:target="{{ $updateMethod }}">Actualizando...</span>
                        </button>

                        <button wire:click="cancelEditing"
                            wire:loading.attr="disabled"
                            wire:target="cancelEditing"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center disabled:opacity-50 text-sm whitespace-nowrap">

                            <svg wire:loading.remove wire:target="cancelEditing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>

                            <svg wire:loading wire:target="cancelEditing" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="cancelEditing">Cancelar</span>
                            <span wire:loading wire:target="cancelEditing">Cancelando...</span>
                        </button>
                    </div>

                    @else

                    <div class="space-y-2">

                        @if(!$selectedCustomer)
                        <button disabled
                            class="w-full bg-gray-400 dark:bg-gray-600 text-gray-200 dark:text-gray-400 font-medium py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.802-.833-2.572 0L4.242 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            Seleccione un Cliente
                        </button>
                        @else

                        <button wire:click="saveQuote"
                            wire:loading.attr="disabled"
                            wire:target="saveQuote"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center disabled:opacity-50">

                            <svg wire:loading.remove wire:target="saveQuote" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1" />
                            </svg>

                            <svg wire:loading wire:target="saveQuote" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="saveQuote">Guardar Cotización</span>
                            <span wire:loading wire:target="saveQuote">Guardando...</span>
                        </button>

                        <!-- Botón Facturar - También disponible al crear cotización -->
                        @if($this->isInvoiceModuleActive)
                        <button wire:click="invoiceOrder"
                            wire:loading.attr="disabled"
                            wire:target="invoiceOrder"
                            class="w-full mt-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center shadow-lg transform transition active:scale-95 disabled:opacity-50 border border-blue-500 dark:border-blue-600">

                            <svg wire:loading.remove wire:target="invoiceOrder" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>

                            <svg wire:loading wire:target="invoiceOrder" class="w-6 h-6 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>

                            <span wire:loading.remove wire:target="invoiceOrder">Facturar</span>
                            <span wire:loading wire:target="invoiceOrder">Facturando...</span>
                        </button>
                        @endif

                        @endif
                    </div>

                    @endif
                </div>
                @endif

            </div>
        </div>

    </div>
    @endif

    <!-- Modal de Métodos de Pago (Versión Móvil) -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-2 z-50">

        <!-- Modal Principal (Responsivo para móvil) -->
        <div class="w-full h-full max-w-md bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="bg-gray-800 text-white p-4 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-lg font-bold">MÉTODOS DE PAGO</h1>
                        <p class="text-gray-300 text-sm">
                            Total: ${{ number_format($totalAmount, 2, ',', '.') }}
                        </p>
                    </div>
                    <button wire:click="closePaymentModal" class="text-gray-300 hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="flex-1 p-4 overflow-y-auto">

                <!-- Estado del Pago -->
                <div class="bg-gray-100 rounded-lg p-4 mb-6">
                    @if($changeAmount > 0)
                    <!-- Modo con cambio -->
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-600">PAGADO</div>
                            <div class="text-xl font-bold text-blue-600">${{ number_format($totalPaid, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">TOTAL</div>
                            <div class="text-xl font-bold text-gray-800">${{ number_format($totalAmount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">CAMBIO</div>
                            <div class="text-xl font-bold text-green-600">${{ number_format($changeAmount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @else
                    <!-- Modo normal -->
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-600">PAGADO</div>
                            <div class="text-xl font-bold text-blue-600">${{ number_format($totalPaid, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">FALTA</div>
                            <div class="text-xl font-bold text-red-600">${{ number_format($remainingBalance, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Métodos de Pago -->
                <div class="space-y-4">
                    @foreach($paymentMethods as $key => $method)
                    <div class="bg-white rounded-lg p-4 border-2
                        @if($currentPaymentMethod === $key) border-yellow-500 bg-yellow-50 @else border-gray-200 @endif">

                        <div class="flex justify-between items-center mb-3">
                            <button wire:click="selectPaymentMethod('{{ $key }}')"
                                    class="font-bold text-lg text-gray-800">
                                @if($currentPaymentMethod === $key)
                                    <span class="text-yellow-500">▶ </span>
                                @elseif($method['value'] > 0)
                                    <span class="text-blue-500">● </span>
                                @endif
                                {{ $method['name'] }}
                            </button>
                            <button wire:click="selectPaymentMethod('{{ $key }}'); payTotalWithCurrentMethod();"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm font-bold">
                                TODO
                            </button>
                        </div>

                        <input type="number"
                               wire:model.live="paymentMethods.{{ $key }}.value"
                               wire:change="calculatePaymentBalances"
                               class="w-full text-xl font-bold text-center py-3 px-4 border rounded-lg
                                   @if($currentPaymentMethod === $key) border-yellow-500 bg-yellow-50 @else border-gray-300 @endif"
                               placeholder="0"
                               min="0"
                               step="1000">
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="p-4 border-t bg-gray-50 flex gap-3">
                <button wire:click="closePaymentModal"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 rounded-lg">
                    CANCELAR
                </button>

                @if($canProceedToPayment && ($remainingBalance == 0 || $changeAmount > 0))
                <button wire:click="confirmPayment"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">
                    @if($changeAmount > 0)
                        FACTURAR (Cambio: ${{ number_format($changeAmount, 0, ',', '.') }})
                    @else
                        FACTURAR
                    @endif
                </button>
                @else
                <button disabled
                        class="flex-1 bg-gray-400 text-gray-200 font-bold py-3 rounded-lg text-sm">
                    @if($remainingBalance > 0)
                        FALTA ${{ number_format($remainingBalance, 0, ',', '.') }}
                    @else
                        INGRESE PAGO
                    @endif
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>