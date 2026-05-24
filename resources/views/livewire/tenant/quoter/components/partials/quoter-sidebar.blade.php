<!-- Header del cotizador -->
<div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
    {{-- Badge de cotización en edición --}}
    @if($isEditing && $editingQuoteConsecutive)
    <div class="flex items-center gap-1.5 mb-2 px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700">
        <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <span class="text-xs font-bold text-amber-700 dark:text-amber-300">
            Editando cotización <span class="text-amber-900 dark:text-amber-100">#{{ $editingQuoteConsecutive }}</span>
        </span>
    </div>
    @endif
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

                    @if(!empty($selectedCustomer['address']))
                    <p class="text-[10px] text-green-700 dark:text-green-300 mt-1">
                        <span class="font-bold">Dirección:</span> {{ $selectedCustomer['address'] }}
                    </p>
                    @endif

                    @if(!empty($selectedCustomer['cityName']))
                    <p class="text-[10px] text-green-700 dark:text-green-300">
                        <span class="font-bold">Ciudad:</span> {{ $selectedCustomer['cityName'] }}
                    </p>
                    @endif
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

            <!-- Selector de Sucursales (si aplica) -->
            @if(!empty($branches) && count($branches) > 1 && !$isEditing)
            <div class="mt-3 pt-3 border-t border-green-200 dark:border-green-700">
                <label class="block text-[10px] font-bold text-green-700 dark:text-green-300 uppercase mb-1">
                    Seleccionar Agencia/Sucursal
                </label>
                <select 
                    wire:model.live="selectedBranchId"
                    wire:change="selectBranch($event.target.value)"
                    class="block w-full text-xs border-green-300 dark:border-green-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-green-500 focus:border-green-500 shadow-sm"
                >
                    <option value="">-- Seleccione una sucursal --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch['id'] }}">
                            {{ $branch['name'] }} {{ !empty($branch['city']['name']) ? '('.$branch['city']['name'].')' : '' }}
                        </option>
                    @endforeach
                </select>
                
                @if($selectedBranchId)
                <div class="mt-2 flex items-center gap-1 text-[10px] text-green-600 dark:text-green-400 font-medium bg-green-100/50 dark:bg-green-900/30 p-1.5 rounded-md">
                    <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Sucursal lista para facturar</span>
                </div>
                @else
                <div class="mt-2 flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-bold animate-pulse">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>Debe seleccionar una sucursal</span>
                </div>
                @endif
            </div>
            @endif
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
                :simplified="true"
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
<div class="flex-1 overflow-y-auto min-h-0 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-700">
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
    <div class="px-3 py-2 space-y-1">
        <!-- Bar de Flete Slim Interactivo -->
        @if($totalWeight > 0 || $estimatedFreight > 0)
        <div 
            wire:click="applyFreightToQuoter"
            class="mb-2 flex items-center justify-between px-4 py-2 {{ $isFreightApplied ? 'bg-indigo-50 dark:bg-indigo-900/40 border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/60' : 'bg-red-50 dark:bg-red-900/40 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/60' }} border rounded-lg cursor-pointer transition-all group shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-medium {{ $isFreightApplied ? 'text-indigo-700 dark:text-indigo-300' : 'text-red-700 dark:text-red-300' }}">Valor Flete Estimado:</span>
                <span class="text-xs font-black {{ $isFreightApplied ? 'text-indigo-800 dark:text-white' : 'text-red-800 dark:text-white' }}">${{ number_format($estimatedFreight, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-bold {{ $isFreightApplied ? 'text-indigo-500/60 dark:text-indigo-400/60' : 'text-red-500/60 dark:text-red-400/60' }}">{{ number_format($totalWeight, 2, ',', '.') }} Kg</span>
                <div class="{{ $isFreightApplied ? 'bg-indigo-600 group-hover:bg-indigo-700' : 'bg-red-600 group-hover:bg-red-700' }} text-white p-1 rounded-md transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif
        @foreach($quoterItems as $index => $item)
        @php
            $itemTotalStock  = $item['total_stock'] ?? null;
            $sidebarInsufficient = $itemTotalStock !== null && $item['quantity'] > $itemTotalStock;
            $itemMinPrice    = (float) ($item['min_price'] ?? 0);
            $itemOrigPrice   = (float) ($item['original_price'] ?? $item['price']);
            $itemMaxPrice    = $itemOrigPrice * 2;
            $hasPriceRange   = $itemMinPrice > 0 || $itemMaxPrice > 0;
        @endphp
        <div class="flex items-center gap-2 py-1.5 px-2 rounded border
            {{ $sidebarInsufficient
                ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700'
                : 'bg-gray-50 dark:bg-gray-700 border-gray-100 dark:border-gray-600' }}">
            <!-- Nombre + precio unitario -->
            <div class="flex-1 min-w-0"
                 x-data="{
                    editing: false,
                    price: {{ (float) $item['price'] }},
                    minPrice: {{ $itemMinPrice }},
                    maxPrice: {{ $itemMaxPrice }},
                    error: '',
                    open() { this.editing = true; this.error = ''; this.$nextTick(() => this.$refs.priceInput?.select()); },
                    cancel() { this.editing = false; this.price = {{ (float) $item['price'] }}; this.error = ''; },
                    save() {
                        const v = parseFloat(this.price);
                        if (isNaN(v) || v <= 0) { this.error = 'Precio inválido'; return; }
                        if (this.minPrice > 0 && v < this.minPrice) {
                            this.error = 'Mín: $' + this.minPrice.toLocaleString('es-CO', {maximumFractionDigits:0});
                            return;
                        }
                        if (v > this.maxPrice) {
                            this.error = 'Máx: $' + this.maxPrice.toLocaleString('es-CO', {maximumFractionDigits:0});
                            return;
                        }
                        $wire.updateItemPrice({{ $index }}, v);
                        this.editing = false;
                        this.error = '';
                    }
                 }">
                <p class="text-xs font-medium text-gray-900 dark:text-white truncate leading-tight" title="{{ $item['name'] }}">
                    <span class="text-gray-400 dark:text-gray-500 font-normal">{{ $item['sku'] }} · </span>{{ $item['name'] }}
                </p>

                <!-- Precio: vista normal -->
                <div x-show="!editing" class="flex items-center gap-1 leading-tight">
                    <p class="text-[10px] text-indigo-500 dark:text-indigo-400">
                        ${{ number_format($item['price']) }} · {{ $item['tax_label'] }}
                        @if(isset($item['price_label']) && $item['price_label'] !== 'Precio seleccionado' && $item['price_label'] !== 'Precio Regular')
                        <span class="mx-0.5 font-bold text-emerald-600 dark:text-emerald-400">· {{ $item['price_label'] }}</span>
                        @endif
                    </p>
                    <!-- Botón lápiz para editar precio -->
                    <button @click="open()"
                        class="p-0.5 text-gray-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors flex-shrink-0"
                        title="Editar precio">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                </div>

                <!-- Precio: modo edición -->
                <div x-show="editing" x-cloak class="mt-0.5">
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] text-gray-500">$</span>
                        <input x-ref="priceInput"
                            x-model.number="price"
                            @keydown.enter="save()"
                            @keydown.escape="cancel()"
                            type="number"
                            min="{{ $itemMinPrice > 0 ? $itemMinPrice : 1 }}"
                            max="{{ $itemMaxPrice }}"
                            step="1"
                            class="w-24 px-1 py-0.5 text-xs font-medium border rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500
                                [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            :class="error ? 'border-red-400' : 'border-indigo-400'">
                        <!-- Confirmar -->
                        <button @click="save()"
                            class="p-0.5 text-green-600 hover:text-green-700 transition-colors" title="Guardar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <!-- Cancelar -->
                        <button @click="cancel()"
                            class="p-0.5 text-red-400 hover:text-red-600 transition-colors" title="Cancelar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Rango permitido + error -->
                    <div class="flex items-center justify-between mt-0.5">
                        <p class="text-[8px] text-gray-400 leading-tight">
                            @if($itemMinPrice > 0)
                                Mín ${{ number_format($itemMinPrice, 0, ',', '.') }}
                            @endif
                            · Máx ${{ number_format($itemMaxPrice, 0, ',', '.') }}
                        </p>
                        <p x-show="error" x-text="error" class="text-[8px] font-bold text-red-500 leading-tight"></p>
                    </div>
                </div>

                @if($sidebarInsufficient)
                <p class="text-[9px] font-bold text-orange-600 dark:text-orange-400 leading-tight mt-0.5 flex items-center gap-0.5">
                    <span>⚠️</span> Stock insuficiente · disponible: {{ (int)$itemTotalStock }}
                </p>
                @endif
            </div>
            <!-- Cantidad -->
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
                class="w-12 px-1 py-0.5 text-center text-xs font-medium border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
            >

            <!-- Botón Eliminar -->
            <button 
                wire:click="removeFromQuoter({{ $index }})"
                wire:loading.attr="disabled"
                class="p-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                title="Eliminar item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Footer del cotizador - Fijo en la parte inferior -->
@if(!empty($quoterItems))
<div class="border-t border-gray-200 dark:border-gray-700 p-6 flex-shrink-0 bg-white dark:bg-gray-800">
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

        <!-- Desglose de Impuestos -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg px-3 py-2 mb-3 space-y-0.5 text-xs">
            @php
                // Buscar todos los descuentos aplicados (3%, 5%, 7%) y listarlos una sola vez
                $discountPercents = collect($quoterItems)
                    ->pluck('price_label')
                    ->filter(function($label) {
                        return in_array(trim($label), ['3%', '5%', '7%']);
                    })
                    ->unique()
                    ->values();
            @endphp
            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>Subtotal:</span>
                <span>${{ number_format($subTotal, 2, ',', '.') }}</span>
            </div>
            @if($taxBreakdown['iva_5'] > 0)
            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>IVA 5%:</span>
                <span>${{ number_format($taxBreakdown['iva_5'], 2, ',', '.') }}</span>
            </div>
            @endif
            @if($taxBreakdown['iva_19'] > 0)
            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>IVA 19%:</span>
                <span>${{ number_format($taxBreakdown['iva_19'], 2, ',', '.') }}</span>
            </div>
            @endif
            @if($taxBreakdown['exento'] > 0)
            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>Exento:</span>
                <span>${{ number_format($taxBreakdown['exento'], 2, ',', '.') }}</span>
            </div>
            @endif
            @if($appliedFreight > 0 || $isFreightApplied)
            <div class="flex flex-col gap-1 border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-medium">
                        <span>Flete:</span>
                        <button wire:click="removeFreight" class="text-red-500 hover:text-red-700 transition-colors" title="Eliminar flete">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-500 mr-1">$</span>
                        <input type="number" wire:model.live.debounce.500ms="appliedFreight" class="w-24 text-right px-1 py-0.5 text-xs font-medium border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 focus:ring-1 focus:ring-indigo-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>
                </div>
                @if($isFreightManuallyEdited)
                <div class="mt-1">
                    <input type="text" wire:model.live="freightJustification" placeholder="Justificación del cambio de flete..." class="w-full px-2 py-1 text-[11px] border border-red-300 focus:border-red-500 rounded bg-red-50 dark:bg-red-900/20 text-gray-900 dark:text-white placeholder-red-400 outline-none" required>
                </div>
                @endif
            </div>
            @endif
            @if($totalTaxes > 0)
            <div class="flex justify-between text-gray-700 dark:text-gray-300 border-t border-gray-200 dark:border-gray-600 pt-1 mt-1">
                <span class="font-medium">Total impuestos:</span>
                <span class="font-semibold">${{ number_format($totalTaxes, 2, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <!-- Total -->
        <div class="flex justify-between items-center text-lg font-bold text-gray-900 dark:text-white bg-green-50 dark:bg-green-900 rounded-lg p-3">
            <span>Total:</span>
            <span class="text-green-600 dark:text-green-400">${{ number_format($totalAmount, 2, ',', '.') }}</span>
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
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span wire:loading.remove wire:target="cancelEditing">Cancelar</span>
                <span wire:loading wire:target="cancelEditing">Cancelando...</span>
            </button>
        </div>

        @if($isEditing)
        <!-- Botón Confirmar Pedido -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">

            @if($hasChanges)
            {{-- ⚠️ Advertencia: cambios sin guardar --}}
            <div class="mb-3 flex items-start gap-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 rounded-lg px-3 py-2.5">
                <svg class="w-4 h-4 mt-0.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="text-xs text-amber-700 dark:text-amber-300 font-medium leading-tight">
                    Tienes cambios sin guardar. <span class="font-bold">Actualiza la cotización</span> antes de crear la OP.
                </p>
            </div>
            <button disabled
                class="w-full bg-amber-400/60 dark:bg-amber-700/40 text-amber-800/70 dark:text-amber-300/70 font-semibold py-3 px-4 rounded-lg flex items-center justify-center cursor-not-allowed border border-amber-300 dark:border-amber-700 opacity-70 select-none">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Crear OP (Guarda primero)
            </button>
            @else
            <button wire:click="confirmOrder"
                wire:loading.attr="disabled"
                wire:target="confirmOrder"
                class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg border border-green-500 dark:border-green-600 disabled:opacity-50 disabled:cursor-wait">
                <svg wire:loading.remove wire:target="confirmOrder" class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg wire:loading wire:target="confirmOrder" class="w-5 h-5 mr-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="confirmOrder">Crear OP</span>
                <span wire:loading wire:target="confirmOrder">Creando OP...</span>
            </button>
            @endif

            @if($this->canShowInvoiceButton)
            <button wire:click="invoiceOrder"
                wire:loading.attr="disabled"
                wire:target="invoiceOrder"
                class="w-full mt-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-200 shadow-md hover:shadow-lg border border-blue-500 dark:border-blue-600 disabled:opacity-50 disabled:cursor-wait">
                <svg wire:loading.remove wire:target="invoiceOrder" class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <svg wire:loading wire:target="invoiceOrder" class="w-5 h-5 mr-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="invoiceOrder">Facturar</span>
                <span wire:loading wire:target="invoiceOrder">Facturando...</span>
            </button>
            @endif
        </div>
        @endif
        @else
        @if(!$selectedCustomer)
        <button disabled
            class="w-full bg-gray-400 dark:bg-gray-600 text-gray-200 dark:text-gray-400 font-medium py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.802-.833-2.572 0L4.242 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Seleccione un Cliente
        </button>
        @else
        <button wire:click="saveQuote"
            wire:loading.attr="disabled"
            wire:target="saveQuote"
            class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
            <svg wire:loading.remove wire:target="saveQuote" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            <svg wire:loading wire:target="saveQuote" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove wire:target="saveQuote">Crear Cotización</span>
            <span wire:loading wire:target="saveQuote">Guardando...</span>
        </button>
        @endif
        @endif
    </div>
</div>
@endif
