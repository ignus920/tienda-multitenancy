<div x-data="{
    openColumnsDropdown: false,
    visibleColumns: {
        codigo: true,
        descripcion: true,
        programacion: true,
        existencias: true,
        cantidad: true,
        porcentaje: true,
        salida: true,
        entrada: true,
        exw: true,
        acciones: true
    },
    init() {
        const stored = localStorage.getItem('import_list_columns');
        if (stored) {
            try {
                this.visibleColumns = { ...this.visibleColumns, ...JSON.parse(stored) };
            } catch(e) {}
        }
    },
    toggleColumn(key) {
        this.visibleColumns[key] = !this.visibleColumns[key];
        localStorage.setItem('import_list_columns', JSON.stringify(this.visibleColumns));
    }
}" @click.away="openColumnsDropdown = false">
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

            <!-- Filtro de Productos Críticos -->
            <div class="w-full sm:w-64">
                <select wire:model.live="filterCritical" 
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="ninguno">Todos los Productos</option>
                    <option value="importados">Productos críticos importados</option>
                    <option value="compra_nacional">Productos críticos compra nacional</option>
                </select>
            </div>

            <!-- Selector de Columnas (Alpine.js + LocalStorage) -->
            <div class="relative w-full sm:w-auto">
                <button type="button" @click.stop="openColumnsDropdown = !openColumnsDropdown"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider bg-white hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/80 dark:text-gray-300 border border-gray-300 dark:border-gray-600 transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Columnas</span>
                    <svg class="w-3 h-3 ml-2 transition-transform duration-200" :class="{ 'rotate-180': openColumnsDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Menú Dropdown -->
                <div x-show="openColumnsDropdown"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg z-50 p-3 space-y-2 text-xs text-gray-700 dark:text-gray-300"
                     style="display: none;">
                    <div class="font-bold border-b border-gray-150 dark:border-gray-700 pb-1.5 mb-2 text-gray-400">
                        Visibilidad de columnas
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.codigo" @change="toggleColumn('codigo')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Código</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.descripcion" @change="toggleColumn('descripcion')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Descripción</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.programacion" @change="toggleColumn('programacion')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Programación</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.existencias" @change="toggleColumn('existencias')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Existencias ERP</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.cantidad" @change="toggleColumn('cantidad')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Cantidad</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.porcentaje" @change="toggleColumn('porcentaje')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Porcentaje</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.salida" @change="toggleColumn('salida')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Salida ERP</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.entrada" @change="toggleColumn('entrada')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Entrada ERP</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.exw" @change="toggleColumn('exw')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>EXW</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 p-1.5 rounded transition-colors">
                        <input type="checkbox" :checked="visibleColumns.acciones" @change="toggleColumn('acciones')" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Acciones</span>
                    </label>
                </div>
            </div>

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
                 @php
                     $asapDisabled = in_array('asap', $this->occupiedPriorities);
                     $secondDisabled = in_array('second', $this->occupiedPriorities);
                     $thirdDisabled = in_array('third', $this->occupiedPriorities);
                     $expressDisabled = in_array('express', $this->occupiedPriorities);
                     $express2Disabled = in_array('express 2', $this->occupiedPriorities);
                     $express3Disabled = in_array('express 3', $this->occupiedPriorities);
                 @endphp

                 <button wire:click="assignPriorityToSelected('ASAP')" 
                         {{ $asapDisabled ? 'disabled' : '' }}
                         style="background-color: #dc2626; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $asapDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     ASAP
                 </button>
                 <button wire:click="assignPriorityToSelected('Second')" 
                         {{ $secondDisabled ? 'disabled' : '' }}
                         style="background-color: #d97706; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $secondDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     Second
                 </button>
                 <button wire:click="assignPriorityToSelected('Third')" 
                         {{ $thirdDisabled ? 'disabled' : '' }}
                         style="background-color: #2563eb; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $thirdDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     Third
                 </button>
                 
                 {{-- Nuevas Prioridades Express --}}
                 <span class="text-gray-300 mx-1">|</span>
                 
                 <button wire:click="assignPriorityToSelected('Express')" 
                         {{ $expressDisabled ? 'disabled' : '' }}
                         style="background-color: #dc2626; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $expressDisabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     Express
                 </button>
                 <button wire:click="assignPriorityToSelected('Express 2')" 
                         {{ $express2Disabled ? 'disabled' : '' }}
                         style="background-color: #d97706; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $express2Disabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     Express 2
                 </button>
                 <button wire:click="assignPriorityToSelected('Express 3')" 
                         {{ $express3Disabled ? 'disabled' : '' }}
                         style="background-color: #2563eb; color: #ffffff;"
                         class="px-3 py-1.5 rounded-lg text-xs font-bold transition-opacity shadow {{ $express3Disabled ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'hover:opacity-90' }}">
                     Express 3
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
        <div class="overflow-x-auto max-h-[70vh] overflow-y-auto custom-scrollbar blue-scrollbar">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th scope="col" class="w-10 px-6 py-3"></th>
                        <th scope="col" x-show="visibleColumns.codigo" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
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
                        <th scope="col" x-show="visibleColumns.descripcion" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                        <th scope="col" x-show="visibleColumns.existencias" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
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
                        <th scope="col" x-show="visibleColumns.cantidad" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                        <th scope="col" x-show="visibleColumns.porcentaje" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
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
                        <th scope="col" x-show="visibleColumns.salida" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" 
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
                        <th scope="col" x-show="visibleColumns.entrada" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Entrada ERP</th>
                        <th scope="col" x-show="visibleColumns.exw" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">EXW</th>
                        <th scope="col" x-show="visibleColumns.acciones" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($items as $item)
                        <tr wire:key="import-item-row-{{ $item->id }}"
                            x-data="{ 
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
                            <td x-show="visibleColumns.codigo" class="px-6 py-4 whitespace-nowrap">
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
                        <td x-show="visibleColumns.descripcion" class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white font-medium">{{ $item->description ?? $item->name }}</div>
                            <!-- Programación Compacta debajo de la Descripción -->
                            <div x-show="visibleColumns.programacion" class="flex flex-wrap items-center gap-1 mt-1" onclick="event.stopPropagation()">
                                @forelse($item->programaciones ?? [] as $prog)
                                    @php
                                        $estadoTraducido = match(strtolower($prog->status_name ?? '')) {
                                            'requested' => 'Solicitado',
                                            'pending' => 'Pendiente',
                                            'approved' => 'Aprobado',
                                            'production', 'in production' => 'En producción',
                                            'transit', 'in transit' => 'En tránsito',
                                            default => $prog->status_name ?? 'Solicitado'
                                        };

                                        $prioridadLower = strtolower($prog->priority ?? '');
                                        $badgeClasses = match(true) {
                                            in_array($prioridadLower, ['asap', 'express']) => 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/50 px-1 py-0.5 rounded text-[8px] font-black uppercase',
                                            in_array($prioridadLower, ['second', 'express 2']) => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 px-1 py-0.5 rounded text-[8px] font-black uppercase',
                                            in_array($prioridadLower, ['third', 'express 3']) => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50 px-1 py-0.5 rounded text-[8px] font-black uppercase',
                                            default => 'bg-gray-50 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-800/50 px-1 py-0.5 rounded text-[8px] font-semibold'
                                        };
                                    @endphp
                                    
                                    <div x-data="{ show: false }" class="relative inline-block" @click.away="show = false">
                                        <div class="bg-gray-50 dark:bg-gray-800/80 rounded-lg p-1.5 border border-gray-200 dark:border-gray-700 shadow-sm text-[10px] w-auto max-w-xs flex flex-col gap-0.5 cursor-help whitespace-nowrap"
                                             @mouseenter="show = true"
                                             @mouseleave="show = false">
                                            
                                            <!-- Fila 1: Cantidad | Estado -->
                                            <div class="flex items-center gap-1 font-bold text-gray-700 dark:text-gray-300">
                                                @if($prog->status_id == 7 || !empty($prog->shipment_number))
                                                    <span class="text-blue-600 dark:text-blue-400 font-extrabold">{{ number_format($prog->qty_requested, 0) }}</span>
                                                @else
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-extrabold">{{ number_format($prog->qty_requested, 0) }}</span>
                                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                                    <span>{{ $estadoTraducido }}</span>
                                                @endif
                                            </div>

                                            <!-- Fila 2: Prioridad / Shipment | Fecha -->
                                            <div class="flex items-center gap-1 text-[9px] text-gray-500 dark:text-gray-400 font-semibold">
                                                @if($prog->status_id == 7 || !empty($prog->shipment_number))
                                                    <span>{{ $prog->shipment_number ?? 'Shipment' }}</span>
                                                @else
                                                    <span class="{{ $badgeClasses }}">{{ $prog->priority }}</span>
                                                    @if($prog->due_date)
                                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                                        <span class="text-[9px] text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($prog->due_date)->format('d/m/y') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Tooltip en hover -->
                                        <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center font-normal leading-normal normal-case select-none pointer-events-none">
                                            {{ number_format($prog->qty_requested, 0) }} unidades {{ $estadoTraducido }}
                                            @if(!empty($prog->priority))
                                                 - Prioridad: {{ $prog->priority }}
                                            @endif
                                            @if($prog->due_date)
                                                 (Entrega: {{ \Carbon\Carbon::parse($prog->due_date)->format('d/m/y') }})
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-gray-400 text-[9px] italic select-none">Sin programar</span>
                                @endforelse
                            </div>
                        </td>
                        <td x-show="visibleColumns.existencias" class="px-6 py-4 whitespace-nowrap relative">
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
                        <td x-show="visibleColumns.cantidad" class="px-6 py-4 whitespace-nowrap" onclick="event.stopPropagation()">
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
                        <td x-show="visibleColumns.porcentaje" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->percentage ?? 0 }}%</td>
                        <td x-show="visibleColumns.salida" class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-red-600 dark:text-red-400">{{ number_format($item->outsideMovement ?? 0, 0) }}</div>
                        </td>
                        <td x-show="visibleColumns.entrada" class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-green-600 dark:text-green-400">{{ number_format($item->insideMovement ?? 0, 0) }}</div>
                        </td>
                        <td x-show="visibleColumns.exw" class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($item->exw ?? 0, 2) }}</div>
                        </td>
                        <td x-show="visibleColumns.acciones" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
        <div wire:key="import-item-mobile-{{ $item->id }}"
            wire:click="selectItem({{ $item->id }}, {{ $item->quantity ?? 0 }})"
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

    <style>
        .blue-scrollbar {
            scrollbar-width: auto !important;
            scrollbar-color: #2563eb #f1f5f9 !important;
        }
        .blue-scrollbar::-webkit-scrollbar {
            width: 10px !important;
            height: 10px !important;
            display: block !important;
        }
        .blue-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 10px !important;
        }
        .blue-scrollbar::-webkit-scrollbar-thumb {
            background: #2563eb !important;
            border-radius: 10px !important;
        }
        .blue-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #1d4ed8 !important;
        }
        .dark .blue-scrollbar {
            scrollbar-color: #3b82f6 #1e293b !important;
        }
        .dark .blue-scrollbar::-webkit-scrollbar-track {
            background: #1e293b !important;
            border-radius: 10px !important;
        }
        .dark .blue-scrollbar::-webkit-scrollbar-thumb {
            background: #3b82f6 !important;
            border-radius: 10px !important;
        }
        .dark .blue-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #2563eb !important;
        }
    </style>
</div>