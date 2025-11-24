{{-- Establecer el header --}}
@php
    $header = 'Seleccionar productos';
@endphp

<div>
    <div class="flex">
        <!-- Área principal de productos -->
        <div class="flex-1 p-6">
            <!-- Botón de regresar -->
            <div class="mb-6">
                <button onclick="history.back()" class="inline-flex items-center text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Regresar cotizaciones
                </button>
            </div>

            <!-- Barra de búsqueda y filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <!-- Búsqueda -->
                        <div class="flex-1">
                            <div class="relative">
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
                        </div>

                        <!-- Controles -->
                        <div class="flex items-center gap-3">
                            <!-- Filtro Todos -->
                            <button class="px-4 py-2 bg-yellow-400 text-yellow-900 rounded-lg font-medium text-sm">
                                Todos
                            </button>

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

            <!-- Grid de productos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @forelse($products as $product)
                    @php
                        $quantity = $this->getProductQuantity($product->id);
                        $isSelected = $quantity > 0;
                    @endphp
                    <div wire:click="addToQuoter({{ $product->id }})"
                         class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer transform transition-all duration-200
                                hover:shadow-lg hover:shadow-indigo-100 dark:hover:shadow-gray-900/30 hover:-translate-y-1 hover:border-indigo-300 dark:hover:border-indigo-500
                                {{ $isSelected ? 'ring-2 ring-indigo-500 shadow-lg border-indigo-300 dark:border-indigo-500' : '' }}">

                        <!-- Contador en la esquina superior derecha -->
                        @if($quantity > 0)
                            <div class="absolute top-2 right-2 bg-indigo-600 dark:bg-indigo-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center z-10">
                                {{ $quantity }}
                            </div>
                        @endif

                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                <span class="text-2xl font-bold text-gray-400 dark:text-gray-500">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>

                        <!-- Información del producto -->
                        <div class="p-3 flex flex-col h-full">
                            <div class="text-center flex-1">
                                <!-- Precio -->
                                <div class="font-bold text-lg text-gray-900 dark:text-white mb-1">
                                    {{ $product->formatted_price }}
                                </div>

                                <!-- Nombre -->
                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {{ $product->display_name }}
                                </div>

                                <!-- SKU (con altura fija para mantener estructura) -->
                                <div class="text-xs text-gray-500 dark:text-gray-500 mb-3 h-4 flex items-center justify-center">
                                    @if($product->sku && trim($product->sku) !== '')
                                        SKU: {{ $product->sku }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Overlay de selección -->
                        @if($isSelected)
                            <div class="absolute inset-0 bg-indigo-500 bg-opacity-10 dark:bg-indigo-400 dark:bg-opacity-10"></div>
                        @endif
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

            <!-- Paginación -->
            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- Sidebar del cotizador -->
        <div class="w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col">
            <!-- Header del cotizador -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Productos</h2>
                    @if(!empty($quoterItems))
                        <button wire:click="clearQuoter"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                            Limpiar
                        </button>
                    @endif
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $this->quoterCount }} productos seleccionados
                </div>

                <!-- Búsqueda de clientes -->
                <div class="mt-4">
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
                    <div class="mt-4">
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
            </div>

            <!-- Lista de productos en el cotizador -->
            <div class="flex-1 overflow-y-auto">
                @if(empty($quoterItems))
                    <div class="flex flex-col items-center justify-center h-full p-6 text-center">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Elige los platos o bebidas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Selecciona productos de la lista para agregarlos a tu cotización
                        </p>
                    </div>
                @else
                    <div class="p-4 space-y-3">
                        @foreach($quoterItems as $index => $item)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                        @if($item['description'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($item['description'], 50) }}</p>
                                        @endif
                                    </div>
                                    <button wire:click="removeFromQuoter({{ $index }})"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                class="w-7 h-7 flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            -
                                        </button>
                                        <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                class="w-7 h-7 flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            +
                                        </button>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        ${{ number_format($item['price'] * $item['quantity']) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Footer del cotizador -->
            @if(!empty($quoterItems))
                <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                    <div class="space-y-4">
                        <!-- Total -->
                        <div class="flex justify-between items-center text-lg font-bold text-gray-900 dark:text-white">
                            <span>Total:</span>
                            <span>${{ number_format($totalAmount) }}</span>
                        </div>

                        <!-- Botón guardar cambios -->
                        <button wire:click="saveQuote"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>