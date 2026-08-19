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
	<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 mb-6">
        @foreach($this->status as $stat)
            @php
                $isActive = ($filterStatus == $stat->{'id'}) || ($stat->{'id'} == 10 && $filterNews == 1);
            @endphp
            <button wire:click="putFilter({{ $stat->{'id'} }})" class="w-full text-center transition-transform transform hover:scale-105">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex items-center justify-center py-2 px-3 border {{ $isActive ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex items-center justify-center gap-1.5 min-w-0 w-full text-xs font-semibold text-gray-500 dark:text-gray-400">
                        @if (Auth::user()?->profile_id == 17)
                            <span class="truncate">{{ $stat->{'translated_name'} }}:</span>
                        @else
                            <span class="truncate">{{ $stat->{'nombre_estado'} }}:</span>
                        @endif
                        <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 ml-1.5">{{ $stat->{'cantidad'} }}</span>
                    </div>
                </div>
            </button>
		@endforeach
        @if($selectedShipp > 0 && $this->selectedShippmentWeight > 0)
            <div class="col-span-1 lg:col-start-6 w-full text-center transition-transform transform hover:scale-105">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex items-center justify-center py-2 px-3 border border-indigo-500 ring-2 ring-indigo-100 dark:ring-indigo-900/50">
                    <div class="flex items-center justify-center gap-1.5 min-w-0 w-full text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                        <span>{{ Auth::user()?->profile_id == 17 ? 'Weight:' : 'Peso:' }}</span>
                        <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 ml-1.5">{{ number_format($this->selectedShippmentWeight / 1000, 2) }} Kg</span>
                    </div>
                </div>
            </div>
        @endif
	</div>

    {{-- 
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
    --}}

    {{-- 
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
    --}}
    
    <!-- DataTable Card -->
    <div class="max-w-12xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="{ 
                 showCols: { 
                     factoryRef: true, 
                     lastPrice: true, 
                     label: true, 
                     quotedPrice: true, 
                     comment: true, 
                     action: true, 
                     status: true, 
                     qtyShipped: true, 
                     shippingInfo: true 
                 },
                 init() {
                     const stored = localStorage.getItem('order_table_cols');
                     if (stored) {
                         try {
                             this.showCols = Object.assign({}, this.showCols, JSON.parse(stored));
                         } catch(e) {}
                     }
                     this.$watch('showCols', value => {
                         localStorage.setItem('order_table_cols', JSON.stringify(value));
                     }, { deep: true });
                 }
             }">
            <!-- Toolbar -->
            <div class="py-8 px-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Búsqueda y Filtros Rápidos -->
                    <div class="flex flex-col sm:flex-row gap-3 flex-1 max-w-2xl">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ Auth::user()?->profile_id == 17 ? 'Search by item or code...' : 'Buscar por item o código...' }}"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Filtro de prioridad/etiquetas -->
                        <div class="w-full sm:w-48">
                            <select wire:model.live="filterPriority"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">{{ Auth::user()?->profile_id == 17 ? 'All priorities' : 'Todas las prioridades' }}</option>
                                <option value="ASAP">ASAP</option>
                                <option value="Second">Second</option>
                                <option value="Third">Third</option>
                                <option value="Express">Express</option>
                                <option value="Express 2">Express 2</option>
                                <option value="Express 3">Express 3</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            {{-- Selector de Etiquetas (Programaciones/Labels) - Ocultado temporalmente
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
                            --}}

                            <!-- Selector de Envíos - Visible en En tránsito (7) o Recibido (8) -->
                            @if($filterStatus == 7 || $filterStatus == 8)
                                <div class="w-full sm:w-[420px]">
                                    @livewire('selects.generic-select', [
                                        'selectedValue' => $selectedShipp,
                                        'items' => $this->shippments,
                                        'name' => 'selectedShipp',
                                        'placeholder' => $selectedShipp > 0 && $this->selectedShippmentData ? 'Envío: ' . $this->selectedShippmentData->way : 'Filtrar por Envío',
                                        'label' => '',
                                        'required' => false,
                                        'showLabel' => false,
                                        'class' => 'block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent ' . ($selectedShipp ? 'bg-indigo-50 border-indigo-500 text-indigo-900 dark:bg-indigo-900/30 dark:border-indigo-400 dark:text-indigo-200 ring-1 ring-indigo-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'),
                                        'eventName' => 'shippmentSelected',
                                        'displayField' => 'way',
                                        'valueField' => 'id',
                                    ], key('shippment-select-' . ($selectedShipp ?? 'none') . '-' . now()->timestamp))
                                    @error('selectedShipp') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            
                            <!-- Botón Desfiltrar / Limpiar Filtros -->
                            <button wire:click="clearFilters" 
                                    class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all"
                                    title="{{ Auth::user()?->profile_id == 17 ? 'Clear all filters and reset the board' : 'Limpiar todos los filtros y restablecer el tablero' }}">
                                <span>{{ Auth::user()?->profile_id == 17 ? 'Clear Filters' : 'Borrar Filtros' }}</span>
                            </button>

                            @if ($profileUser != '17')
                                <button wire:click="openCreateNewProductModal" class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-lg border border-transparent bg-indigo-600 hover:bg-indigo-700 text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Crear Producto Nuevo</span>
                                </button>
                            @endif

                            @if (count($selectedOrders) > 0 && ($filterStatus == 5 || $filterStatus == 12))
                                <button wire:click="openModalShipping" class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"><x-heroicon-o-truck class="w-4 h-4 mr-2"/> Assign Shipping Data</button>
                            @endif
                            @if ($profileUser != '17' && count($selectedOrders) > 0)
                                <div class="flex items-center gap-2">
                                    @php
                                        // Verificar si en los seleccionados hay al menos una orden en estado "Solicitado" (1) o "Cotizado" (2)
                                        $hasPendingApprovals = \App\Models\Tenant\Imports\ImpImports::whereIn('id', $selectedOrders)
                                            ->whereIn('status', [1, 2])
                                            ->exists();
                                    @endphp
                                    
                                    @if ($hasPendingApprovals)
                                    <button wire:click="approvePricesInBatch" 
                                            class="inline-flex items-center justify-center px-4 py-2 text-sm border border-transparent rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <x-heroicon-o-check class="w-4 h-4 mr-2"/> Aprobar Seleccionados
                                    </button>
                                    @endif

                                    {{-- 
                                    <button wire:click="assignPriorityToSelectedOrders(null)" 
                                            style="background-color: #ffffff; color: #374151; border: 1px solid #d1d5db;"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                                        Quitar
                                    </button>
                                    --}}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Controles -->
                    <div class="flex items-center gap-3">
                        @if ($filterStatus == 7 && $selectedShipp > 0 && $profileUser != '17')
                            <button type="button"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-75 cursor-not-allowed"
                                    @click="
                                        Swal.fire({
                                            title: '¿Confirmar recibido de mercancía?',
                                            text: 'Antes de confirmar el recibido de la mercancía, ¿has revisado cantidades y códigos de la mercancía recibida?\n\nSi todo está revisado, presiona confirmar para cargar los productos recibidos al inventario.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#16a34a',
                                            cancelButtonColor: '#dc2626',
                                            confirmButtonText: 'CONFIRMAR',
                                            cancelButtonText: 'CANCELAR'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $wire.rotatePriorities();
                                            }
                                        })
                                    "
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    title="Al recibir los productos seleccionados, confirma la recepción y rota todos los productos de Segunda a Primera y de Tercera a Segunda de forma global.">
                                <svg wire:loading wire:target="rotatePriorities" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="rotatePriorities" class="bg-green-800 text-white text-xs font-bold px-2 py-0.5 rounded-full mr-2">{{ $this->productsToReceiveCount }}</span>
                                <span wire:loading wire:target="rotatePriorities">Procesando...</span>
                                <span wire:loading.remove wire:target="rotatePriorities">Confirmar Recepción y Rotar</span>
                            </button>
                        @endif
                        <!-- Visibilidad de Columnas -->
                        <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
                            <button @click="open = !open" 
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ Auth::user()?->profile_id == 17 ? 'Columns' : 'Columnas' }}
                                <svg class="w-4 h-4 ml-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 ring-1 ring-black ring-opacity-5 z-50 focus:outline-none"
                                 x-cloak>
                                <div class="py-2 px-3 space-y-1.5 text-xs text-gray-700 dark:text-gray-200">
                                    <div class="font-semibold text-gray-400 dark:text-gray-500 pb-1 border-b border-gray-150 dark:border-gray-600 mb-1">
                                        {{ Auth::user()?->profile_id == 17 ? 'Columns visibility' : 'Visibilidad de columnas' }}
                                    </div>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.factoryRef" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Factory Ref</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.lastPrice" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>$ Last</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.label" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Label</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.quotedPrice" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Quoted Price</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.comment" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Coment</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.action" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Action</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.status" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Status</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.qtyShipped" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Qty Shipped</span>
                                    </label>
                                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-600/50 cursor-pointer">
                                        <input type="checkbox" x-model="showCols.shippingInfo" class="w-4 h-4 rounded border-gray-300 dark:border-gray-500 text-indigo-600 focus:ring-indigo-500">
                                        <span>Shipping Info</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Registros por página -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">{{ Auth::user()?->profile_id == 17 ? 'Show:' : 'Mostrar:' }}</label>
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
                
                @if ($profileUser != '17' && count($selectedOrders) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mr-2">Prioridad en Lote:</span>
                        <button wire:click="assignPriorityToSelectedOrders('ASAP')" 
                                style="background-color: #dc2626; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            ASAP
                        </button>
                        <button wire:click="assignPriorityToSelectedOrders('Second')" 
                                style="background-color: #d97706; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            Second
                        </button>
                        <button wire:click="assignPriorityToSelectedOrders('Third')" 
                                style="background-color: #2563eb; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            Third
                        </button>
                        
                        <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                        
                        <button wire:click="assignPriorityToSelectedOrders('Express')" 
                                style="background-color: #dc2626; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            Express
                        </button>
                        <button wire:click="assignPriorityToSelectedOrders('Express 2')" 
                                style="background-color: #d97706; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            Express 2
                        </button>
                        <button wire:click="assignPriorityToSelectedOrders('Express 3')" 
                                style="background-color: #2563eb; color: #ffffff;"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold rounded-lg shadow hover:opacity-90 transition-all focus:outline-none">
                            Express 3
                        </button>
                    </div>
                @endif
            @if($this->selectedShippmentData)
                <div class="m-6 bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-600 p-5 rounded-r-lg shadow-sm">
                    <div class="flex flex-wrap lg:flex-nowrap items-end justify-between gap-4 w-full">
                         <div class="flex-1 min-w-[100px]">
                             <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Número de Operación</p>
                             <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                 {{ $this->selectedShippmentData->operation_number ?? 'N/A' }}
                             </p>
                         </div>
                          <div class="flex-1 min-w-[125px] max-w-[145px]">
                              <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">ETD (Salida)</p>
                              <input type="date" 
                                  wire:model="tempEtd" 
                                  wire:change="confirmEditField('etd')"
                                  class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mt-0.5 focus:ring-1 focus:ring-indigo-500">
                          </div>
                          <div class="flex-1 min-w-[80px]">
                              <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Vía</p>
                              <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                  #{{ $this->selectedShippmentData->consecutive }} 
                                  {{ $this->selectedShippmentData->way == 'Aerea' ? 'Aérea' : ($this->selectedShippmentData->way == 'Maritima' ? 'Marítima' : $this->selectedShippmentData->way) }}
                              </p>
                          </div>
                          <div class="flex-1 min-w-[125px]">
                              <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Transportador</p>
                              <input type="text" 
                                  wire:model="tempConveyor" 
                                  wire:change="confirmEditField('conveyor')"
                                  class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mt-0.5 focus:ring-1 focus:ring-indigo-500">
                          </div>
                         <div class="flex-1 min-w-[125px] max-w-[145px]">
                             <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">ETA (Puerto)</p>
                             @if($profileUser == '17')
                                 <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                     {{ $this->selectedShippmentData->eta ? \Carbon\Carbon::parse($this->selectedShippmentData->eta)->format('d/m/Y') : 'N/A' }}
                                 </p>
                             @else
                                 <input type="date" wire:model="shipmentEta" wire:change="updateShipmentDates"
                                     class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mt-0.5 focus:ring-1 focus:ring-indigo-500">
                             @endif
                         </div>
                         <div class="flex-1 min-w-[125px] max-w-[145px]">
                             <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Llega a Fervicom</p>
                             @if($profileUser == '17')
                                 <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                     {{ $this->selectedShippmentData->fervicom_arrival_date ? \Carbon\Carbon::parse($this->selectedShippmentData->fervicom_arrival_date)->format('d/m/Y') : 'N/A' }}
                                 </p>
                             @else
                                 <input type="date" wire:model="shipmentFervicomArrival" wire:change="updateShipmentDates"
                                     class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white mt-0.5 focus:ring-1 focus:ring-indigo-500">
                             @endif
                         </div>
                         <div class="flex-1 min-w-[210px] max-w-[250px]">
                             <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">Notas / Acciones</p>
                             <div class="flex items-center gap-1 mt-1">
                                 <input type="text" 
                                     wire:model.live="shipmentComment"
                                     wire:keydown.enter="saveShipmentComment"
                                     class="w-full px-2.5 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                     placeholder="Notas...">
                                 <button 
                                     wire:click="saveShipmentComment"
                                     class="p-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shrink-0"
                                     title="Guardar comentario">
                                     <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                 </button>
                                 <button 
                                     wire:click="openModalShipmentHistory"
                                     class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shrink-0"
                                     title="Historial del envío / Shipping History">
                                     <x-heroicon-o-eye class="w-4 h-4" />
                                 </button>
                                 @if($filterStatus == 7 && $profileUser != '17')
                                     <button 
                                         wire:click="openMarkReceivedModal"
                                         class="p-1.5 bg-indigo-700 text-white rounded-lg hover:bg-indigo-800 transition-colors focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 shrink-0 flex items-center justify-center"
                                         title="Marcar como Recibido / Mark as Received">
                                         <x-heroicon-o-check-circle class="w-4 h-4" />
                                     </button>
                                 @endif
                             </div>
                         </div>
                    </div>
                </div>
            @endif

            <!-- Tabla -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                        <tr>
                            @if(!in_array($filterStatus, [6, 8]))
                            <th class="px-4 py-4 text-left w-12">
                            </th>
                            @endif
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Item
                            </th>
                            <th x-show="showCols.factoryRef" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Factory Ref
                            </th>
                            <th x-show="showCols.lastPrice" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                $ Last
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Qty Ordered
                            </th>
                            <th x-show="showCols.label" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Label
                            </th>
                            <th x-show="showCols.quotedPrice" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Quoted price
                            </th>
                            <th x-show="showCols.comment" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Coment
                            </th>
                            <th x-show="showCols.action" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Action
                            </th>
                            <th x-show="showCols.status" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Status
                            </th>
                            <th x-show="showCols.qtyShipped" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Qty Shipped
                            </th>
                            <th x-show="showCols.shippingInfo && !$wire.selectedShipp" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                Shipping Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->orders as $order)
                            <tr wire:key="order-{{ $order->id }}-{{ $refreshCounter }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 {{ in_array($order->id, $selectedOrders) ? 'bg-indigo-50/70 dark:bg-indigo-900/20' : '' }}">
                                @if(!in_array($filterStatus, [6, 8]))
                                <td class="px-4 py-4">
                                    @if(!in_array($order->status, [6, 8]))
                                        @php
                                            $hasPrice = isset($order->price) && floatval($order->price) > 0;
                                        @endphp
                                        <input type="checkbox" 
                                            wire:model.live="selectedOrders" 
                                            value="{{ $order->id }}"
                                            {{ !$hasPrice ? 'disabled' : '' }}
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 {{ !$hasPrice ? 'opacity-40 cursor-not-allowed bg-gray-150' : '' }}"
                                            title="{{ !$hasPrice ? 'No se puede seleccionar hasta tener precio cotizado' : 'Seleccionar ítem' }}"
                                        >
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-4 max-w-[300px]">
                                    <div class="flex items-center gap-3">
                                        @php
                                            if ($order->status == 13) {
                                                $thumbnail = !empty($order->image_path) ? asset('storage/' . $order->image_path) : asset('images/placeholder-item.png');
                                            } else {
                                                $itemModel = \App\Models\Tenant\Items\Items::find($order->item_id);
                                                $thumbnail = $itemModel ? $itemModel->getPrincipalThumbnailUrl('COMERCIAL') : asset('images/placeholder-item.png');
                                            }
                                        @endphp
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 shrink-0 {{ $order->status != 13 ? 'cursor-pointer hover:opacity-80 transition-opacity' : '' }}"
                                             @if($order->status != 13) @click.stop="$dispatch('openImageModal', { productId: {{ $order->item_id }}, context: 'COMERCIAL' })" @endif>
                                            <img src="{{ $thumbnail }}" 
                                                 alt="Product" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="text-xs font-medium text-gray-900 dark:text-white break-words">
                                            {{ $order->item }}
                                        </div>
                                    </div>
                                </td>
                                <td x-show="showCols.factoryRef" class="px-4 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono">
                                        {{ $order->factory_ref ?? 'N/A' }}
                                    </div>
                                </td>
                                <td x-show="showCols.lastPrice" class="px-4 py-4 text-center">
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
                                <td x-show="showCols.label" class="px-4 py-4 text-center">
                                    @if (!empty($order->priority))
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                {{ in_array($order->priority, ['ASAP', 'Express']) ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                                {{ in_array($order->priority, ['Second', 'Express 2']) ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                                {{ in_array($order->priority, ['Third', 'Express 3']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}">
                                                {{ $order->priority }}
                                            </span>
                                            @if (!empty($order->priority_assigned_at))
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono whitespace-nowrap" title="Fecha de asignación">
                                                    {{ \Carbon\Carbon::parse($order->priority_assigned_at)->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $order->label ?? 'N/A' }}
                                        </span>
                                    @endif
                                </td>
                                <td x-show="showCols.quotedPrice" class="px-4 py-4 text-center"
                                    x-data="{ priceQ: {{ $order->price ?? 0 }} }"
                                    x-effect="if (document.activeElement !== $refs.priceInput) priceQ = {{ $order->price ?? 0 }}">
                                    @if ($profileUser == '17' && in_array($order->status, [1,2,4,6,7,13]))
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
                                <td x-show="showCols.comment" class="px-4 py-4">
                                    <div class="space-y-2 min-w-[200px]">
                                        <div x-data="{ comment: '' }" class="flex items-center gap-1">
                                            <input type="text" 
                                                x-model="comment"
                                                @change="$wire.saveComment({{ $order->id }}, comment); comment = ''"
                                                @keydown.enter="$wire.saveComment({{ $order->id }}, comment); comment = ''"
                                                class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="{{ Auth::user()?->profile_id == 17 ? 'Add comment...' : 'Añadir comentario...' }}">
                                            <button 
                                                @click="$wire.openModalHistory({{ $order->id }})"
                                                class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ isset($order->news) && $order->news == 1 ? 'animate-pulse' : '' }}"
                                                title="{{ Auth::user()?->profile_id == 17 ? 'View history' : 'Ver historial' }}">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                            </button>
                                        </div>
                                        
                                        @php
                                        $data = is_string($order->ultimo_comentario) ? json_decode($order->ultimo_comentario, true) : null;
                                        @endphp

                                        @if(is_array($data) && isset($data['type']))
                                            <div class="text-xs bg-gray-50 dark:bg-gray-700/30 p-2 rounded border border-gray-200 dark:border-gray-600">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $data['type'] === 'qty_change' ? (Auth::user()?->profile_id == 17 ? 'Quantity Change' : 'Cambio de Cantidad') : (Auth::user()?->profile_id == 17 ? 'Price Change' : 'Cambio de Precio') }}</span>
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
                                <td x-show="showCols.action" class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($order->status == 13)
                                            @if($profileUser != '17')
                                                <button wire:click="openConvertModal({{ $order->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Convertir</span>
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-500 italic font-medium">Cotización Temporal</span>
                                            @endif
                                        @endif
                                        @if($order->status == 2 && $profileUser != '17')
                                            <button wire:click="approvePrice({{ $order->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 whitespace-nowrap">
                                                <x-heroicon-o-check class="w-4 h-4" /> Approve price
                                            </button>
                                        @elseif($order->status == 4 && $profileUser == '17')
                                            <button wire:click="saveSendProduction({{ $order->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 whitespace-nowrap">
                                                <x-heroicon-o-check class="w-4 h-4" /> Production
                                            </button>
                                        @elseif($order->status == 5 && $profileUser == '17')
                                            <button wire:click="saveSendFinished({{ $order->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 whitespace-nowrap">
                                                <x-heroicon-o-check class="w-4 h-4" /> {{ Auth::user()?->profile_id == 17 ? 'Finished' : 'Terminar' }}
                                            </button>
                                        @elseif($order->status == 7)
                                            <button type="button"
                                                    @click="
                                                        Swal.fire({
                                                            title: '{{ $profileUser == 17 ? "Change quantities" : "Cambiar cantidades" }}',
                                                            text: '{{ $profileUser == 17 ? "Enter the total quantity to send for this item:" : "Ingresa la cantidad total a enviar para este ítem:" }}',
                                                            input: 'number',
                                                            inputAttributes: {
                                                                min: 0,
                                                                step: 1
                                                            },
                                                            inputValue: {{ $order->qty_requested }},
                                                            showCancelButton: true,
                                                            confirmButtonText: '{{ $profileUser == 17 ? "Accept" : "Aceptar" }}',
                                                            cancelButtonText: '{{ $profileUser == 17 ? "Cancel" : "Cancelar" }}',
                                                            inputValidator: (value) => {
                                                                if (value === '' || value === null || value === undefined || value < 0) {
                                                                    return '{{ $profileUser == 17 ? "You must enter a valid quantity" : "Debes ingresar una cantidad válida" }}';
                                                                }
                                                            }
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.changeShipmentQuantity({{ $order->id }}, result.value);
                                                            }
                                                        })
                                                    "
                                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 whitespace-nowrap" 
                                                    title="Cambiar cantidades del envío">
                                                <x-heroicon-o-arrow-path class="w-4 h-4" /> {{ Auth::user()?->profile_id == 17 ? 'Change quantities' : 'Cambiar cantidades' }}
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">N/A</span>
                                        @endif

                                        @if($filterNews == 1 && $order->status != 11 && $profileUser != '17')
                                            <button wire:click="confirmDelete({{ $order->id }})" class="p-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2" title="Eliminar Producto">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td x-show="showCols.status" class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $order->translated_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td x-show="showCols.qtyShipped" class="px-4 py-4 text-center"
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
                                <td x-show="showCols.shippingInfo && !$wire.selectedShipp" class="px-4 py-4">
                                    @if($order->status == 11)
                                        <div class="space-y-1 text-xs min-w-[220px]">
                                            <div class="flex flex-col gap-1 bg-red-50 dark:bg-red-950/30 p-2.5 rounded-lg border border-red-100 dark:border-red-900/40">
                                                <div class="flex items-center gap-1 text-red-700 dark:text-red-400 font-semibold">
                                                    <svg class="w-3.5 h-3.5 text-red-500 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                    <span>Eliminado por:</span>
                                                </div>
                                                <span class="text-gray-900 dark:text-white font-medium pl-4.5">{{ $order->deleted_by_user ?? 'N/A' }}</span>
                                                
                                                <div class="flex items-center gap-1 text-red-700 dark:text-red-400 font-semibold mt-1">
                                                    <span>Justificación:</span>
                                                </div>
                                                <p class="text-gray-700 dark:text-gray-300 italic pl-4.5 break-words">
                                                    "{{ $order->delete_justification ?? 'Sin registrar' }}"
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-1 text-xs min-w-[200px]">
                                            {{-- 
                                            @if($order->packing_number)
                                                <div class="flex items-center gap-1">
                                                    <span class="font-medium text-gray-500 dark:text-gray-400 w-12">Pack:</span>
                                                    <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $order->packing_number }}</span>
                                                </div>
                                            @endif
                                            --}}

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
                                                <span>
                                                    @if($order->received_at)
                                                        {{ \Carbon\Carbon::parse($order->received_at)->format('d/m/Y') }}
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td colspan="12" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
                    x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <!-- Modal Header -->
                    <div class="sticky top-0 z-10 flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $filterStatus == 13 ? 'Historial de la cotización' : 'Historial de la importación' }}
                        </h2>
                        <button wire:click="cancel" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <!-- Modal Content -->
                    <div class="p-6 space-y-6">
                        @if(count($this->timelineEvents) > 0)
                            <div class="space-y-6">
                                @foreach($this->timelineEvents as $event)
                                    <div class="flex items-start gap-4">
                                        <!-- Columna izquierda: Icono del Timeline y Línea conectora -->
                                        <div class="flex flex-col items-center flex-shrink-0">
                                            <span class="flex items-center justify-center w-8 h-8 rounded-full shadow-sm
                                                {{ $event->event_type === 'comment' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }}">
                                                @if($event->event_type === 'comment')
                                                    <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4" />
                                                @else
                                                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                                                @endif
                                            </span>
                                            @if(!$loop->last)
                                                <div class="w-0.5 bg-gray-200 dark:bg-gray-700 min-h-[3rem] flex-1 my-1"></div>
                                            @endif
                                        </div>
                                        
                                        <!-- Columna derecha: Tarjeta del Evento -->
                                        <div class="flex-1 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        {{ $event->name }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                                        {{ $event->event_type === 'comment' ? 'bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-50 text-green-700 border border-green-100 dark:bg-green-900/30 dark:text-green-400' }}">
                                                        {{ $event->event_type === 'comment' ? 'Comentario' : 'Cambio de Estado' }}
                                                    </span>
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ \Carbon\Carbon::parse($event->created_at)->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            
                                            <!-- Detalle del Evento -->
                                            @if($event->event_type === 'comment')
                                                @php
                                                    $data = is_string($event->comment) ? json_decode($event->comment, true) : null;
                                                @endphp
                                                @if(is_array($data) && isset($data['type']))
                                                    <div class="bg-blue-50/50 dark:bg-blue-950/20 p-3 rounded border border-blue-100 dark:border-blue-900/50 text-sm">
                                                        <span class="font-medium text-blue-700 dark:text-blue-400">
                                                            {{ $data['type'] === 'qty_change' ? 'Ajuste de Cantidad' : 'Ajuste de Precio' }}
                                                        </span>
                                                        <p class="mt-1 font-mono text-gray-700 dark:text-gray-300">
                                                            De: <span class="line-through text-gray-400">{{ $data['old'] }}</span> 
                                                            <span class="mx-2">→</span> 
                                                            A: <span class="font-bold text-gray-900 dark:text-white">{{ $data['new'] }}</span>
                                                        </p>
                                                        @if(!empty($data['note']))
                                                            <p class="mt-2 text-xs italic text-gray-500 dark:text-gray-400 border-l-2 border-blue-300 pl-2">
                                                                "{{ $data['note'] }}"
                                                            </p>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                        {{ $event->comment }}
                                                    </p>
                                                @endif
                                            @else
                                                <div class="flex items-center gap-4 bg-gray-50/50 dark:bg-gray-900/30 p-2.5 rounded border border-gray-100 dark:border-gray-800 text-sm">
                                                    <div class="text-center flex-1">
                                                        <span class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Estado Anterior</span>
                                                        <span class="px-2.5 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                            {{ $event->previousStatus->translated_name ?? $event->previousStatus->name ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-gray-300 dark:text-gray-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="text-center flex-1">
                                                        <span class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Estado Nuevo</span>
                                                        <span class="px-2.5 py-1 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-100 dark:bg-green-900/30 dark:text-green-400">
                                                            {{ $event->newStatus->translated_name ?? $event->newStatus->name ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-gray-500 dark:text-gray-400 py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <x-heroicon-o-clock class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                                <p class="text-lg font-medium">Línea de tiempo vacía</p>
                                <p class="text-sm">No se han registrado eventos o comentarios para esta importación.</p>
                            </div>
                        @endif

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
                                <h3 class="font-medium text-indigo-900 dark:text-indigo-200">Selected Products</h3>
                            </div>
                    
                            <div class="grid grid-cols-1 gap-3">
                                <div class="bg-white dark:bg-gray-800/80 rounded-lg p-3 shadow-sm border border-indigo-200/50 dark:border-indigo-800/30">
                                    <div class="flex items-center gap-3">
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider mb-0">Total Products to Ship:</p>
                                        <span class="text-base font-black text-indigo-700 dark:text-indigo-300">{{ count($selectedOrders) }}</span>
                                    </div>
                                </div>
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
                            <!-- Selector: Nuevo o Existente -->
                            <div class="flex items-center gap-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="is_existing_shipp" wire:model.live="isExistingShipping" value="0" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">New Shipment</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="is_existing_shipp" wire:model.live="isExistingShipping" value="1" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Existing Shipment</span>
                                </label>
                            </div>

                            @if($isExistingShipping)
                                <!-- SELECT ENVIO EXISTENTE -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Seleccionar Envío Creado <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="selectedExistingShippingId"
                                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow hover:shadow-sm">
                                        <option value="">-- Selecciona un envío activo --</option>
                                        @foreach($this->shippments as $sh)
                                            <option value="{{ $sh['id'] }}">{{ $sh['way'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedExistingShippingId') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            @else
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
                                        <option value="Express" class="py-2">EXPRESS</option>
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
                            @endif
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

    <!-- Modal confirmación y justificación de eliminación -->
    @if ($showModalDelete)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-red-600 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Eliminar Producto
                        </h3>
                        <button type="button" wire:click="$set('showModalDelete', false)" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ¿Estás seguro de que deseas eliminar este producto? Esta acción cambiará el estado del producto a <strong class="text-gray-900 dark:text-white">ELIMINADO</strong> para fines de auditoría.
                        </p>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Justificación de la Eliminación <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="deleteJustification"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:outline-none transition-shadow resize-none"
                                placeholder="Escribe la razón o justificación detallada por la cual eliminas este producto..."></textarea>
                            @error('deleteJustification') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <button type="button" wire:click="$set('showModalDelete', false)"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteOrderWithJustification"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Confirmar Eliminación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL HISTORIAL DE COMENTARIOS DEL ENVÍO -->
    @if ($showModalShipmentHistory)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModalShipmentHistory', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-red-600 dark:text-red-400">
                            Historial del envío / Shipping History
                        </h3>
                        <button type="button" wire:click="$set('showModalShipmentHistory', false)" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4"
                         x-data
                         x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight });"
                         @scroll-to-bottom.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">
                        @forelse($this->shipmentComments as $sc)
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4" />
                                    </div>
                                    <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 my-1"></div>
                                </div>
                                <div class="flex-1 bg-white dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-xl p-2.5 shadow-sm">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-gray-900 dark:text-white text-xs" title="{{ $sc->name }}">
                                                {{ \Illuminate\Support\Str::limit(strtoupper($sc->name), 22, '...') }}
                                            </span>
                                            <span class="px-1.5 py-0.5 text-[9px] font-semibold bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 rounded border border-blue-200 dark:border-blue-800 uppercase">
                                                COMENTARIO
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                            {{ \Carbon\Carbon::parse($sc->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="w-full text-left" style="text-align: left !important;">
                                        @php
                                            $commentText = $sc->comment;
                                            $original = $commentText;
                                            $translation = null;

                                            if (strpos($commentText, '[TRANSLATED]') !== false) {
                                                $parts = explode('[TRANSLATED]', $commentText);
                                                $original = $parts[0];
                                                $translation = $parts[1];
                                            } elseif (strpos($commentText, '--- Translated to English ---') !== false) {
                                                $parts = explode('--- Translated to English ---', $commentText);
                                                $original = $parts[0];
                                                $translation = $parts[1];
                                            } elseif (strpos($commentText, '--- Traducido al Español ---') !== false) {
                                                $parts = explode('--- Traducido al Español ---', $commentText);
                                                $original = $parts[0];
                                                $translation = $parts[1];
                                            }
                                        @endphp
                                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-normal text-left whitespace-pre-line" style="text-align: left !important; margin: 0; padding: 0;">
                                            {{ trim($original) }}
                                        </p>
                                        @if($translation)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-normal italic text-left mt-1.5 whitespace-pre-line" style="text-align: left !important; margin-top: 0.375rem !important; margin-bottom: 0; padding: 0;">
                                                {{ trim($translation) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No hay comentarios registrados para este envío.
                            </div>
                        @endforelse
                    </div>
 
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-3 w-full">
                            <textarea wire:model="shipmentComment" 
                                      placeholder="Escribe una nota para el envío..." 
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-shadow text-sm resize-none"
                                      rows="3"></textarea>
                            <div class="flex justify-end gap-3">
                                <button type="button" wire:click="$set('showModalShipmentHistory', false)" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
                                    Cerrar / Close
                                </button>
                                <button type="button" wire:click="saveShipmentComment" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                                    Guardar Nota / Save Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL MARCAR COMO RECIBIDO / MARK RECEIVED -->
    @if ($showModalMarkReceived)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModalMarkReceived', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            Recibir Envío / Mark as Received
                        </h3>
                        <button type="button" wire:click="$set('showModalMarkReceived', false)" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <form wire:submit.prevent="markShipmentAsReceived" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                ETA (Llegada a Puerto) <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="receivedEta" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('receivedEta') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Llega a Fervicom <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="receivedFervicomArrival" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('receivedFervicomArrival') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" wire:click="$set('showModalMarkReceived', false)"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-all">
                                Cancelar
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-not-allowed"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg font-medium text-sm text-white transition-all hover:shadow">
                                <svg wire:loading wire:target="markShipmentAsReceived" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading wire:target="markShipmentAsReceived">Sincronizando...</span>
                                <span wire:loading.remove wire:target="markShipmentAsReceived">Confirmar Recepción</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Crear Producto Nuevo (Borrador) -->
    @if($showModalCreateNewProduct)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Crear Producto Nuevo (Borrador)
                    </h3>
                    <button wire:click="$set('showModalCreateNewProduct', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="saveNewProduct" class="p-6 space-y-6">
                    <!-- Código Interno Autogenerado y Descripción -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código Interno <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="newProductCode" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm focus:outline-none">
                            @error('newProductCode') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción / Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="newProductDescription" placeholder="Ej: NEW_PRODUCT" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                            @error('newProductDescription') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Proveedor y Ref Fábrica -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proveedor <span class="text-red-500">*</span></label>
                            <select wire:model="newProductSupplierId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccionar Proveedor</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('newProductSupplierId') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Referencia de fábrica</label>
                            <input type="text" wire:model="newProductFactoryRef" placeholder="Ej: Ref Fábrica" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                            @error('newProductFactoryRef') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Cantidad Mínima del Proveedor y Factores Generales -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cant Mínima Proveedor <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="newProductMinQty" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                            @error('newProductMinQty') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Porcentaje (%)</label>
                            <input type="number" step="0.01" wire:model="newProductPorcentaje" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Factor</label>
                            <input type="number" step="0.01" wire:model="newProductFactor" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                        </div>
                    </div>

                    <!-- Imagen del Producto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Imagen del Producto</label>
                        <input type="file" wire:model="newProductImage" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200">
                        @error('newProductImage') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        
                        @if ($newProductImage)
                            <div class="mt-4 flex items-center justify-center p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <img src="{{ $newProductImage->temporaryUrl() }}" class="max-h-36 object-contain rounded">
                            </div>
                        @endif
                    </div>

                    <!-- Sección de Factores de Precios -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Factores de Precio y Descuentos</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">$EXW <span class="text-red-500">*</span></label>
                                <input type="number" step="0.0001" wire:model="newProductExw" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                                @error('newProductExw') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Incr. Fletes</label>
                                <input type="number" step="0.01" wire:model="newProductIncrFletes" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Factor PVP1</label>
                                <input type="number" step="0.01" wire:model="newProductPvp1" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Factor PVP Mín</label>
                                <input type="number" step="0.01" wire:model="newProductPvpMin" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 text-center">
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="$set('showModalCreateNewProduct', false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg text-sm font-medium text-white shadow transition-colors">
                            Crear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL: Convertir Producto Nuevo a Real (Camilo) -->
    @if($showModalConvertNewProduct)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Convertir a Producto Real
                    </h3>
                    <button wire:click="$set('showModalConvertNewProduct', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="convertNewProductToReal" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código Interno Definitivo <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="finalInternalCode" placeholder="Ej: 7800201" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('finalInternalCode') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría del Inventario <span class="text-red-500">*</span></label>
                        <select wire:model="finalCategoryId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccionar Categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('finalCategoryId') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="$set('showModalConvertNewProduct', false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 border border-transparent rounded-lg text-sm font-medium text-white shadow transition-colors">
                            Convertir y Aprobado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Overlay de carga global bloqueante para Alegra y base de datos -->
    <div wire:loading.flex wire:target="rotatePriorities, markShipmentAsReceived" class="bg-gray-900/50 backdrop-blur-sm" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; align-items: center; justify-content: center; z-index: 99999;">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl max-w-sm w-full mx-4 text-center border border-gray-100 dark:border-gray-700">
            <div class="flex justify-center mb-4" style="display: flex; justify-content: center;">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent"></div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Procesando Recepción</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Guardando información localmente y sincronizando el ajuste de inventario con Alegra. Por favor, no cierres esta ventana.
            </p>
    </div>
</div>

@script
<script>
    $wire.on('confirm-shipment-edit', (data) => {
        const payload = data[0];
        Swal.fire({
            title: payload.title,
            text: payload.text,
            input: 'text',
            inputPlaceholder: payload.placeholder,
            showCancelButton: true,
            confirmButtonText: payload.isSupplier ? 'Save' : 'Guardar',
            cancelButtonText: payload.isSupplier ? 'Cancel' : 'Cancelar',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return payload.isSupplier ? 'You must enter a justification' : 'Debes ingresar una justificación';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.updateShipmentField(payload.field, payload.newValue, result.value);
            } else {
                $wire.cancelShipmentEdit();
            }
        });
    });
</script>
@endscript
