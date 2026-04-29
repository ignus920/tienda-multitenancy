<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6"
    x-data="{ 
        notifications: [], 
        addNotification(message, type = 'success') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => {
                this.removeNotification(id);
            }, 3000);
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }"
    @notify.window="addNotification($event.detail.message, $event.detail.type)">
    <!-- Notification Toast Container -->
    <div class="fixed top-5 right-5 z-[100] flex flex-col gap-3">
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="flex items-center p-4 rounded-lg shadow-lg border-l-4 min-w-[300px]"
                :class="{
                    'bg-green-100 border-green-500 text-green-800 dark:bg-green-900 dark:text-green-200': notification.type === 'success',
                    'bg-red-100 border-red-500 text-red-800 dark:bg-red-900 dark:text-red-200': notification.type === 'error',
                    'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900 dark:text-blue-200': notification.type === 'info',
                    'bg-yellow-100 border-yellow-500 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': notification.type === 'warning'
                }"
            >
                <div class="flex-shrink-0 mr-3">
                    <template x-if="notification.type === 'success'">
                        <x-heroicon-o-check-circle class="w-6 h-6" />
                    </template>
                    <template x-if="notification.type === 'error'">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </template>
                    <template x-if="notification.type === 'info' || notification.type === 'warning'">
                        <x-heroicon-o-information-circle class="w-6 h-6" />
                    </template>
                </div>
                    
                <div class="flex-1 font-medium text-sm" x-text="notification.message"></div>
                <button @click="removeNotification(notification.id)" class="ml-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>

    <!-- Status Summary -->
	<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        @foreach($this->status as $stat)
            @php
                $isActive = ($filterStatus == $stat->{'id'}) || ($stat->{'id'} == 10 && $filterNews == 1);
            @endphp
            <button wire:click="putFilter({{ $stat->{'id'} }})" class="text-left transition-transform transform hover:scale-105">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex items-center p-4 border {{ $isActive ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex-shrink-0 {{ $isActive ? 'bg-indigo-600' : 'bg-indigo-100 dark:bg-indigo-900' }} rounded-lg p-3 mr-4 transition-colors">
                        <x-heroicon-o-document-text class="w-8 h-8 {{ $isActive ? 'text-white' : 'text-indigo-600 dark:text-indigo-400' }}" />
                    </div>
                    <div>
                        @if (Auth::user()?->profile_id == 17)
                            <div class="text-gray-500 dark:text-gray-400 font-semibold text-sm">{{ $stat->{'translated_name'} }}</div>
                        @else
                            <div class="text-gray-500 dark:text-gray-400 font-semibold text-sm">{{ $stat->{'nombre_estado'} }}</div>
                        @endif
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $stat->{'cantidad'} }}
                        </div>
                    </div>
                </div>
            </button>
		@endforeach
	</div>

    <!-- PACKINGS CARDS (Tarjetas de Packs) -->
    @if($filterStatus == 5 && $profileUser == 17)
    <div class="mb-6">
        <div class="inline-block bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="font-bold text-lg mb-2 text-gray-900 dark:text-white">Selected PACK(s):</div>
            <div class="flex flex-wrap gap-3">
                @forelse($this->packings as $packing)
                    <div wire:click="togglePacking({{ $packing->id }})"
                        class="flex flex-col items-center justify-center px-4 py-2 rounded-lg shadow border transition-all cursor-pointer min-w-[110px] hover:shadow-md {{ in_array($packing->id, $selectedPackingIds) ? 'border-indigo-600 ring-2 ring-indigo-200 bg-indigo-300 dark:bg-indigo-900/30 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700  dark:bg-gray-900 hover:bg-gray-200 dark:hover:bg-gray-800' }}">

                        <div class="font-semibold {{ in_array($packing->id, $selectedPackingIds) ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200' }} text-base mb-1">
                            {{ str_pad($packing->number_packing, 3, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="text-xs font-medium mb-1">
                            @if($packing->imports_count == 0)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    Available
                                </span>
                            @else
                                <span class="inline-flex items-center">
                                    <span class="w-4 h-4 mr-1 inline-flex items-center justify-center bg-indigo-500 text-white rounded-full text-xs">
                                        {{ $packing->imports_count }}
                                    </span>
                                    In Use
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-gray-500 dark:text-gray-400">No hay packs disponibles</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($filterStatus == 6)
        <!-- Chips/etiquetas de los seleccionados -->
        <div class="mt-4 mb-2 flex flex-wrap gap-3">
            @forelse($this->packings->where('imports_count', '>', 0) as $packing)
                <div wire:click="putFilterPacking({{ $packing->id }})"
                    class="relative flex items-center gap-2 px-3 py-2 rounded-lg border transition-all cursor-pointer min-w-[120px] hover:shadow-md
                        {{ $filterPacking == $packing->id
                            ? 'border-indigo-600 ring-2 ring-indigo-200 bg-indigo-50 dark:bg-indigo-900/30 dark:ring-indigo-900/50' 
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50' 
                        }}">
            
                    <!-- Badge del número de packing -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-semibold
                        {{ $filterPacking == $packing->id
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700' 
                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600' 
                        }} border shadow-sm">
                        #{{ str_pad($packing->number_packing, 3, '0', STR_PAD_LEFT) }}
                    </span>

                    <!-- Contenedor del contador -->
                    <div class="flex items-center gap-1.5 ml-auto">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ítems:</span>
                        <span class="inline-flex items-center justify-center min-w-[1.8rem] h-6 px-1.5 
                            {{ $filterPacking == $packing->id
                                ? 'bg-indigo-500 text-white shadow-sm shadow-indigo-300/50 dark:shadow-indigo-900/50' 
                                : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200' 
                            }} rounded-full text-xs font-bold transition-colors">
                            {{ $packing->imports_count }}
                        </span>
                    </div>

                    <!-- Check icon cuando está seleccionado -->
                    @if($filterPacking == $packing->id )
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-indigo-500 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            @empty
                <div class="w-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/30 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <!-- Icono más grande y descriptivo -->
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No hay packs seleccionados</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Selecciona los packs que deseas incluir</p>
                </div>
            @endforelse
            <div class="flex items-center gap-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                <x-heroicon-o-information-circle class="w-6 h-6 flex-shrink-0" />
                <b class="flex-1">Select one or more packages. The selected ones will be displayed in purple. and select products with the check mark.</b>
            </div>
        </div>
    @endif
    
    <!-- DataTable Card -->
    <div class="max-w-12xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Búsqueda -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por item o código..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <div class="w-full sm:w-48">
                                @livewire('selects.generic-select', [
                                    'selectedValue' => $selectedLabelId,
                                    'items' => $labels,
                                    'name' => 'selectedLabel',
                                    'placeholder' => $selectedLabelName,
                                    'label' => '',
                                    'required' => false,
                                    'showLabel' => false,
                                    'class' => 'block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent ' . ($selectedLabelId ? 'bg-indigo-50 border-indigo-500 text-indigo-900 dark:bg-indigo-900/30 dark:border-indigo-400 dark:text-indigo-200 ring-1 ring-indigo-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'),
                                    'eventName' => 'labelSelected',
                                    'displayField' => 'name',
                                    'valueField' => 'id',
                                ], key('label-select-' . ($selectedLabelId ?? 'none') . '-' . now()->timestamp))
                                @error('selectedLabel') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                            @if ($showButtonShipping)
                                <button wire:click="openModalShipping({{ $filterPacking }})" class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"><x-heroicon-o-truck class="w-4 h-4 mr-2"/> Assign Shipping Data</button>
                            @endif
                            @if ($filterStatus == 7)
                                <select wire:model.live="selectedShipp"
                                    class="block w-96 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="0">Select shippment</option>
                                    @forelse ($this->shippments as $sp)
                                        <option value="{{ $sp->id }}">{{ $sp->way }} - {{ $sp->operation_number }} (#{{ $sp->consecutive }})</option>
                                    @empty
                                        <option value=""></option>
                                    @endforelse
                                </select>
                            @endif
                        </div>
                    </div>

                    <!-- Controles -->
                    <div class="flex items-center gap-3">
                        <!-- Registros por página -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                            <select wire:model.live="perPage"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 px-3 py-1">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <x-export-buttons />
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-4 text-left w-12">
                                <!-- Columna para selección -->
                            </th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Item
                            </th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Factory Ref
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                $ Last
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Qty Ordered
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Label
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Quoted price
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Coment
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Action
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Status
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Qty Shipped
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Shipping Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->orders as $order)
                            <tr wire:key="order-{{ $order->id }}-{{ $refreshCounter }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ in_array($order->id, $selectedOrders) ? 'bg-indigo-50/70 dark:bg-indigo-900/20' : '' }}">
                                <td class="px-4 py-4">
                                    <input type="checkbox" 
                                        wire:model.live="selectedOrders" 
                                        value="{{ $order->id }}"
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                </td>
                                <td class="px-4 py-4 max-w-[200px]">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white line-clamp-2">
                                        {{ $order->item}}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono">
                                        {{ $order->factory_ref ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-mono text-gray-600 dark:text-gray-300">
                                        ${{ number_format($order->exw ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center"
                                    x-data="{ qtyOrdered: {{ $order->qty_requested ?? 0 }} }"
                                    x-effect="if (document.activeElement !== $refs.qtyInput) qtyOrdered = {{ $order->qty_requested ?? 0 }}">
                                    @if ($profileUser != '17' && in_array($order->status, [1,2,4,5]))
                                        <input type="number"
                                        wire:key="qty-input-{{ $order->id }}-{{ $refreshCounter }}"
                                        x-ref="qtyInput"
                                        x-model="qtyOrdered"
                                        @change="$wire.updateQty({{ $order->id }}, qtyOrdered)"
                                        @keydown.enter="$wire.updateQty({{ $order->id }}, qtyOrdered)"
                                        class="w-24 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center"
                                        placeholder="{{ $order->qty_requested ?? 0 }}">
                                    @else
                                        <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ number_format($order->qty_requested ?? 0) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $order->label ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center"
                                    x-data="{ priceQ: {{ $order->price ?? 0 }} }"
                                    x-effect="if (document.activeElement !== $refs.priceInput) priceQ = {{ $order->price ?? 0 }}">
                                    @if ($profileUser == '17' && in_array($order->status, [1,2,4,6,7]))
                                        <div class="flex justify-center">
                                            <input type="number" 
                                            wire:key="price-input-{{ $order->id }}-{{ $refreshCounter }}"
                                            x-ref="priceInput"
                                            x-model="priceQ"
                                            @change="$wire.updatePriceQ({{ $order->id }}, priceQ)"
                                            @keydown.enter="$wire.updatePriceQ({{ $order->id }}, priceQ)"
                                            class="w-28 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center"
                                            placeholder="{{ $order->price ?? 0 }}"
                                            step="0.01">
                                        </div>
                                    @else
                                        <span class="text-sm font-mono text-gray-600 dark:text-gray-300">
                                            ${{ number_format($order->price ?? 0, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="space-y-2 min-w-[200px]">
                                        <div x-data="{ comment: '' }" class="flex items-center gap-1">
                                            <input type="text" 
                                                x-model="comment"
                                                @change="$wire.saveComment({{ $order->id }}, comment); comment = ''"
                                                @keydown.enter="$wire.saveComment({{ $order->id }}, comment); comment = ''"
                                                class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Añadir comentario...">
                                            <button 
                                                @click="$wire.openModalHistory({{ $order->id }})"
                                                class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                title="Ver historial">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                            </button>
                                            @if(isset($order->news) && $order->news == 1)
                                                <span class="relative flex">
                                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                                                    </span>
                                                    <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @php
                                        $data = is_string($order->ultimo_comentario) ? json_decode($order->ultimo_comentario, true) : null;
                                        @endphp

                                        @if(is_array($data) && isset($data['type']))
                                            <div class="text-xs bg-gray-50 dark:bg-gray-700/30 p-2 rounded border border-gray-200 dark:border-gray-600">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $data['type'] === 'qty_change' ? 'Cambio de Cantidad' : 'Cambio de Precio' }}</span>
                                                <div class="flex items-center gap-1 mt-1 font-mono">
                                                    @if ($data['type'] === 'qty_change')
                                                        <span class="text-gray-500 line-through">{{ $data['old'] }}</span>
                                                    @elseif ($data['type'] === 'price_change')
                                                        <span class="text-gray-500 line-through">${{ number_format($data['old'], 2) }}</span>
                                                    @endif

                                                    <x-heroicon-o-arrow-right class="w-3 h-3 text-gray-400" />

                                                    @if ($data['type'] === 'qty_change')
                                                        <span class="text-green-600 dark:text-green-400 font-semibold">{{ $data['new'] }}</span>
                                                    @elseif ($data['type'] === 'price_change')
                                                        <span class="text-green-600 dark:text-green-400 font-semibold">${{ number_format($data['new'], 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($order->ultimo_comentario)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 p-2 rounded border border-gray-200 dark:border-gray-600 truncate" title="{{ $order->ultimo_comentario }}">
                                                {{ Str::limit($order->ultimo_comentario, 50) }}
                                            </div>
                                        @endif
                                    </div> 
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($order->status == 2 && $order->news == 0 && $profileUser != '17')
                                        <button wire:click="openModalConfirmPrice({{ $order->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 whitespace-nowrap">
                                            <x-heroicon-o-check class="w-4 h-4" /> Approve price
                                        </button>
                                    @elseif($order->status == 4 && $order->news == 0 && $profileUser == '17')
                                        <button wire:click="openModalConfirmProduction({{ $order->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 whitespace-nowrap">
                                            <x-heroicon-o-check class="w-4 h-4" /> Production
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $order->translated_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center"
                                    x-data="{ qtyShip: {{ $order->qty_shipped ?? $order->qty_requested ?? 0 }} }"
                                    x-effect="if (document.activeElement !== $refs.qtyShipInput) qtyShip = {{ $order->qty_shipped ?? $order->qty_requested ?? 0 }}">
                                    @if ($profileUser == '17' && ($order->status == 6))
                                        <div class="flex justify-center">
                                            <input type="number" 
                                                wire:key="qty-ship-input-{{ $order->id }}-{{ $refreshCounter }}"
                                                x-ref="qtyShipInput"
                                                x-model="qtyShip"
                                                @change="$wire.updateQtyShip({{ $order->id }}, qtyShip)"
                                                @keydown.enter="$wire.updateQtyShip({{ $order->id }}, qtyShip)"
                                                class="w-24 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center"
                                                placeholder="{{ $order->qty_requested ?? 0 }}">
                                        </div>
                                    @else
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $order->qty_shipped ?? $order->qty_requested ?? 0 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="space-y-1 text-xs min-w-[200px]">
                                        @if($order->packing_number)
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium text-gray-500 dark:text-gray-400 w-12">Pack:</span>
                                                <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $order->packing_number }}</span>
                                            </div>
                                        @endif

                                        @if($order->operation_number)
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium text-gray-500 dark:text-gray-400 w-12">O.N:</span>
                                                <span class="font-mono">{{ $order->operation_number }}</span>
                                            </div>
                                        @endif

                                        @if($order->etd)
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium text-gray-500 dark:text-gray-400 w-12">ETD:</span>
                                                <span>{{ \Carbon\Carbon::parse($order->etd)->format('d/m/Y') }}</span>
                                            </div>
                                        @endif

                                        @if($order->way)
                                            <div class="flex items-center gap-1">
                                                <span class="font-medium text-gray-500 dark:text-gray-400 w-12">Via:</span>
                                                <span class="flex items-center gap-1">
                                                    @if($order->way == 'Aerea')
                                                        Air
                                                    @elseif($order->way == 'Maritima')
                                                        Maritime
                                                    @else
                                                        {{ $order->way }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-1">
                                            <span class="font-medium text-gray-500 dark:text-gray-400 w-12">Rec:</span>
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-4 text-gray-400 dark:text-gray-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium">No se encontraron registros</p>
                                        <p class="text-sm">
                                            {{ $search ? 'Intenta ajustar tu búsqueda' : 'No hay órdenes disponibles en este momento' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($this->orders->hasPages())
            <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando {{ $this->orders->firstItem() }} a {{ $this->orders->lastItem() }} de {{ $this->orders->total() }}
                        resultados
                    </div>
                    <div>
                        {{ $this->orders->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Historal de comentario y estados -->
    @if ($showModalHistory)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <!-- Modal Header -->
                    <div class="sticky top-0 z-10 flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Historial de la importación</h2>
                        <button wire:click="cancel" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <!-- Modal Content -->
                    <div class="p-6 space-y-8">
                        <!-- SECCIÓN 1: COMENTARIOS -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Comentarios</span>
                            </div>

                            @if(count($this->historyComments) > 0)
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach($this->historyComments as $comment)
                                        @php
                                            // Asignar color característico por usuario
                                            static $userColors = [];
                                            $colorClasses = [
                                                ['bg-yellow-100', 'text-yellow-800', 'dark:bg-yellow-900/30', 'dark:text-yellow-200'],
                                                ['bg-blue-100', 'text-blue-800', 'dark:bg-blue-900/30', 'dark:text-blue-200'],
                                                ['bg-green-100', 'text-green-800', 'dark:bg-green-900/30', 'dark:text-green-200'],
                                                ['bg-pink-100', 'text-pink-800', 'dark:bg-pink-900/30', 'dark:text-pink-200'],
                                                ['bg-purple-100', 'text-purple-800', 'dark:bg-purple-900/30', 'dark:text-purple-200'],
                                            ];
                                    
                                            if (!isset($userColors[$comment->name])) {
                                                $userColors[$comment->name] = $colorClasses[count($userColors) % count($colorClasses)];
                                            }
                                            $classes = $userColors[$comment->name];

                                            $data = is_string($comment->comment) ? json_decode($comment->comment, true) : null;
                                        @endphp
                                
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                                            <!-- Header del comentario -->
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $classes[0] }} {{ $classes[1] }} {{ $classes[2] }} {{ $classes[3] }}">
                                                        {{ $comment->name }}
                                                    </span>
                                                    @if(isset($comment->type))
                                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium 
                                                            {{ $comment->type === 'importaciones' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200' }}">
                                                            {{ $comment->type }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                    
                                            <!-- Contenido del comentario -->
                                            @if(is_array($data) && isset($data['type']))
                                                <div class="bg-blue-50 dark:bg-blue-900/10 p-3 rounded-lg border border-blue-100 dark:border-blue-800/30">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <x-heroicon-o-arrow-path class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                                        <span class="font-semibold text-sm text-blue-700 dark:text-blue-400">
                                                            {{ $data['type'] === 'qty_change' ? 'Cambio de Cantidad' : 'Cambio de Precio' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-sm space-y-1">
                                                        <p class="text-gray-700 dark:text-gray-300">
                                                            De: <span class="font-semibold">{{ $data['old'] }}</span> 
                                                            <span class="mx-2">→</span> 
                                                            A: <span class="font-semibold">{{ $data['new'] }}</span>
                                                        </p>
                                                        @if(!empty($data['note']))
                                                            <p class="text-gray-600 dark:text-gray-400 italic mt-2 text-sm border-l-2 border-blue-300 pl-2">
                                                                "{{ $data['note'] }}"
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                    {{ $comment->comment }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                                    <p>No hay comentarios en el historial</p>
                                </div>
                            @endif
                        </div>

                        <!-- SECCIÓN 2: HISTORIAL DE ESTADOS -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
                                <x-heroicon-o-clock class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Historial de Estados</span>
                            </div>

                            @if(count($this->historyStatus) > 0)
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach ($this->historyStatus as $hs)
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start mb-3">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $hs->created_at->format('d/m/Y H:i') }}</span>
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $hs->name }}
                                                </span>
                                            </div>

                                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                                <div class="flex-1 text-center">
                                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Estado anterior</span>
                                                    <span class="px-3 py-1.5 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200 inline-block">
                                                        {{ $hs->previousStatus->name ?? 'N/A' }}
                                                    </span>
                                                </div>

                                                <div class="px-3">
                                                    <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                                                </div>

                                                <div class="flex-1 text-center">
                                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Estado nuevo</span>
                                                    <span class="px-3 py-1.5 rounded-lg text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200 inline-block">
                                                        {{ $hs->newStatus->name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <x-heroicon-o-clock class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                                    <p>No hay cambios de estado en el historial</p>
                                </div>
                            @endif
                        </div>

                        <!-- Botón Finish (si aplica) -->
                        @if($this->initiatorCanFinish)
                            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button wire:click="openModalAcceptNew({{ $import_id }})"
                                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors shadow-sm hover:shadow">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                                    Finalizar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Cambio Cantidad Quantity Ordered -->
    @if ($showModalChangeQuantity)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-900/10 dark:to-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <x-heroicon-o-arrow-path class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Cambio de Cantidad
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Modifica la cantidad del producto
                                </p>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveChangeQuantity">
                        <!-- Contenido del modal -->
                        <div class="p-6 space-y-5">
                            <!-- Cantidades -->
                            <div class="space-y-3">
                                <!-- Cantidad anterior -->
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1.5 bg-gray-200 dark:bg-gray-600 rounded-full">
                                                <x-heroicon-o-minus-circle class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                            </span>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad anterior</span>
                                        </div>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">$</span>
                                            <input type="text" 
                                                   wire:model="oldQty" 
                                                   class="w-28 pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-500 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-semibold text-right focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                                   disabled>
                                        </div>
                                    </div>
                                </div>

                                <!-- Flecha indicadora (opcional) -->
                                <div class="flex justify-center">
                                    <div class="p-1 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">
                                        <x-heroicon-o-arrow-down class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                </div>

                                <!-- Nueva cantidad -->
                                <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1.5 bg-indigo-200 dark:bg-indigo-800 rounded-full">
                                                <x-heroicon-o-plus-circle class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                            </span>
                                            <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Nueva cantidad</span>
                                        </div>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-600 dark:text-indigo-400 font-medium">$</span>
                                            <input type="number" 
                                                   step="0.01"
                                                   wire:model="newQty" 
                                                   class="w-28 pl-7 pr-3 py-2 border border-indigo-300 dark:border-indigo-600 rounded-lg bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-300 font-semibold text-right focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                                   disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo de comentario -->
                            <div class="pt-3">
                                <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        <span>Comentario sobre el cambio</span>
                                    </div>
                                </label>
                                <textarea wire:model="commentChangeQuantity"
                                    id="comment"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow hover:shadow-sm resize-none"
                                    placeholder="Escribe un comentario sobre el motivo del cambio..."></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                                    Este comentario quedará registrado en el historial
                                </p>
                            </div>

                            <!-- Mensaje de error (opcional) -->
                            @error('newQty')
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                        <span>{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror
                        </div>

                        <!-- Footer con botones -->
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 flex justify-end gap-3">
                            <button type="button" wire:click="cancel"
                                class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                <span wire:loading.remove wire:target="saveChangeQuantity">Guardar Cambio</span>
                                <span wire:loading wire:target="saveChangeQuantity" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Guardando...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para terminar conversación -->
    @if ($showModalAcceptNew)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-emerald-50 to-white dark:from-emerald-900/10 dark:to-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Aceptar Novedad
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Confirma la aceptación de esta novedad
                                </p>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="finishConversation" class="p-6">
                        <div class="space-y-6">
                            <!-- Campo de comentario -->
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 19c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-1">
                                            ¿Está seguro de que desea aceptar esta novedad?
                                        </h4>
                                        <p class="text-xs text-amber-700 dark:text-amber-400">
                                            Esta acción confirmará la novedad y no podrá ser revertida posteriormente.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo de comentario (obligatorio) -->
                            <div class="space-y-2">
                                <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span>Comentario <span class="text-red-500">*</span></span>
                                    </div>
                                </label>
                                <textarea wire:model="commentAccept"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow hover:shadow-sm resize-none"
                                    placeholder="Indique el motivo o detalles de la aceptación..."></textarea>

                                <!-- Contador de caracteres (opcional) -->
                                <div class="flex justify-between items-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">*</span> Campo obligatorio
                                    </p>
                                    @if(strlen($commentAccept ?? '') > 0)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ strlen($commentAccept) }}/500 caracteres
                                        </span>
                                    @endif
                                </div>

                                <!-- Mensaje de error para comentario vacío -->
                                @error('commentAccept')
                                    <div class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Footer con botones -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex justify-end gap-3">
                            <button type="button" wire:click="cancel"
                                class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                <span wire:loading.remove wire:target="finishConversation" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Aceptar Novedad
                                </span>
                                <span wire:loading wire:target="finishConversation" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Procesando...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal confirmación de precio -->
    @if ($showModalConfirmPrice)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <!-- Modal Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-amber-50 to-white dark:from-amber-900/10 dark:to-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-xl">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Confirmar Aprobación
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Revise los detalles antes de confirmar
                                </p>
                            </div>
                        </div>
                        <button wire:click="cancel" 
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit.prevent="saveChangeQuantity" class="p-6">
                        <div class="space-y-6">
                            <!-- Mensaje de confirmación -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">
                                            ¿Confirma aprobar este producto?
                                        </h4>
                                        <p class="text-xs text-blue-700 dark:text-blue-400">
                                            Esta acción confirmará el precio y cantidad solicitada para el producto.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta de información del producto -->
                            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Código interno -->
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Código interno</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white bg-white dark:bg-gray-800 px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-600">
                                        {{ $this->infoPrice['internal_code'] }}
                                    </span>
                                </div>

                                <!-- Precio -->
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Precio</span>
                                    </div>
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded-lg border border-green-200 dark:border-green-800">
                                        ${{ number_format($this->infoPrice['price'], 2) }}
                                    </span>
                                </div>

                                <!-- Cantidad -->
                                <div class="flex items-center justify-between p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Cantidad solicitada</span>
                                    </div>
                                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 px-3 py-1 rounded-lg border border-purple-200 dark:border-purple-800">
                                        {{ number_format($this->infoPrice['qty_requested'], 0) }} und
                                    </span>
                                </div>
                            </div>

                            <!-- Mensaje de advertencia (opcional) -->
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 19c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <p class="text-xs text-amber-700 dark:text-amber-400">
                                        Una vez aprobado, el precio quedará registrado y no podrá ser modificado sin un nuevo proceso de aprobación.
                                    </p>
                                </div>
                            </div>

                            <!-- Footer con botones mejorados -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex justify-end gap-3">
                                <button type="button" wire:click="cancel"
                                    class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="approvePrice({{ $import_id }})" wire:loading.attr="disabled"
                                    class="inline-flex items-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    <span wire:loading.remove wire:target="approvePrice" class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Sí, aprobar precio
                                    </span>
                                    <span wire:loading wire:target="approvePrice" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Procesando...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para justificar el cambio de precio-->
    @if ($showModalJustifyChangePrice)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center gap-3 sticky top-0 bg-white dark:bg-gray-800 z-10">
                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-full">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Precio Modificado
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                Esta acción quedará registrada en el historial
                            </p>
                        </div>
                        <button type="button" wire:click="cancel" class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit.prevent="saveChangePrice" class="p-6 space-y-6">
                        <!-- Campo de comentario -->
                        <div class="space-y-2">
                            <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Por favor justifica el cambio de precio:
                            </label>
                            <textarea wire:model="commentJustifyPrice"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm resize-none"
                                placeholder="Escribe el comentario aquí..."></textarea>

                            @error('commentJustifyPrice') 
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <!-- Footer con botones -->
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 flex flex-col sm:flex-row justify-end gap-3 rounded-b-lg">
                            <button type="button" wire:click="cancel"
                                class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600 order-2 sm:order-1">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 order-1 sm:order-2">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal confirmación paso a Producción -->
    @if ($showModalConfirmProduction)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Send to Production?</h2>
                        <button wire:click="cancel" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit.prevent="saveSendProduction">
                        <div class="p-6 space-y-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    ¿Confirm approve the product?
                                </p>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-600">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Products</span>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Internal Code</p>
                                        <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">{{ $this->infoPrice['internal_code'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Requested quantity</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($this->infoPrice['qty_requested']) }} units</p>
                                    </div>
                                </div>

                                <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300"></span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($this->infoPrice['price'], 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen rápido (opcional) -->
                            <div class="grid grid-cols-2 gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-cube class="w-4 h-4" />
                                    <span>Product ready for production.</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-clock class="w-4 h-4" />
                                    <span>Irreversible action</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer con botones -->
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3 rounded-b-lg">
                            <button type="button" wire:click="cancel"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-5 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                <span wire:loading.remove wire:target="saveSendProduction">Yes, continue</span>
                                <span wire:loading wire:target="saveSendProduction" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de cambio de cantidad shipped -->
    @if($showModalChangeQtyShip)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-900/10 dark:to-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <x-heroicon-o-arrow-path class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Why are you changing the amount sent?
                                </h3>
                            </div>
                        </div>
                    </div>
                    <form wire:submit.prevent="saveChangeQtyShip">
                        <div class="p-6 space-y-5">
                            <div class="pt-3">
                                <textarea wire:model="commentChangeQtyShip"
                                    id="commentChangeQtyShip"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow hover:shadow-sm resize-none"
                                    placeholder="Write the reason for the change...">
                                </textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                                    This comment will be recorded in the history.
                                </p>
                            </div>
                            <!-- Mensaje de error (opcional) -->
                            @error('commentChangeQtyShip')
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                        <span>{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror
                        </div>
                        <!-- Footer con botones -->
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 flex justify-end gap-3">
                            <button type="button" wire:click="cancel"
                                class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                <span wire:loading.remove wire:target="saveChangeQuantity">Save Change</span>
                                <span wire:loading wire:target="saveChangeQuantity" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Guardando...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal registro información Shipping -->
    @if ($showModalShipping)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assign Shipping Data</h2>
                        <button wire:click="cancel" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="saveShippingData" class="p-4 md:p-6 space-y-4 md:space-y-6">
                        <!-- PACK SELECTED -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 dark:from-indigo-950/40 dark:to-indigo-900/20 rounded-xl p-5 border border-indigo-200 dark:border-indigo-800/50 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="p-1.5 bg-indigo-500 rounded-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <h3 class="font-medium text-indigo-900 dark:text-indigo-200">Selected Packings</h3>
                            </div>
                    
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Resumen de packs seleccionados -->
                                @foreach ($this->infoPacking as $ip)
                                <div class="bg-white dark:bg-gray-800/80 rounded-lg p-2 shadow-sm border border-indigo-200/50 dark:border-indigo-800/30">
                                    <div class="flex items-center gap-3 mb-1">
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-0">Packings</p>
                                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ $ip->number_packing ?? 0 }}</span>
                                    </div>
                                </div>

                                <!-- Total productos -->
                                <div class="bg-white dark:bg-gray-800/80 rounded-lg p-2 shadow-sm border border-indigo-200/50 dark:border-indigo-800/30">
                                    <div class="flex items-center gap-3 mb-1">
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-1">Total Products</p>
                                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ $ip->imports_count ?? 0 }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                    
                            {{-- <!-- Lista detallada de packs (opcional) -->
                            @if(!empty($selectedPacks))
                                <div class="mt-3 pt-3 border-t border-indigo-200 dark:border-indigo-800/30">
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-2">Packings list:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedPacks as $pack)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-gray-800 rounded-md text-xs border border-indigo-200 dark:border-indigo-800">
                                                <span class="font-mono font-medium text-indigo-700 dark:text-indigo-300">#{{ str_pad($pack->number_packing, 3, '0', STR_PAD_LEFT) }}</span>
                                                <span class="w-1 h-1 rounded-full bg-indigo-300 dark:bg-indigo-600"></span>
                                                <span class="text-indigo-600 dark:text-indigo-400">{{ $pack->products_count }} items</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif --}}
                        </div>

                        <div class="space-y-5">
                            <!-- ETD -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    ETD <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="etd" type="date" 
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm">
                                @error('etd') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- OPERATION NUMBER -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    OPERATION NUMBER <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="operation_number" type="text" 
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm"
                                    placeholder="e.g., OP-2024-001">
                                @error('operation_number')<span class="text-red-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>

                            <!-- VIA -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    VIA <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="way"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm appearance-none bg-no-repeat bg-[length:20px_20px] bg-[right_1rem_center]">
                                    <option value="">Select route</option>
                                    <option value="Aerea" class="py-2">AIR</option>
                                    <option value="Maritima" class="py-2">MARITIME</option>
                                </select>
                                @error('way')<span class="text-red-600 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <!-- Conveyor -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    CONVEYOR
                                </label>
                                <input wire:model="conveyor" type="text" id="conveyor"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm">
                            </div>
                            <!-- Observations -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    OBSERVATIONS
                                </label>
                                <textarea wire:model="observations"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm resize-none"
                                    placeholder="Add any additional notes or instructions...">
                                </textarea>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex flex-col sm:flex-row justify-end gap-3">
                                <button type="button" wire:click="cancel"
                                    class="w-full sm:w-auto px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200 hover:shadow focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600 order-2 sm:order-1">
                                    Cancel
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 order-1 sm:order-2">
                                    <span wire:loading.remove wire:target="save">Save Changes</span>
                                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Saving...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
