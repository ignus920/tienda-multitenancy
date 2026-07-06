<div>
    <!-- Mensajes de notificación -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Buscar items..." 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Programacion  -->
            <div class="w-full sm:w-48">
                @livewire('selects.generic-select', [
                'selectedValue' => null,
                'items' => $labels,
                'name' => 'selectedLabel',
                'placeholder' => $selectedLabelName,
                'label' => '',
                'required' => false,
                'showLabel' => false,
                'class' => 'block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                'eventName' => 'labelSelected',
                'displayField' => 'name',
                'valueField' => 'id',
                ], key('label-select-' . now()->timestamp))
                @error('selectedLabel') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Botón Productos Críticos -->
            <button wire:click="$toggle('filterCritical')"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider transition-all duration-200
                           {{ $filterCritical 
                              ? 'bg-red-600 hover:bg-red-700 text-white shadow ring-2 ring-red-300 dark:ring-red-900' 
                              : 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600' 
                           }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                {{ $filterCritical ? 'Ver Todos' : 'Prod. Críticos' }}
            </button>

            <!-- Per Page -->
            <select wire:model.live="perPage" 
                    class="block w-full sm:w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="5">5</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    @if(count($selectedItems) > 0)
        <div class="mb-4 p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                    {{ count($selectedItems) }}
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">ítems seleccionados. Definir prioridad en lote:</span>
            </div>
             <div class="flex items-center gap-2">
                 <button wire:click="assignPriorityToSelected('ASAP')" 
                         style="background-color: #dc2626; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity hover:opacity-90 shadow">
                     ASAP
                 </button>
                 <button wire:click="assignPriorityToSelected('Second')" 
                         style="background-color: #d97706; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity hover:opacity-90 shadow">
                     Second
                 </button>
                 <button wire:click="assignPriorityToSelected('Third')" 
                         style="background-color: #2563eb; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity hover:opacity-90 shadow">
                     Third
                 </button>
                 {{-- 
                 <button wire:click="assignPriorityToSelected(null)" 
                         style="background-color: #ffffff; color: #374151; border: 1px solid #d1d5db;"
                         class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-opacity hover:opacity-90 shadow">
                     Quitar
                 </button>
                 --}}
             </div>
        </div>
    @endif

    <!-- Vista Desktop (tabla) - oculta en móvil -->
    <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto max-h-[70vh] overflow-y-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th scope="col" class="w-10 px-6 py-3"></th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
                            wire:click="sortBy('name')">
                            <div class="flex items-center space-x-1">
                                <span>Código</span>
                                @if($sortField === 'name')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
                            wire:click="sortBy('stock_items_store')">
                            <div class="flex items-center space-x-1">
                                <span>Existencias ERP</span>
                                @if($sortField === 'stock_items_store')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
                            wire:click="sortBy('percentage')">
                            <div class="flex items-center space-x-1">
                                <span>Porcentaje</span>
                                @if($sortField === 'percentage')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
                            wire:click="sortBy('outsideMovement')">
                            <div class="flex items-center space-x-1">
                                <span>Salida ERP</span>
                                @if($sortField === 'outsideMovement')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Entrada ERP</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EXW</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($items as $item)
                        <tr x-data="{ 
                                showTooltip: false,
                                tooltipPosition: 'top'
                            }"
                            @mouseenter="
                                showTooltip = true;
                                const rect = $el.getBoundingClientRect();
                                tooltipPosition = rect.top < 100 ? 'bottom' : 'top';
                            "
                        @mouseleave="showTooltip = false"
                        class="{{ $selectedLabelId ? 'bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }} cursor-pointer transition-colors relative group"
                        wire:click="selectItem({{ $item->id }}, {{ $item->quantity ?? 0 }})">

                        <td class="px-6 py-4 whitespace-nowrap" onclick="event.stopPropagation()">
                                @php
                                    $itemCurrentQty = $selectedQuantities[$item->id] ?? $item->quantity ?? 0;
                                @endphp
                                <input type="checkbox" 
                                    wire:model.live="selectedItems" 
                                    value="{{ $item->id }}"
                                    {{ $itemCurrentQty <= 0 ? 'disabled' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 {{ $itemCurrentQty <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-100' : '' }}"
                                >
                            </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-80 transition-opacity"
                                     @click.stop="$dispatch('openImageModal', { productId: {{ $item->id }}, context: 'COMERCIAL' })">
                                    @php
                                        $thumbnail = $item->getPrincipalThumbnailUrl('COMERCIAL');
                                    @endphp
                                    <img src="{{ $thumbnail }}" 
                                         alt="Producto" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white uppercase">{{ $item->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $item->description ?? $item->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap relative">
                            <div x-show="showTooltip"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="absolute left-1/2 transform -translate-x-1/2 px-3 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-xs font-medium rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                :class="{
                                         '-top-10': tooltipPosition === 'top',
                                         'top-full mt-2': tooltipPosition === 'bottom'
                                     }"
                                style="display: none;">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"></path>
                                    </svg>
                                    Seleccionar item
                                </div>
                                <div x-show="tooltipPosition === 'bottom'"
                                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-px">
                                    <div class="border-4 border-transparent border-b-gray-900 dark:border-b-gray-700"></div>
                                </div>
                                <div x-show="tooltipPosition === 'top'"
                                    class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-px">
                                    <div class="border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                </div>
                            </div>

                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($item->stock_items_store ?? 0, 0) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" onclick="event.stopPropagation()">
                            <input type="number" 
                                min="0" 
                                step="1" 
                                value="{{ $selectedQuantities[$item->id] ?? $item->quantity ?? 0 }}"
                                @if($selectedLabelId) disabled @endif
                                @click="$wire.selectItem({{ $item->id }}, {{ $selectedQuantities[$item->id] ?? $item->quantity ?? 0 }})"
                                @change="
            $wire.updateQuantity({{ $item->id }}, $event.target.value).then(() => {
                $wire.selectItem({{ $item->id }}, parseInt($event.target.value) || 0);
            });
        "
                                title="{{ !empty($item->label_assignments) ? "Cantidades pedidas por etiqueta:\n" . $item->label_assignments : 'Sin etiquetas programadas' }}"
                                class="block w-24 px-3 py-2 text-sm font-semibold {{ $selectedLabelId ? 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' }} border {{ $selectedLabelId ? 'border-gray-300 dark:border-gray-600' : 'border-blue-200 dark:border-blue-800' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                placeholder="0">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->percentage ?? 0 }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-red-600 dark:text-red-400">{{ number_format($item->outsideMovement ?? 0, 0) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-green-600 dark:text-green-400">{{ number_format($item->insideMovement ?? 0, 0) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($item->exw ?? 0, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" 
                                        @click.stop="$dispatch('openAccessoriesModal', { itemId: {{ $item->id }} })"
                                        class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors group" 
                                        title="Ver accesorios">
                                    <svg class="w-5 h-5 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </button>
                                <button type="button" 
                                        wire:click.stop="$dispatch('open-item-history', { itemId: {{ $item->id }}, labelId: {{ $selectedLabelId ?? 'null' }}, isAllMode: {{ $selectedLabelId ? 'false' : 'true' }} })"
                                        class="p-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors group" 
                                        title="Ver historial">
                                    <svg class="w-5 h-5 text-amber-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay items</h3>
                                <p class="text-gray-500 dark:text-gray-400">No se encontraron items que coincidan con tu búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Vista Mobile (cards) - visible solo en móvil -->
    <div class="lg:hidden space-y-4">
        @forelse($items as $item)
        <div wire:click="selectItem({{ $item->id }}, {{ $item->quantity ?? 0 }})"
            class="{{ $selectedLabelId ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }} rounded-lg shadow-sm border p-4 hover:bg-blue-100 dark:hover:bg-blue-900/30 cursor-pointer transition-colors relative">

            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex-shrink-0 h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 shadow-sm active:scale-95 transition-transform"
                         @click.stop="$dispatch('openImageModal', { productId: {{ $item->id }}, context: 'COMERCIAL' })">
                        @php
                            $thumbnail = $item->getPrincipalThumbnailUrl('COMERCIAL');
                        @endphp
                        <img src="{{ $thumbnail }}" alt="Producto" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            @if($selectedLabelId)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                    ✓ ASIGNADO
                                </span>
                            @endif
                            <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $item->sku ?? 'N/A' }}</h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-50 dark:bg-gray-700/50 px-1.5 py-0.5 rounded inline-block">ID: {{ $item->id }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item->description ?? $item->name }}</p>
                <div class="flex items-center gap-2 mt-2" onclick="event.stopPropagation()">
                    <button type="button" class="p-1.5 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors" title="Ver imágenes">
                        <svg class="w-5 h-5 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 15l3-3 4 4M8 19h8" />
                        </svg>
                    </button>
                    <button type="button" class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Ver documentos">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v13a1 1 0 01-1 1H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v5h5M9 15h6M9 18h4" />
                        </svg>
                    </button>
                    <button type="button" 
                            @click.stop="$dispatch('openAccessoriesModal', { itemId: {{ $item->id }} })"
                            class="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors" 
                            title="Ver accesorios">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </button>
                    <button type="button" 
                            @click.stop="$dispatch('openObservationsModal', { itemId: {{ $item->id }} })"
                            class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" 
                            title="Ver observaciones">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Existencias ERP</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($item->stock_items_store ?? 0, 0) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Porcentaje</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->percentage ?? 0 }}%</p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Salida ERP</p>
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">{{ number_format($item->outsideMovement ?? 0, 0) }}</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Entrada ERP</p>
                    <p class="text-sm font-semibold text-green-600 dark:text-green-400">{{ number_format($item->insideMovement ?? 0, 0) }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-gray-700" onclick="event.stopPropagation()">
                <div class="flex-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Cantidad</label>
                    <input type="number" 
                        min="0" 
                        step="1" 
                        value="{{ $selectedQuantities[$item->id] ?? $item->quantity ?? 0 }}"
                        @if($selectedLabelId) disabled @endif
                        @click="$wire.selectItem({{ $item->id }}, {{ $selectedQuantities[$item->id] ?? $item->quantity ?? 0 }})"
                        @change="
                $wire.updateQuantity({{ $item->id }}, $event.target.value).then(() => {
                    $wire.selectItem({{ $item->id }}, parseInt($event.target.value) || 0);
                });
            "
                        title="{{ !empty($item->label_assignments) ? "Cantidades pedidas por etiqueta:\n" . $item->label_assignments : 'Sin etiquetas programadas' }}"
                        class="block w-full px-3 py-2 text-sm font-semibold {{ $selectedLabelId ? 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' }} border {{ $selectedLabelId ? 'border-gray-300 dark:border-gray-600' : 'border-blue-200 dark:border-blue-800' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                        placeholder="0">
                </div>
                <div class="flex-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">EXW</label>
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($item->exw ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>


                <div class="absolute bottom-4 right-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-[9px] font-black bg-indigo-600 text-white uppercase tracking-tighter shadow-lg ring-1 ring-white/20">
                        Tap para seleccionar
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <div class="flex flex-col items-center text-center">
                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay items</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No se encontraron items que coincidan con tu búsqueda.</p>
                </div>
            </div>
        @endforelse

        @if($items->hasPages())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Componente de Historial -->
    @livewire('tenant.imports.import-item-history')

    <!-- Modales Compartidos -->
    @livewire('tenant.components.product-image-modal')
    {{-- @livewire('tenant.components.item-accessories-modal') --}}
</div>