<div class="py-4"
     x-data="{ 
        cart: [],
        addToCart(id, code, name, price, label) {
            let exists = this.cart.find(item => item.id === id && item.label === label);
            if (exists) {
                exists.qty++;
            } else {
                this.cart.push({ id, code, name, price, label, qty: 1 });
            }
        },
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        updateQty(index, delta) {
            this.cart[index].qty += delta;
            if (this.cart[index].qty < 1) this.cart[index].qty = 1;
        },
        get total() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },
        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        }
     }"
>
    <!-- Layout con sidebar sticky -->
    <div class="flex gap-0">
        <div class="flex-1 min-w-0 px-4 sm:px-6">

            <!-- Encabezado compacto -->
            <div class="mb-4">
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Portal de Autogestión Comercial
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Visualiza nuestro inventario limitado en tiempo real, consulta especificaciones y monta tus pedidos de forma directa.
                </p>
            </div>

            <!-- Barra de búsqueda y filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-4">
                <div class="p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Buscar productos..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <select wire:model.live="selectedCategory"
                            class="block w-full lg:w-48 py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categorías en pills -->
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <button 
                            wire:click="$set('selectedCategory', '')"
                            class="px-3 py-1 text-[11px] font-semibold rounded-md border transition-colors {{ $selectedCategory === '' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-700' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}"
                        >
                            Todos
                        </button>
                        @foreach($categories as $category)
                            <button 
                                wire:click="$set('selectedCategory', '{{ $category->id }}')"
                                class="px-3 py-1 text-[11px] font-semibold rounded-md border transition-colors {{ $selectedCategory == $category->id ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-700' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}"
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TABLA DE PRODUCTOS (estilo cotizador) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Encabezado de la tabla -->
                <div class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center px-4 py-2.5 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <div class="w-10 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0 pl-3">NOMBRE</div>
                        <div class="w-20 text-center flex-shrink-0">DISP.</div>
                        <div class="w-28 text-center flex-shrink-0">CONTADO</div>
                        <div class="w-28 text-center flex-shrink-0">CRÉDITO</div>
                        <div class="w-10 text-center flex-shrink-0"></div>
                    </div>
                </div>

                <!-- Filas de productos -->
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($products as $product)
                        @php
                            $prices = $product->all_prices;
                            $priceCash = $prices['Precio Mínimo'] ?? ($prices['Precio Regular'] ?? 0);
                            $priceCredit = $prices['Precio Crédito'] ?? null;
                            
                            $realStock = ($product->total_stock ?? 0) - ($product->reserved_stock ?? 0);
                            $visibleStock = round($realStock * 0.30);
                            if ($realStock > 100) { $visibleStock = 30; }
                            if ($visibleStock < 0) { $visibleStock = 0; }
                            
                            $imageUrl = $product->getPrincipalThumbnailUrl('COMERCIAL');
                        @endphp
                        <div class="flex items-center px-4 py-3 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors group"
                             x-data="{ showDetail: false }">
                            
                            <!-- Miniatura -->
                            <div class="w-10 h-10 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <img src="{{ $imageUrl }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            </div>

                            <!-- Nombre y código -->
                            <div class="flex-1 min-w-0 pl-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded">{{ $product->internal_code }}</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $product->name }}</span>
                                </div>
                                @if($product->description)
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ Str::limit($product->description, 80) }}</p>
                                @endif
                            </div>

                            <!-- Stock disponible -->
                            <div class="w-20 text-center flex-shrink-0">
                                @if($visibleStock > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                        {{ $visibleStock }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                                        0
                                    </span>
                                @endif
                            </div>

                            <!-- Precio Contado (botón clickeable) -->
                            <div class="w-28 text-center flex-shrink-0 px-1">
                                @if($priceCash > 0)
                                    <button 
                                        @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ addslashes($product->name) }}', {{ $priceCash }}, 'Contado')"
                                        class="w-full py-1.5 px-2 rounded-lg border border-emerald-400/40 bg-emerald-50 dark:bg-emerald-900/15 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 active:scale-95 transition-all"
                                    >
                                        <div class="text-[11px] font-black text-emerald-700 dark:text-emerald-300">${{ number_format($priceCash, 0, ',', '.') }}</div>
                                    </button>
                                @else
                                    <span class="text-[10px] text-gray-400">N/A</span>
                                @endif
                            </div>

                            <!-- Precio Crédito (botón clickeable) -->
                            <div class="w-28 text-center flex-shrink-0 px-1">
                                @if($priceCredit && $priceCredit > 0)
                                    <button 
                                        @click="addToCart({{ $product->id }}, '{{ $product->internal_code }}', '{{ addslashes($product->name) }}', {{ $priceCredit }}, 'Crédito')"
                                        class="w-full py-1.5 px-2 rounded-lg border border-yellow-400/40 bg-yellow-50 dark:bg-yellow-900/15 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 active:scale-95 transition-all"
                                    >
                                        <div class="text-[11px] font-black text-yellow-700 dark:text-yellow-300">${{ number_format($priceCredit, 0, ',', '.') }}</div>
                                    </button>
                                @else
                                    <span class="text-[10px] text-gray-400">N/A</span>
                                @endif
                            </div>

                            <!-- Botón detalle -->
                            <div class="w-10 text-center flex-shrink-0">
                                <button @click="showDetail = !showDetail" 
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="showDetail ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Panel de detalle expandible -->
                        <div x-show="showDetail" x-collapse 
                             class="px-4 py-3 bg-gray-50/80 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex gap-4 ml-[52px]">
                                <!-- Imagen grande -->
                                <div class="w-32 h-32 rounded-lg overflow-hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex-shrink-0 shadow-sm">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
                                </div>
                                <!-- Info expandida -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $product->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $product->description ?: 'Sin observaciones técnicas registradas para este producto.' }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-3">
                                        <div class="text-[10px]">
                                            <span class="text-gray-400">Código:</span>
                                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $product->internal_code }}</span>
                                        </div>
                                        @if($product->sku)
                                        <div class="text-[10px]">
                                            <span class="text-gray-400">SKU:</span>
                                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $product->sku }}</span>
                                        </div>
                                        @endif
                                        <div class="text-[10px]">
                                            <span class="text-gray-400">Disponibilidad:</span>
                                            <span class="font-bold {{ $visibleStock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $visibleStock }} unidades (cupo autogestión)</span>
                                        </div>
                                    </div>
                                </div>
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

            <!-- Paginación -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>

        <!-- SIDEBAR (estilo cotizador) -->
        <div class="hidden lg:block w-96 flex-shrink-0 sticky top-16 h-[calc(100vh-4rem)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 overflow-y-auto self-start">
            <div class="p-5">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span x-text="totalItems + ' Productos seleccionados'">0 Productos seleccionados</span>
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
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold text-gray-900 dark:text-white line-clamp-2" x-text="item.name"></p>
                                    <p class="text-[9px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                        <span x-text="'COD: ' + item.code"></span> · 
                                        <span class="font-bold px-1 py-0.5 rounded text-[8px]" 
                                              :class="item.label === 'Contado' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'"
                                              x-text="item.label"></span>
                                    </p>
                                </div>
                                <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0 p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-0.5 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-600">
                                    <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-gray-800 dark:hover:text-white text-sm font-bold">−</button>
                                    <span class="w-7 text-center text-xs font-bold text-gray-900 dark:text-white" x-text="item.qty"></span>
                                    <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-gray-800 dark:hover:text-white text-sm font-bold">+</button>
                                </div>
                                <span class="text-sm font-extrabold text-gray-800 dark:text-gray-200" x-text="'$' + (item.price * item.qty).toLocaleString('es-CO')"></span>
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

                    <!-- Dirección -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Confirmación de Dirección de Envío</label>
                        <input type="text" placeholder="Ej. Calle 45 Sur #78 B 16 - Bogotá" 
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                    </div>

                    <!-- Comprobante -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-700 dark:text-gray-300 block mb-1">Comprobante de Pago (Obligatorio)</label>
                        <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center cursor-pointer hover:border-indigo-400 transition-colors bg-gray-50 dark:bg-gray-900/50">
                            <input type="file" class="hidden" id="receipt-upload">
                            <label for="receipt-upload" class="cursor-pointer block">
                                <svg class="w-6 h-6 text-gray-400 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">Arrastrar o seleccionar comprobante</span>
                                <span class="text-[8px] text-gray-400 block">Formatos: PDF, JPG, PNG (Max 5MB)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Botón enviar -->
                    <button 
                        :disabled="cart.length === 0"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-sm text-sm flex items-center justify-center gap-2"
                        @click="alert('¡Tu pedido B2B y comprobante de pago han sido enviados para verificación!')"
                    >
                        🚀 Enviar Pedido para Verificación
                    </button>
                    <p class="text-[9px] text-gray-400 text-center leading-relaxed">
                        Al enviar el pedido, queda en estado <strong>Por verificar</strong> hasta que un auxiliar comercial apruebe y genere la OP correspondiente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
