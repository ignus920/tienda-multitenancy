
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 ">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            
        </div>

        <!-- Search Input and Add Button - Sticky -->
        <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm" x-data="{ showFilters: false }">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between mb-3">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">Cotizaciones</h1>
                    <div class="flex items-center gap-2">
                        <button @click="showFilters = !showFilters" 
                            class="p-2 text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg transition-colors flex items-center gap-1 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filtros
                        </button>
                        <button
                            wire:click="nuevaCotizacion"
                            wire:loading.attr="disabled"
                            wire:target="nuevaCotizacion"
                            class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow-sm transition-all duration-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                        @include('livewire.tenant.parameters.dynamic-buttons', ['buttons' => $this->dynamicButtons])
                    </div>
                </div>

                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Búsqueda rápida..."
                        class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                    >
                </div>

                <!-- Panel de Filtros Colapsable -->
                <div x-show="showFilters" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-3 pb-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">NIT / Cédula</label>
                            <input type="text" wire:model.live="filterNit" placeholder="Ej: 900..."
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Consecutivo</label>
                            <input type="text" wire:model.live="filterConsecutive" placeholder="Ej: COT-1"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Nombre / Razón Social</label>
                        <input type="text" wire:model.live="filterName" placeholder="Buscar cliente..."
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Desde</label>
                            <input type="date" wire:model.live="filterDateFrom"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Hasta</label>
                            <input type="date" wire:model.live="filterDateTo"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <select wire:model.live="perPage"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm font-medium">
                                <option value="10">Mostrar 10</option>
                                <option value="25">Mostrar 25</option>
                                <option value="50">Mostrar 50</option>
                            </select>
                        </div>
                        <button wire:click="clearFilters" 
                            class="px-4 py-2 bg-red-50 text-red-500 dark:bg-red-900/20 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Lista de Registros</span>
                    <span class="text-[10px] text-gray-500">{{ $quotes->total() }} encontrados</span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="px-4 py-4">
            <!-- Success Message -->
            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Quotes List -->
            <div class="space-y-4">
            @forelse($quotes as $quote)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <!-- Quote Header -->
                    <div class="bg-gray-800 dark:bg-gray-700 text-white p-3 rounded-t-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-semibold">Cotización #{{ $quote->consecutive }}</span>
                                @if($quote->customer)
                                    <br><span class="text-sm text-gray-300">{{ $quote->customer->short_name }}</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-sm">{{ $quote->created_at->format('d/m/Y') }}</span>
                                <br><span class="text-xs text-gray-300">{{ $quote->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quote Content -->
                    <div class="p-4">
                        <!-- Cliente Information -->
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $quote->customer_name }}
                                @if($quote->customer && $quote->customer->billingEmail)
                                    <br><small class="text-gray-500">{{ $quote->customer->billingEmail }}</small>
                                @endif
                                </p>
                                
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($quote->typeQuote === 'POS') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @endif">
                                    {{ $quote->typeQuote }}
                                    
                                </span>
                            </div>

                            @if($quote->customer)
                                <p class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $quote->customer->full_name }}
                                </p>
                                @if($quote->customer->email)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $quote->customer->email }}</p>
                                @endif
                                @if($quote->customer->business_phone)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">📞 {{ $quote->customer->business_phone }}</p>
                                @endif
                            @else
                                <p class="text-base text-gray-400">Sin cliente asignado</p>
                            @endif
                        </div>

                        <!-- Status and Warehouse -->
                        <div class="mb-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($quote->status === 'REGISTRADO') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($quote->status === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($quote->status === 'FACTURADO') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                    {{ $quote->status }}
                                </span>
                            </div>

                            @if(isset($quote->storage_name))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bodega</span>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        {{ $quote->storage_name }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vendedor</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $quote->seller_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Observations if any -->
                        @if($quote->observations)
                            <div class="mb-3">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Observaciones</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $quote->observations }}</p>
                            </div>
                        @endif

                        <!-- Actions - Botones de acción para cada cotización -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Botón Ver Detalle -->
                            <button
                                wire:click="viewDetails({{ $quote->id }})"
                                wire:loading.attr="disabled"
                                wire:target="viewDetails({{ $quote->id }})"
                                class="flex-1 bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-all duration-200">
                                
                                <div wire:loading wire:target="viewDetails({{ $quote->id }})" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <div wire:loading.remove wire:target="viewDetails({{ $quote->id }})" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span>Detalles</span>
                                </div>
                            </button>

                            <!-- Botón Ir al Carrito -->
                            @if($quote->status !== 'REMISIÓN' && $quote->status !== 'FACTURADO' && $quote->status !== 'ANULADO')
                            <button
                                wire:click="irAlCarrito({{ $quote->id }})"
                                wire:loading.attr="disabled"
                                wire:target="irAlCarrito({{ $quote->id }})"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed text-white px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-all duration-200">

                                <!-- Spinner de loading (se muestra cuando está cargando) -->
                                <div wire:loading wire:target="irAlCarrito({{ $quote->id }})" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Abriendo...</span>
                                </div>

                                <!-- Contenido normal (se oculta cuando está cargando) -->
                                <div wire:loading.remove wire:target="irAlCarrito({{ $quote->id }})" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5-5a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span>Carrito</span>
                                </div>
                            </button>
                            @endif

                            <!-- Botón Imprimir -->
                            <button
                                wire:click="printQuote({{ $quote->id }})"
                                wire:loading.attr="disabled"
                                wire:target="printQuote({{ $quote->id }})"
                                class="bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed text-white p-2 rounded-lg transition-all duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                title="Imprimir cotización"
                            >
                                <!-- Spinner de loading (se muestra cuando está cargando) -->
                                <div wire:loading wire:target="printQuote({{ $quote->id }})">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <!-- Ícono normal (se oculta cuando está cargando) -->
                                <div wire:loading.remove wire:target="printQuote({{ $quote->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </div>
                            </button>

                            <!-- Botón Imprimir Factura (Solo si está facturado) -->
                            @if($quote->status === 'FACTURADO')
                            <button
                                wire:click="printInvoice({{ $quote->id }})"
                                wire:loading.attr="disabled"
                                wire:target="printInvoice({{ $quote->id }})"
                                class="bg-green-600 hover:bg-green-700 disabled:bg-green-300 disabled:cursor-not-allowed text-white p-2 rounded-lg transition-all duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                title="Imprimir factura"
                            >
                                <!-- Spinner de loading -->
                                <div wire:loading wire:target="printInvoice({{ $quote->id }})">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <!-- Ícono normal -->
                                <div wire:loading.remove wire:target="printInvoice({{ $quote->id }})">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </button>
                            @endif

                            <!-- Botón Eliminar -->
                            <button
                                wire:click="eliminar({{ $quote->id }})"
                                wire:loading.attr="disabled"
                                wire:target="eliminar({{ $quote->id }})"
                                onclick="return confirm('¿Está seguro de eliminar esta cotización?')"
                                class="bg-red-500 hover:bg-red-600 disabled:bg-red-300 disabled:cursor-not-allowed text-white p-2 rounded-lg transition-all duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                title="Eliminar cotización"
                            >
                                <!-- Spinner de loading (se muestra cuando está cargando) -->
                                <div wire:loading wire:target="eliminar({{ $quote->id }})">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <!-- Ícono normal (se oculta cuando está cargando) -->
                                <div wire:loading.remove wire:target="eliminar({{ $quote->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
                            @if($search)
                                Sin resultados
                            @else
                                No hay cotizaciones
                            @endif
                        </h3>
                        <p class="mb-6 text-sm">
                            @if($search)
                                No se encontraron cotizaciones que coincidan con "{{ $search }}".
                            @else
                                Comienza creando tu primera cotización.
                            @endif
                        </p>
                        @if(!$search)
                            <button
                                wire:click="nuevaCotizacion"
                                wire:loading.attr="disabled"
                                wire:target="nuevaCotizacion"
                                class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2"
                            >
                                <!-- Spinner de loading -->
                                <div wire:loading wire:target="nuevaCotizacion" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Creando...</span>
                                </div>

                                <!-- Texto normal -->
                                <span wire:loading.remove wire:target="nuevaCotizacion">Crear Primera Cotización</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

            <!-- Pagination -->
            @if($quotes->hasPages())
                <div class="mt-6">
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>

    <!-- Modal de Detalle de Cotización -->
    <div x-data="{ show: @entangle('showDetailModal') }"
         x-show="show"
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false">
                <div class="absolute inset-0 bg-gray-500 dark:bg-slate-900 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-slate-700 w-full"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                @if($this->selectedQuote)
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Detalles #{{ $this->selectedQuote->consecutive }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                {{ $this->selectedQuote->created_at->format('d/m/Y H:i') }} | 
                                <span class="font-medium text-indigo-500">{{ $this->selectedQuote->status }}</span>
                            </p>
                        </div>
                        <button @click="show = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-slate-300 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-4 mb-6">
                            <!-- Info Cliente -->
                            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-4 border border-gray-100 dark:border-slate-700">
                                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-3">Cliente</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">Nombre:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white text-right">{{ $this->selectedQuote->customer_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">ID:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $this->selectedQuote->customer->identification ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">Email:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white truncate ml-2">{{ $this->selectedQuote->customer->billingEmail ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles Generales -->
                            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-4 border border-gray-100 dark:border-slate-700">
                                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-3">General</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">Tipo:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $this->selectedQuote->typeQuote }}</span>
                                    </div> 
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">Bodega:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $this->selectedQuote->storage_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500 dark:text-slate-400">Vendedor:</span>
                                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $this->selectedQuote->seller_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Productos (estilo móvil) -->
                        <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-3 px-1">Productos</h4>
                        <div class="space-y-2">
                            @php $totalModal = 0; @endphp
                            @foreach($this->selectedQuote->detalles as $detalle)
                                @php 
                                    $subtotal = $detalle->quantity * $detalle->value;
                                    $totalModal += $subtotal;
                                @endphp
                                <div class="bg-white dark:bg-slate-700/30 rounded-lg p-3 border border-gray-100 dark:border-slate-700">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $detalle->item->name ?? $detalle->description }}</span>
                                        <span class="text-xs font-bold text-indigo-500">${{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400">
                                        <span>{{ number_format($detalle->quantity, 0) }} x ${{ number_format($detalle->value, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Total Footer -->
                        <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30 flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-700 dark:text-slate-300 uppercase">Total:</span>
                            <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">${{ number_format($totalModal, 2) }}</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                        <button @click="show = false" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-bold transition-all transform active:scale-95 shadow-lg shadow-indigo-500/30">
                            Cerrar
                        </button>
                    </div>
                @else
                    <!-- Error State -->
                    <div class="p-6">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-red-400 dark:text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <h3 class="text-lg font-bold text-red-800 dark:text-red-300 mb-2">Cotización no encontrada</h3>
                            <p class="text-sm text-red-600 dark:text-red-400">La cotización no fue encontrada o ha sido eliminada.</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                        <button @click="show = false" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl text-sm font-bold transition-all transform active:scale-95">
                            Cerrar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


