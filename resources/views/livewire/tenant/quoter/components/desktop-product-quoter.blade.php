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
                <a href="{{ route('tenant.quoter') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium" wire:navigate.hover>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Regresar cotizaciones
                </a>
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
                <div @if($isSelected) wire:click="increaseQuantity({{ $product->id }})" @endif
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all duration-200
                                hover:shadow-lg hover:shadow-indigo-100 dark:hover:shadow-gray-900/30 hover:-translate-y-1 hover:border-indigo-300 dark:hover:border-indigo-500
                                {{ $isSelected ? 'ring-2 ring-indigo-500 shadow-lg border-indigo-300 dark:border-indigo-500 cursor-pointer' : '' }}">

                    <!-- Contador en la esquina superior derecha -->
                    @if($quantity > 0)
                    <div class="absolute top-2 right-2 bg-indigo-600 dark:bg-indigo-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center z-10">
                        {{ $quantity }}
                    </div>
                    @endif

                    <!-- Imagen del producto -->
                    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center p-2">
                        @if($product->principalImage)
                        <img class="w-full h-full object-cover rounded-lg"
                            src="{{ $product->principalImage->getImageUrl() }}"
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
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ $product->display_name }}
                            </div>
                            <!-- SKU (con altura fija para mantener estructura) -->
                            <div class="text-xs text-gray-500 dark:text-gray-500 mb-3 h-4 flex items-center justify-center">
                                @if($product->sku && trim($product->sku) !== '')
                                SKU: {{ $product->sku }}
                                @endif
                            </div>

                            <!-- Bodegas disponibles -->
                            @if($product->store_names)
                            <div class="mb-2 px-2">
                                <div class="text-[9px] uppercase font-semibold text-indigo-600 dark:text-indigo-400 mb-1">
                                    Disponible en:
                                </div>
                                <div class="flex flex-wrap gap-1 justify-center">
                                    @foreach(explode(', ', $product->store_names) as $storeName)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">
                                        <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                        </svg>
                                        {{ $storeName }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Precios -->
                            @php
                            $allPrices = $product->all_prices;
                            @endphp
                            @if(!empty($allPrices))
                            <div class="mb-2 space-y-1">
                                @foreach($allPrices as $label => $price)
                                @php
                                    // Verificar si este precio está seleccionado en modo edición
                                    $isThisPriceSelected = $this->isPriceSelected($product->id, $label);
                                @endphp
                                <button
                                    wire:click.stop="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')"
                                    class="relative w-full py-1 px-2 rounded-lg border transition-colors overflow-hidden group
                                        {{ $isThisPriceSelected 
                                            ? 'border-blue-500 bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500/50' 
                                            : 'border-emerald-500/30 bg-emerald-50/50 dark:bg-emerald-900/10 active:bg-emerald-100 dark:active:bg-emerald-900/30' 
                                        }}">
                                    
                                    <!-- Contenido Normal -->
                                    <div wire:loading.remove wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')">
                                        <div class="text-[9px] uppercase font-bold truncate transition-colors
                                            {{ $isThisPriceSelected 
                                                ? 'text-blue-700 dark:text-blue-300' 
                                                : 'text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700' 
                                            }}">
                                            {{ $label }}
                                            @if($isThisPriceSelected)
                                                <span class="ml-1">✓</span>
                                            @endif
                                        </div>
                                        <div class="text-[12px] font-black
                                            {{ $isThisPriceSelected 
                                                ? 'text-blue-800 dark:text-blue-200' 
                                                : 'text-emerald-700 dark:text-emerald-300' 
                                            }}">
                                            ${{ number_format($price) }}
                                        </div>
                                    </div>

                                    <!-- Spinner de Carga (Reload) -->
                                    <div wire:loading wire:target="addToQuoter({{ $product->id }}, {{ $price }}, '{{ $label }}')" class="flex items-center justify-center py-1">
                                        <svg class="animate-spin h-4 w-4 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @else
                            <div class="font-bold text-lg text-gray-400 dark:text-gray-500 mb-1">
                                Sin precio
                            </div>
                            @endif
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
        <div class="w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-screen">
            <!-- Header del cotizador -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->quoterCount }} Productos seleccionados</h2>
                    @if(!empty($quoterItems))
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
                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                        Limpiar
                    </button>
                    @endif
                </div>


                <!-- Búsqueda de clientes -->
                <div class="mt-4">
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
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 mr-2"
                                    title="Editar cliente">

                                    <!-- Ícono normal -->
                                    <svg wire:loading.remove wire:target="editCustomer" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>

                                    <!-- Ícono de loading -->
                                    <svg wire:loading wire:target="editCustomer" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
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
                                class="w-full px-3 py-2 text-sm border-2 border-gray-200 focus:border-indigo-500 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all outline-none"
                                @keydown.enter="$wire.searchCustomer()">

                            <!-- Resultados de búsqueda -->
                            @if(!empty($customerResults))
                            <div class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden max-h-60 overflow-y-auto">
                                @foreach($customerResults as $result)
                                <button
                                    wire:click="selectCustomer({{ $result['id'] }})"
                                    class="w-full text-left px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">
                                        {{ $result['businessName'] ?: ($result['firstName'] . ' ' . $result['lastName']) }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        {{ $result['identification'] }}
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Lista de productos en el cotizador con scroll interno -->
            <div class="flex-1 overflow-y-auto min-h-0 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-700" style="max-height: calc(100vh - 400px);">
                @if(empty($quoterItems))
                <div class="flex flex-col items-center justify-center h-full p-6 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Agregar items</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selecciona productos de la lista para agregarlos a tu cotización
                    </p>
                </div>
                @else
                <div class="p-4 space-y-3 pb-4">
                    @foreach($quoterItems as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                <div class="flex items-center gap-3 mt-1">
                                    @if(isset($item['price_label']))
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">Precio: {{ $item['price_label'] }}</p>
                                    @endif
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">Impuesto: {{ $item['tax_label']}}</p>
                                </div>
                            </div>
                            <button wire:click="removeFromQuoter({{ $index }})"
                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 ml-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <label for="quantity-{{ $index }}" class="text-xs font-medium text-gray-500 dark:text-gray-400">Cant:</label>
                                <input
                                    id="quantity-{{ $index }}"
                                    type="number"
                                    wire:model.lazy="quoterItems.{{ $index }}.quantity"
                                    wire:change="validateQuantity({{ $index }})"
                                    min="1"
                                    max="999999"
                                    step="1"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    class="min-w-16 w-auto max-w-24 px-2 py-1 text-center text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    value="{{ $item['quantity'] }}"
                                    onwheel="this.blur()"
                                    autocomplete="off">

                            </div>

                            <!-- <div class="flex items-center space-x-2">
                                <label for="quantity-{{ $index }}" class="text-xs font-medium text-gray-500 dark:text-gray-400">Desc:</label>
                                <input
                                    id="quantity-{{ $index }}"
                                    type="number"
                                    wire:model.lazy="quoterItems.{{ $index }}.quantity"
                                    wire:change="validateQuantity({{ $index }})"
                                    min="1"
                                    max="999999"
                                    step="1"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    class="min-w-16 w-auto max-w-24 px-2 py-1 text-center text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    value="{{ $item['quantity'] }}"
                                    onwheel="this.blur()"
                                    autocomplete="off">

                            </div> -->

                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                ${{ number_format($item['price'] * $item['quantity']) }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Footer del cotizador - Fijo en la parte inferior -->
            @if(!empty($quoterItems))
            <div class="border-t border-gray-200 dark:border-gray-700 p-6 flex-shrink-0 bg-white dark:bg-gray-800 sticky bottom-0">
                <div class="space-y-4">
                    <!-- Observaciones - Acordeón -->
                    <div x-data="{ open: @entangle('showObservations') }" class="w-full">

                        <button
                            @click="open = !open"
                            class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">

                            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Observaciones:
                            </span>

                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 transform transition-transform"
                                :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="mt-3">
                            <textarea
                                wire:model="observaciones"
                                rows="4"
                                placeholder="Escribe observaciones adicionales..."
                                class="block w-full p-2 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                     </textarea>
                        </div>

                    </div>


                    <!-- Total -->
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900 dark:text-white">
                        <span>Total:</span>
                        <span>${{ number_format($totalAmount, 2, ',', '.') }}</span>
                    </div>


                    @if($isEditing || $isEditingRemission)
                    <!-- Botones para edición -->
                    <div class="flex gap-2">
                        @php
                            $updateMethod = $isEditingRemission ? 'updateRemission' : 'updateQuote';
                            $updateText = $isEditingRemission ? 'Actualizar Remisión' : 'Actualizar Cotización';
                        @endphp
                        <button wire:click="{{ $updateMethod }}"
                            wire:loading.attr="disabled"
                            wire:target="{{ $updateMethod }}"
                            class="flex-1  bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed text-sm whitespace-nowrap">

                            <svg wire:loading.remove wire:target="{{ $updateMethod }}" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>

                            <svg wire:loading wire:target="{{ $updateMethod }}" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <span wire:loading.remove wire:target="{{ $updateMethod }}">{{ $updateText }}</span>
                            <span wire:loading wire:target="{{ $updateMethod }}">Actualizando...</span>
                        </button>

                        <button wire:click="cancelEditing"
                            wire:loading.attr="disabled"
                            wire:target="cancelEditing"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed text-sm whitespace-nowrap">

                            <svg wire:loading.remove wire:target="cancelEditing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>

                            <svg wire:loading wire:target="cancelEditing" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <span wire:loading.remove wire:target="cancelEditing">Cancelar</span>
                            <span wire:loading wire:target="cancelEditing">Cancelando...</span>
                        </button>
                    </div>

                    @if($isEditing)
                    <!-- Botón Confirmar Pedido (Solo en modo edición de cotización) -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="confirmOrder"
                            wire:loading.attr="disabled"
                            wire:target="confirmOrder"
                            class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg border border-green-500 dark:border-green-600 disabled:opacity-50 disabled:cursor-wait">
                            <svg wire:loading.remove wire:target="confirmOrder" class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg wire:loading wire:target="confirmOrder" class="w-5 h-5 mr-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="confirmOrder">Crear remisión</span>
                            <span wire:loading wire:target="confirmOrder">Creando remisión...</span>
                        </button>

                        <!-- Botón Facturar - Solo visible si el módulo está activo -->
                        @if($this->isInvoiceModuleActive)
                        <button wire:click="invoiceOrder"
                            wire:loading.attr="disabled"
                            wire:target="invoiceOrder"
                            class="w-full mt-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg border border-blue-500 dark:border-blue-600 disabled:opacity-50 disabled:cursor-wait">
                            <svg wire:loading.remove wire:target="invoiceOrder" class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <svg wire:loading wire:target="invoiceOrder" class="w-5 h-5 mr-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="invoiceOrder">Facturar</span>
                            <span wire:loading wire:target="invoiceOrder">Facturando...</span>
                        </button>
                        @endif
                    </div>
                    @endif

                    @else
                    <!-- Botón crear nueva cotización -->
                    @if(!$selectedCustomer)
                    <!-- Botón deshabilitado cuando no hay cliente -->
                    <button disabled
                        class="w-full bg-gray-400 dark:bg-gray-600 text-gray-200 dark:text-gray-400 font-medium py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.802-.833-2.572 0L4.242 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Seleccione un Cliente
                    </button>
                    @else
                    <!-- Botón activo con estado de carga -->
                    <button wire:click="saveQuote"
                        wire:loading.attr="disabled"
                        wire:target="saveQuote"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">

                        <!-- Ícono normal (se oculta durante la carga) -->
                        <svg wire:loading.remove wire:target="saveQuote" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>

                        <!-- Ícono de carga (se muestra durante la carga) -->
                        <svg wire:loading wire:target="saveQuote" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        <!-- Texto del botón (cambia durante la carga) -->
                        <span wire:loading.remove wire:target="saveQuote">Crear Cotización</span>
                        <span wire:loading wire:target="saveQuote">Guardando...</span>
                    </button>

                        <!-- Botón Facturar - También disponible al crear cotización -->
                        @if($this->isInvoiceModuleActive)
                        <button wire:click="invoiceOrder"
                            wire:loading.attr="disabled"
                            wire:target="invoiceOrder"
                            class="w-full mt-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg border border-blue-500 dark:border-blue-600 disabled:opacity-50 disabled:cursor-wait">
                            <svg wire:loading.remove wire:target="invoiceOrder" class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <svg wire:loading wire:target="invoiceOrder" class="w-5 h-5 mr-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="invoiceOrder">Facturar</span>
                            <span wire:loading wire:target="invoiceOrder">Facturando...</span>
                        </button>
                        @endif
                    @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
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
        <div class="w-full max-w-4xl max-h-[90vh] bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="bg-gray-800 text-white p-6 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">MÉTODOS DE PAGO</h1>
                        <p class="text-gray-300">
                            @if($selectedCustomer)
                                {{ $selectedCustomer['businessName'] ?? $selectedCustomer['firstName'] ?? 'Cliente' }}
                            @endif
                            - Total: ${{ number_format($totalAmount, 2, ',', '.') }}
                        </p>
                    </div>
                    <button wire:click="closePaymentModal"
                            class="text-gray-300 hover:text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="flex flex-1 overflow-hidden">

                <!-- Panel Izquierdo - Resumen -->
                <div class="w-1/3 bg-gray-100 p-6 border-r border-gray-300 overflow-y-auto">
                    <div class="space-y-6">

                        <!-- Total de la Venta -->
                        <div class="bg-white rounded-lg p-6 shadow">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">TOTAL VENTA</h3>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-green-600">
                                    ${{ number_format($totalAmount, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Estado del Pago -->
                        <div class="bg-white rounded-lg p-6 shadow">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">ESTADO</h3>
                            <div class="space-y-3 text-lg">
                                <div class="flex justify-between">
                                    <span>Pagado:</span>
                                    <span class="font-bold text-blue-600">${{ number_format($totalPaid, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-3">
                                    <span>Falta:</span>
                                    <span class="font-bold text-red-600">${{ number_format($remainingBalance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Panel Derecho - Métodos de Pago -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">FORMA DE PAGO</h2>

                    <!-- Tabla de Métodos de Pago -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                        <!-- Header de la Tabla -->
                        <div class="bg-gray-800 text-white p-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-xl font-bold text-center">MÉTODO</div>
                                <div class="text-xl font-bold text-center">VALOR</div>
                                <div class="text-xl font-bold text-center">ACCIÓN</div>
                            </div>
                        </div>

                        <!-- Filas de Métodos de Pago -->
                        <div class="divide-y divide-gray-200">
                            @foreach($paymentMethods as $key => $method)
                            <div class="grid grid-cols-3 gap-4 p-6 items-center
                                @if($currentPaymentMethod === $key) bg-yellow-100 border-l-4 border-yellow-500 @endif
                                @if($method['value'] > 0 && $currentPaymentMethod !== $key) bg-blue-50 border-l-4 border-blue-400 @endif">

                                <!-- Nombre del Método -->
                                <div class="text-2xl font-semibold text-gray-800 flex items-center cursor-pointer"
                                     wire:click="selectPaymentMethod('{{ $key }}')">
                                    @if($currentPaymentMethod === $key)
                                        <span class="mr-3 text-yellow-500">▶</span>
                                    @elseif($method['value'] > 0)
                                        <span class="mr-3 text-blue-500">●</span>
                                    @endif
                                    {{ $method['name'] }}
                                </div>

                                <!-- Valor Pagado -->
                                <div class="text-center">
                                    <input type="number"
                                           wire:model.live="paymentMethods.{{ $key }}.value"
                                           wire:change="calculatePaymentBalances"
                                           class="w-full text-2xl font-bold text-center py-3 px-4 border-2 rounded-lg
                                               @if($currentPaymentMethod === $key) border-yellow-500 bg-yellow-50 @else border-gray-300 @endif
                                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0"
                                           min="0"
                                           step="1000">
                                </div>

                                <!-- Botón de Acción -->
                                <div class="text-center">
                                    <button wire:click="selectPaymentMethod('{{ $key }}'); payTotalWithCurrentMethod();"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all">
                                        PAGAR TODO
                                    </button>
                                </div>

                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-8 flex justify-center gap-4">
                        <button wire:click="closePaymentModal"
                                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-8 rounded-lg text-lg">
                            CANCELAR
                        </button>

                        @if($canProceedToPayment && $remainingBalance == 0)
                        <button wire:click="confirmPayment"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-8 rounded-lg text-lg">
                            CONFIRMAR PAGO Y FACTURAR
                        </button>
                        @else
                        <button disabled
                                class="bg-gray-400 text-gray-200 font-bold py-4 px-8 rounded-lg text-lg cursor-not-allowed">
                            @if($remainingBalance > 0)
                                FALTA ${{ number_format($remainingBalance, 0, ',', '.') }}
                            @elseif($remainingBalance < 0)
                                SOBRA ${{ number_format(abs($remainingBalance), 0, ',', '.') }}
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

</div>