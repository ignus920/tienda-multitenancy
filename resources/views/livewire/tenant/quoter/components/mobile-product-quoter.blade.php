{{-- Establecer el header --}}
@php
    $header = 'Seleccionar productos';
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header fijo con búsqueda y carrito -->
    <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3">
            <!-- Barra superior con botón regresar y carrito -->
            <div class="flex items-center justify-between mb-3">
                <button onclick="history.back()" class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Carrito flotante -->
                <button wire:click="toggleCartModal" class="relative p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 16a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    @if($this->quoterCount > 0)
                        <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $this->quoterCount }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Búsqueda -->
            <div class="relative">
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
        </div>
    </div>

    <!-- Lista de productos -->
    <div class="px-4 py-4 space-y-3">
        @forelse($products as $product)
            @php
                $quantity = $this->getProductQuantity($product->id);
                $isSelected = $quantity > 0;
            @endphp

            <div wire:click="addToQuoter({{ $product->id }})"
                 class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 cursor-pointer active:bg-gray-50 dark:active:bg-gray-700 transition-colors
                        {{ $isSelected ? 'ring-2 ring-indigo-500 border-indigo-300' : '' }}">

                <div class="flex items-center justify-between">
                    <!-- Información del producto -->
                    <div class="flex-1">
                        <!-- Código y nombre -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $product->sku ? $product->sku . ' - ' : '' }}{{ $product->display_name }}
                                </div>
                                @if($product->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ Str::limit($product->description, 50) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Precio -->
                        <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                            {{ $product->formatted_price }}
                        </div>
                    </div>

                    <!-- Cantidad seleccionada -->
                    @if($quantity > 0)
                        <div class="ml-4 flex items-center justify-center w-8 h-8 bg-indigo-600 text-white text-sm font-bold rounded-full">
                            {{ $quantity }}
                        </div>
                    @endif
                </div>

                <!-- Indicador de selección -->
                @if($isSelected)
                    <div class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                        ✓ Agregado al carrito
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12">
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

            <!-- Modal -->
            <div class="relative min-h-screen flex items-end">
                <div class="w-full bg-white dark:bg-gray-800 rounded-t-lg max-h-[80vh] flex flex-col">
                    <!-- Header del modal -->
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Carrito de Compras</h3>
                        <button wire:click="toggleCartModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Búsqueda de clientes -->
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        @if($selectedCustomer)
                            <!-- Cliente seleccionado -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-green-800 dark:text-green-200 text-sm">
                                            {{ $selectedCustomer['businessName'] ?: $selectedCustomer['firstName'] . ' ' . $selectedCustomer['lastName'] }}
                                        </h4>
                                        <p class="text-xs text-green-600 dark:text-green-300">
                                            Identificación: {{ $selectedCustomer['identification'] }}
                                        </p>
                                        @if($selectedCustomer['billingEmail'])
                                            <p class="text-xs text-green-600 dark:text-green-300">{{ $selectedCustomer['billingEmail'] }}</p>
                                        @endif
                                    </div>
                                    <button wire:click="clearCustomer" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Formulario de búsqueda -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Buscar Cliente</label>
                                <div class="flex gap-2">
                                    <input wire:model="customerSearch"
                                           type="text"
                                           placeholder="NIT o cédula..."
                                           class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <button wire:click="searchCustomer"
                                            wire:loading.attr="disabled"
                                            wire:target="searchCustomer"
                                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">

                                        <!-- Icono de búsqueda normal -->
                                        <svg wire:loading.remove wire:target="searchCustomer" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>

                                        <!-- Icono de loading con animación -->
                                        <svg wire:loading wire:target="searchCustomer" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Formulario para crear cliente cuando no se encuentra -->
                    @if($showCreateCustomerForm)
                        <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Crear Cliente</h3>
                                <button wire:click="hideCreateCustomerForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-2">
                                <livewire:tenant.vnt-company.vnt-company-form :reusable="true" />
                            </div>
                        </div>
                    @endif

                    <!-- Contenido del carrito -->
                    <div class="flex-1 overflow-y-auto px-4 py-4">
                        @if(empty($quoterItems))
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Tu carrito está vacío</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($quoterItems as $index => $item)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">${{ number_format($item['price']) }} c/u</p>
                                            </div>
                                            <button wire:click="removeFromQuoter({{ $index }})" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Control de cantidad -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                        class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    -
                                                </button>
                                                <span class="w-8 text-center font-medium text-gray-900 dark:text-white">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                        class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    +
                                                </button>
                                            </div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                                ${{ number_format($item['price'] * $item['quantity']) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Footer del modal -->
                    @if(!empty($quoterItems))
                        <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                            <!-- Total -->
                            <div class="flex justify-between items-center text-lg font-bold text-gray-900 dark:text-white">
                                <span>Total:</span>
                                <span>${{ number_format($totalAmount) }}</span>
                            </div>

                            <!-- Botones -->
                            <div class="space-y-2">
                                <button wire:click="saveQuote"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                                    Confirmar Cotización
                                </button>
                                <button wire:click="clearQuoter"
                                        class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-medium py-3 px-4 rounded-lg transition-colors">
                                    Limpiar Carrito
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>