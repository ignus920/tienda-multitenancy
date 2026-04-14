<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Remisiones</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de registros</p>
            </div>
            <!-- <div class="flex items-center space-x-3">
                <button class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium flex items-center transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Nueva Remisión</span>
                </button>
            </div> -->
            @include('livewire.tenant.parameters.dynamic-buttons', ['buttons' => $this->dynamicButtons])
        </div>
    </div>

    <!-- Barra de Herramientas -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Buscador y Filtros -->
            <div class="flex-1 max-w-2xl flex items-center space-x-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Búsqueda rápida..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-gray-900 dark:text-slate-200 placeholder-gray-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-colors">
                </div>
                <button wire:click="$toggle('showAdvancedSearch')" 
                    class="flex items-center px-4 py-2 text-sm font-medium border border-gray-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Búsqueda Avanzada
                </button>
            </div>

            <!-- Acciones y Paginación -->
            <div class="flex items-center space-x-3">
                @if(count($selectedRemissions) > 0)
                    <button wire:click="facturarMasivo" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center transition-all animate-pulse">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Facturar ({{ count($selectedRemissions) }})
                    </button>
                @endif

                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                    <select wire:model.live="perPage"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Panel de Búsqueda Avanzada -->
        <div x-show="$wire.showAdvancedSearch" x-transition 
            class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">NIT / Cédula</label>
                <input type="text" wire:model.live="searchNit" placeholder="Ej: 900..."
                    class="block w-full px-3 py-1.5 border border-gray-300 dark:border-slate-600 rounded bg-gray-50 dark:bg-slate-800 text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nombre / Razón Social</label>
                <input type="text" wire:model.live="searchName" placeholder="Buscar cliente..."
                    class="block w-full px-3 py-1.5 border border-gray-300 dark:border-slate-600 rounded bg-gray-50 dark:bg-slate-800 text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Número Cotización</label>
                <input type="text" wire:model.live="searchQuote" placeholder="Ej: COT-123"
                    class="block w-full px-3 py-1.5 border border-gray-300 dark:border-slate-600 rounded bg-gray-50 dark:bg-slate-800 text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Fecha Desde</label>
                <input type="date" wire:model.live="searchStartDate"
                    class="block w-full px-3 py-1.5 border border-gray-300 dark:border-slate-600 rounded bg-gray-50 dark:bg-slate-800 text-sm focus:ring-indigo-500">
            </div>
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Fecha Hasta</label>
                    <input type="date" wire:model.live="searchEndDate"
                        class="block w-full px-3 py-1.5 border border-gray-300 dark:border-slate-600 rounded bg-gray-50 dark:bg-slate-800 text-sm focus:ring-indigo-500">
                </div>
                <button wire:click="clearFilters" 
                    class="p-2 text-gray-500 hover:text-red-500 transition-colors" title="Limpiar filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="overflow-x-auto min-h-[450px]">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-gray-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>REMISIÓN #</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>FECHA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>COTIZACIÓN</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>CLIENTE</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>ENTREGA Y PAGO</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center justify-end space-x-1">
                                <span>TOTAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>STATUS</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>FACTURA</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($remissions as $remission)
                        <tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="px-4 py-4 text-center">
                                @if($remission->status === 'REGISTRADO' && !$remission->invoice)
                                    <input type="checkbox" wire:model.live="selectedRemissions" value="{{ $remission->id }}"
                                        class="rounded border-gray-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                @elseif($remission->invoice)
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Ya facturada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                #{{ $remission->consecutive }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                {{ $remission->created_at->format('d/m/Y') }}
                                <br><small class="text-gray-500 dark:text-slate-400">{{ $remission->created_at->format('H:i') }}</small>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                #{{ $remission->quote->consecutive ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">
                                {{ $remission->quote->customer_name ?? 'N/A' }}
                                @if(isset($remission->quote->customer->identification))
                                    <br><small class="text-gray-500 dark:text-slate-400">{{ $remission->quote->customer->identification }}</small>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300">
                                <div class="space-y-1">
                                    {{-- Tipo de Entrega --}}
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-blue-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                                        </svg>
                                        @if($remission->deliveryTypeModel)
                                            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                                {{ $remission->deliveryTypeModel->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">Sin tipo</span>
                                        @endif
                                    </div>

                                    {{-- Método de Pago --}}
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-green-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                                        </svg>
                                        @if($remission->methodPayment)
                                            <span class="text-xs font-medium text-green-700 dark:text-green-300">
                                                {{ $remission->methodPayment->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">Sin método</span>
                                        @endif
                                    </div>

                                    {{-- Observaciones si es tipo "Otro" --}}
                                    @if($remission->deliveryTypeModel && strtolower($remission->deliveryTypeModel->name) === 'otro' && !empty($remission->observations_return))
                                    <div class="flex items-start mt-1">
                                        <svg class="w-3 h-3 text-amber-500 mr-1.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-amber-700 dark:text-amber-400 italic">
                                            {{ $remission->observations_return }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white font-medium">
                                @php
                                    $total = $remission->details->sum(function ($detail) {
                                        return $detail->quantity * $detail->value;
                                    });
                                @endphp
                                ${{ number_format($total, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($remission->status === 'ENTREGADO')
                                    {{-- Estado ENTREGADO no es clickeable --}}
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $remission->status }}
                                        <svg class="inline w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                @else
                                    {{-- Estados que pueden cambiar --}}
                                    <button wire:click="changeStatus({{ $remission->id }})"
                                            class="px-3 py-1 text-xs font-semibold rounded-full transition-colors cursor-pointer hover:opacity-80
                                            @if($remission->status === 'REGISTRADO') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($remission->status === 'ALISTAMIENTO') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($remission->status === 'EMPACADO') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                            @elseif($remission->status === 'EN RECORRIDO') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                        {{ $remission->status }}
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($remission->invoice)
                                    @if($remission->invoice->status === 'FACTURADO' && $remission->invoice->invoiceNumber)
                                        <!-- ✅ MODIFICADO: Mostrar número de factura en verde cuando esté facturado -->
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            #{{ $remission->invoice->invoiceNumber }}
                                        </span>
                                    @else
                                        <!-- Mostrar estado para otros casos -->
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if($remission->invoice->status === 'SIN EMITIR') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $remission->invoice->status }}
                                            </span>
                                            @if($remission->invoice->invoiceNumber)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    #{{ $remission->invoice->invoiceNumber }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Sin facturar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left static" style="position: static !important;">
                                    <button @click="open = !open"
                                        class="flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition-colors">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                    <!-- Menú Desplegable -->
                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        @click="open = false"
                                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 z-50"
                                        style="display: none;">
                                        <div class="py-1">
                                            <button wire:click="viewDetails({{ $remission->id }})"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-800 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Ver Detalle
                                            </button>
                                            @if(!$remission->invoice || $remission->invoice->status !=='FACTURADO')
                                            <button wire:click="editarRemision({{ $remission->id }})" class="w-full text-left px-4 py-2 text-sm text-yellow-800 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Editar
                                            </button>
                                            @endif
                                            <button wire:click="printRemission({{ $remission->id }})" class="w-full text-left px-4 py-2 text-sm text-green-800 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Imprimir
                                            </button>
                                            @if($remission->invoice)
                                            <button wire:click="printInvoice({{ $remission->id }})" class="w-full text-left px-4 py-2 text-sm text-blue-800 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Imprimir Factura
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                No se encontraron remisiones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($remissions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $remissions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Detalle de Remisión -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showDetailModal') }"
             x-show="show"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" aria-hidden="true" @click="show = false"></div>

            <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left shadow-2xl transform transition-all w-full max-w-2xl border border-gray-200 dark:border-slate-700 max-h-[90vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                @if($selectedRemission)

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Detalles de Remisión #{{ $selectedRemission['consecutive'] }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                Fecha: {{ $selectedRemission['created_at_formatted'] }} | Estado: 
                                <span class="font-medium text-indigo-500">{{ $selectedRemission['status'] }}</span>
                            </p>
                        </div>
                        <button @click="show = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-slate-300 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Info Cliente -->
                            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-5 border border-gray-100 dark:border-slate-700">
                                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4">Información del Cliente</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Nombre:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['quote']['customer_name'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Identificación:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['quote']['customer']['identification'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Tipo persona:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Natural</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Email:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['quote']['customer']['billingEmail'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles Generales -->
                            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-5 border border-gray-100 dark:border-slate-700">
                                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4">Detalles Generales</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Cotización:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ $selectedRemission['quote']['consecutive'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Bodega:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['store']['name'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Vendedor:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['seller_name'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Fecha Entrega:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['delivery_date_formatted'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Tipo Entrega:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['delivery_type_model']['name'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Método Pago:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedRemission['method_payment']['name'] ?? 'N/A' }}</span>
                                    </div>
                                    @if(!empty($selectedRemission['observations_return']) && isset($selectedRemission['delivery_type_model']['name']) && strtolower($selectedRemission['delivery_type_model']['name']) === 'otro')
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-slate-400">Observaciones:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white italic">{{ $selectedRemission['observations_return'] }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Productos -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-slate-700 mb-6">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Producto</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Cant.</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Precio Unit.</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    @php $totalModal = 0; @endphp
                                    @foreach($selectedRemission['details'] as $detalle)
                                        @php 
                                            $subtotal = $detalle['quantity'] * $detalle['value'];
                                            $totalModal += $subtotal;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                                {{ $detalle['item']['name'] ?? $detalle['description'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-slate-300">
                                                {{ number_format($detalle['quantity'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-slate-300">
                                                ${{ number_format($detalle['value'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                                                ${{ number_format($subtotal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-slate-700/50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-700 dark:text-white uppercase tracking-wider">
                                            Total General:
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-indigo-500">
                                            ${{ number_format($totalModal, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                        <button @click="show = false" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold transition-all transform hover:scale-105 active:scale-95 shadow-lg shadow-indigo-500/30">
                            Cerrar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </template>

    <!-- Modal de Confirmación de Facturación -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showInvoiceModal') }"
             x-show="show"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" aria-hidden="true" @click="show = false"></div>

            <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left shadow-2xl transform transition-all w-full max-w-xl border border-gray-200 dark:border-slate-700 max-h-[90vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">



                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Confirmar Facturación de Remisiones
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                            Revise la información antes de proceder con la facturación
                        </p>
                    </div>
                    <button wire:click="closeInvoiceModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-slate-300 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto">
                    <!-- Información del Cliente -->
                    @if($selectedCustomer)
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800 mb-6">
                        <h4 class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Cliente a Facturar
                        </h4>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                            <div class="flex-1">
                                <span class="text-[10px] uppercase font-bold text-gray-500 dark:text-slate-400">Nombre/Razón Social:</span>
                                <p class="text-base font-bold text-gray-900 dark:text-white truncate">
                                    {{ $selectedCustomer['businessName'] ?: ($selectedCustomer['firstName'] . ' ' . $selectedCustomer['lastName']) }}
                                </p>
                            </div>
                            <div class="sm:text-right">
                                <span class="text-[10px] uppercase font-bold text-gray-500 dark:text-slate-400">Identificación:</span>
                                <p class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ $selectedCustomer['identification'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @endif

                    <!-- Resumen de Remisiones -->
                    <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-5 border border-gray-100 dark:border-slate-700 mb-6">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Remisiones Seleccionadas
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-600">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ count($selectedRemissionsData) }}</p>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Remisiones</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-600">
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalItems }}</p>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Items Totales</p>
                            </div>
                            <div class="text-center p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-600">
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    ${{ number_format(array_sum(array_column($selectedRemissionsData, 'total_value')), 2, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Valor Total</p>
                            </div>
                        </div>

                        <!-- Lista de Remisiones -->
                        @if(count($selectedRemissionsData) > 0)
                        <div class="space-y-2">
                            @foreach($selectedRemissionsData as $remissionData)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-600">
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900 dark:text-white">Remisión #{{ $remissionData['consecutive'] }}</span>
                                    <span class="text-sm text-gray-500 dark:text-slate-400 ml-2">{{ $remissionData['created_at'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-600 dark:text-slate-400">{{ $remissionData['items_count'] }} items</span>
                                    <span class="ml-3 font-semibold text-gray-900 dark:text-white">
                                        ${{ number_format($remissionData['total_value'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Advertencia -->
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-amber-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <h5 class="text-sm font-semibold text-amber-700 dark:text-amber-300">Importante</h5>
                                <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">
                                    Al confirmar, se creará una factura electrónica para todas las remisiones seleccionadas.
                                    Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end space-x-3">
                    <button wire:click="closeInvoiceModal"
                            class="px-6 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button wire:click="confirmarFacturacion"
                            wire:loading.attr="disabled"
                            wire:target="confirmarFacturacion"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium flex items-center transition-colors disabled:opacity-50">
                        <svg wire:loading.remove wire:target="confirmarFacturacion" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <svg wire:loading wire:target="confirmarFacturacion" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="confirmarFacturacion">Confirmar Facturación</span>
                        <span wire:loading wire:target="confirmarFacturacion">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
