<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Facturas</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de facturas</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @include('livewire.tenant.parameters.dynamic-buttons', ['buttons' => $this->dynamicButtons])
            </div>
        </div>
    </div>

    <!-- Barra de Herramientas -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-slate-700 transition-colors shadow-sm">
        <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
            <!-- Búsqueda Rápida -->
            <div style="flex: 1.5; min-width: 200px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Búsqueda rápida</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Búsqueda rápida..."
                        class="block w-full pl-9 pr-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                </div>
            </div>

            <!-- Desde -->
            <div style="flex: 1; min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">desde:</label>
                <input type="date" wire:model.live="filterDateFrom"
                    class="block w-full px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
            </div>

            <!-- Hasta -->
            <div style="flex: 1.2; min-width: 250px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">hasta:</label>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="filterDateTo"
                        class="block w-full px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    <button wire:click="clearFilters" title="Limpiar filtros"
                        class="p-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <button wire:click="exportToExcel" wire:loading.attr="disabled" title="Exportar a Excel"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-1 shadow-sm">
                        <!-- Icono normal de Excel (se oculta al cargar) -->
                        <svg wire:loading.remove wire:target="exportToExcel" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <!-- Spinner de carga (se muestra al cargar) -->
                        <svg wire:loading wire:target="exportToExcel" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportToExcel">Excel</span>
                        <span wire:loading wire:target="exportToExcel">Exportando...</span>
                    </button>
                </div>
            </div>

            <!-- Mostrar -->
            <div class="ml-auto">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">MOSTRAR</label>
                <select wire:model.live="perPage"
                    class="block px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="overflow-x-auto min-h-[450px]">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>FECHA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>No. REM</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>FACTURA #</span>
                                @if ($sortField === 'invoiceNumber')
                                    @if ($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>ESTADO FACTURA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>ESTADO PAGO</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>SUCURSAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>VENDEDOR</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>SUBTOTAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>TOTAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>NOTA CRÉDITO</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>RET. FUENTE</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>RET. ICA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>RET. IVA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>No. ORDEN</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>VALOR CON RETENCIONES</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>ACCIONES</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invoice->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invoice->remission_consecutive }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                #{{ $invoice->invoiceNumber }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($invoice->status === 'FACTURADO') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($invoice->status === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($invoice->status === 'SIN EMITIR') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($invoice->status_payment === 'REGISTRADO') bg-gray-200 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @elseif($invoice->status_payment === 'ABONO') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($invoice->status_payment === 'PAGADO') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($invoice->status_payment === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                    {{ $invoice->status_payment }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invoice->warehouse_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invoice->seller }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->total_sin_impuestos, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->total_con_impuestos, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-white">
                                {{ $invoice->creditNoteId ?? 'Sin NC' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->retentionFuente, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->retentionIca, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->retentionIva, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-white">
                                {{ $invoice->orderNumber ?? 'Sin orden' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($invoice->total_con_impuestos - $invoice->retentionFuente - $invoice->retentionIca - $invoice->retentionIva, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                <!-- Menú de tres puntos con Alpine.js -->
                                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                                    <button @click="open = !open"
                                        class="flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition-colors">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>

                                    <!-- Menú desplegable -->
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
                                        <div class="py-1" role="menu" aria-orientation="vertical">

                                            {{-- Botón de Emitir - Solo para facturas SIN EMITIR --}}
                                            @if($invoice->status === 'SIN EMITIR')
                                                <button wire:click="emitirFactura({{ $invoice->id }})"
                                                    wire:confirm="¿Está seguro de emitir esta factura?"
                                                    class="w-full text-left px-4 py-2 text-sm text-orange-700 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Emitir Factura
                                                </button>
                                            @endif

                                            {{-- Botón de Pagar oculto --}}

                                            {{-- Botón de Imprimir - Solo para facturas EMITIDAS --}}
                                            @if($invoice->status === 'FACTURADO')
                                                <button wire:click="printInvoice({{ $invoice->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                    </svg>
                                                    Imprimir Factura
                                                </button>
                                            @endif

                                            {{-- Separador si hay opciones --}}
                                            @if($invoice->status === 'SIN EMITIR' || ($invoice->status === 'FACTURADO' && $invoice->status_payment !== 'PAGADO' && $invoice->status_payment !== 'ANULADO') || $invoice->status === 'FACTURADO')
                                                <div class="border-t border-gray-100 dark:border-gray-600 my-1"></div>
                                            @endif

                                            {{-- Nota Crédito - Solo para facturas FACTURADAS, sin nota crédito y NO pagadas --}}
                                            @if($invoice->status === 'FACTURADO' && !$invoice->creditNote && $invoice->status_payment !== 'PAGADO')
                                                <button wire:click="openCreditNoteModal({{ $invoice->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h16M15 10l4 4m0 0l-4 4"/>
                                                    </svg>
                                                    Crear Nota Crédito
                                                </button>
                                            @endif

                                            {{-- Agregar Observación --}}
                                            <button wire:click="openJustificacionModal({{ $invoice->id }})"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Agregar observación
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando {{ $invoices->firstItem() }} a {{ $invoices->lastItem() }} de {{ $invoices->total() }} resultados
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         MODAL: CREAR NOTA CRÉDITO
    ══════════════════════════════════════════════════════ --}}
    @if($showCreditNoteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data
         x-on:keydown.escape.window="$wire.closeCreditNoteModal()">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50" wire:click="closeCreditNoteModal"></div>

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Header --}}
            <div class="shrink-0 flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Crear Nota Crédito</h2>
                <button wire:click="closeCreditNoteModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body (scrollable) --}}
            <div class="overflow-y-auto flex-1 px-6 py-4 space-y-5">

                {{-- Resumen de Factura --}}
                <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-4 text-sm">
                    <p class="font-semibold text-gray-700 dark:text-slate-300 mb-3 uppercase tracking-wide text-xs">Resumen de Factura</p>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-1">
                        <div><span class="text-gray-500 dark:text-slate-400">Factura No.:</span>
                            <span class="font-semibold ml-1 text-gray-900 dark:text-white">{{ $creditNoteInvoice['invoiceNumber'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Total Factura:</span>
                            <span class="font-semibold ml-1 text-gray-900 dark:text-white">$ {{ number_format($creditNoteInvoice['total'] ?? 0, 0, ',', '.') }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Cliente:</span>
                            <span class="ml-1 text-gray-900 dark:text-white">{{ $creditNoteInvoice['client_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Estado Pago:</span>
                            <span class="ml-1 font-medium text-blue-600 dark:text-blue-400">{{ $creditNoteInvoice['status_payment'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Estado:</span>
                            <span class="ml-1 font-medium text-green-600 dark:text-green-400">{{ $creditNoteInvoice['status'] ?? '—' }}</span></div>
                    </div>
                </div>

                {{-- Motivo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                        Motivo de la Nota Crédito <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="creditNoteReason"
                        class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="VOID_ELECTRONIC_INVOICE">Anulación de factura electrónica</option>
                        <option value="REDUCTION_DISCOUNT_PARTIAL_TOTAL">Rebaja o descuento parcial o total</option>
                        <option value="PARTIALL_DEVOLUTION">Devolución de parte de los bienes</option>
                        <option value="PRICE_ADJUSTMENT">Ajuste de precio</option>
                        <option value="OTHER">Otros</option>
                    </select>
                </div>

                {{-- Observaciones --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Observaciones</label>
                    <textarea wire:model="creditNoteObs" rows="3" maxlength="250"
                        placeholder="Describa el motivo de la nota crédito"
                        class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    <p class="text-xs text-gray-400 mt-0.5">{{ strlen($creditNoteObs) }}/250 caracteres</p>
                </div>

                {{-- ── ANULACIÓN: sin selección de ítems ── --}}
                @if($creditNoteReason === 'VOID_ELECTRONIC_INVOICE')
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4 text-sm">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-amber-800 dark:text-amber-300">Anulación total de factura</p>
                                <p class="text-amber-700 dark:text-amber-400 mt-1">Se acreditará el total completo de la factura. No es posible hacer anulación parcial con este tipo.</p>
                                <p class="mt-2 font-bold text-amber-800 dark:text-amber-200">
                                    Valor a acreditar: $ {{ number_format($creditNoteTotal, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                {{-- ── REBAJA / AJUSTE DE PRECIO: precio unitario editable ── --}}
                @elseif(in_array($creditNoteReason, ['REDUCTION_DISCOUNT_PARTIAL_TOTAL', 'PRICE_ADJUSTMENT']))
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wide text-xs mb-2">Ítems de la Factura</p>
                        <div class="border border-gray-200 dark:border-slate-600 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-slate-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Código</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Producto</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Cant.</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Precio Unit. (Nuevo)</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">%</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Subtotal</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                    @forelse($creditNoteItems as $index => $item)
                                    <tr class="bg-white dark:bg-slate-800">
                                        <td class="px-3 py-2 text-gray-600 dark:text-slate-400 font-mono text-xs">{{ $item['code'] }}</td>
                                        <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">{{ $item['name'] }}</td>
                                        <td class="px-3 py-2 text-center text-gray-700 dark:text-slate-300">{{ $item['quantity'] }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number"
                                                wire:model.blur="creditNoteItems.{{ $index }}.unit_price"
                                                min="0" step="0.01"
                                                class="w-32 text-right border border-indigo-300 dark:border-indigo-600 rounded px-2 py-1 text-xs bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600 dark:text-slate-400">{{ $item['tax'] }}%</td>
                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-slate-300">
                                            ${{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                                            ${{ number_format($item['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-400 dark:text-slate-500">
                                            No se encontraron ítems para esta factura.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end mt-2">
                            <span class="text-sm text-gray-600 dark:text-slate-400">Total Nota Crédito:
                                <span class="font-bold text-gray-900 dark:text-white ml-1">$ {{ number_format($creditNoteTotal, 0, ',', '.') }}</span>
                            </span>
                        </div>
                    </div>

                {{-- ── DEVOLUCIÓN PARCIAL / OTROS: selección de ítems + cantidad ── --}}
                @else
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-700 dark:text-slate-300 uppercase tracking-wide text-xs">Ítems de la Factura</p>
                            <div class="flex gap-2">
                                <button wire:click="selectAllCreditNoteItems"
                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 hover:bg-indigo-200 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Seleccionar Todos
                                </button>
                                <button wire:click="deselectAllCreditNoteItems"
                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-400 hover:bg-gray-200 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Deseleccionar Todos
                                </button>
                            </div>
                        </div>

                        <div class="border border-gray-200 dark:border-slate-600 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-slate-700">
                                    <tr>
                                        <th class="w-8 px-3 py-2"></th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Código</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Producto</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Cantidad</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Precio Unit.</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">%</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Subtotal</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                    @forelse($creditNoteItems as $index => $item)
                                    <tr class="{{ $item['selected'] ? 'bg-white dark:bg-slate-800' : 'bg-gray-50 dark:bg-slate-800/50 opacity-60' }}">
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox"
                                                wire:click="toggleCreditNoteItem({{ $index }})"
                                                {{ $item['selected'] ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                        </td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-slate-400 font-mono text-xs">{{ $item['code'] }}</td>
                                        <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">{{ $item['name'] }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number"
                                                wire:model.blur="creditNoteItems.{{ $index }}.quantity"
                                                min="0.01" max="{{ $item['max_qty'] }}" step="0.01"
                                                class="w-20 text-center border border-gray-300 dark:border-slate-600 rounded px-2 py-1 text-xs bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-slate-300">
                                            ${{ number_format($item['unit_price'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-600 dark:text-slate-400">{{ $item['tax'] }}%</td>
                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-slate-300">
                                            ${{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">
                                            ${{ number_format($item['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-400 dark:text-slate-500">
                                            No se encontraron ítems para esta factura.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-2">
                            <span class="text-sm text-gray-600 dark:text-slate-400">Total Seleccionado:
                                <span class="font-bold text-gray-900 dark:text-white ml-1">$ {{ number_format($creditNoteTotal, 0, ',', '.') }}</span>
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Valor de la Nota Crédito --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Valor de la Nota Crédito</label>
                    <input type="text" readonly
                        value="{{ number_format($creditNoteTotal, 2, ',', '.') }}"
                        class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-slate-700/50 text-gray-900 dark:text-white font-semibold focus:outline-none cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">El valor se calcula automáticamente basado en los ítems seleccionados</p>
                </div>

            </div>{{-- /body --}}

            {{-- Footer --}}
            <div class="shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                <button wire:click="closeCreditNoteModal"
                    class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                    Cancelar
                </button>
                <button wire:click="submitCreditNote"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors disabled:opacity-60">
                    <span wire:loading.remove wire:target="submitCreditNote">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h16"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="submitCreditNote">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </span>
                    Crear Nota Crédito
                </button>
            </div>

        </div>{{-- /panel --}}
    </div>{{-- /modal --}}
    @endif

    {{-- ══════════════════════════════════════════════════════
         MODAL: REGISTRAR COMPROBANTE DE PAGO
    ══════════════════════════════════════════════════════ --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data
         x-on:keydown.escape.window="$wire.closePaymentModal()">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closePaymentModal"></div>

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden transition-all transform scale-100">

            {{-- Header --}}
            <div class="shrink-0 flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Confirmar Pago
                </h2>
                <button wire:click="closePaymentModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-4">
                
                {{-- Payment Method Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                        Forma de Pago <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="paymentMethodId"
                        class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($paymentMethodsList as $method)
                            <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                        @endforeach
                    </select>
                    @error('paymentMethodId') 
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Remission Proof Payment Option --}}
                @if($remissionProofPath)
                    <div class="bg-slate-50 dark:bg-slate-700/30 p-4 rounded-lg border border-gray-200 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Soporte de la Remisión</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="useRemissionProof" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ml-2 text-xs font-bold text-gray-700 dark:text-slate-300">Vincular</span>
                            </label>
                        </div>
                        
                        @if($this->isRemissionProofImage())
                            <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-slate-600 bg-gray-100 dark:bg-slate-900 max-h-40 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $remissionProofPath) }}" class="object-contain max-h-36 w-full">
                            </div>
                        @else
                            <div class="flex items-center gap-2 p-2 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 rounded-lg">
                                <svg class="w-8 h-8 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-700 dark:text-slate-300 truncate">Soporte adjunto (PDF/Documento)</p>
                                    <a href="{{ asset('storage/' . $remissionProofPath) }}" target="_blank" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Ver documento adjunto</a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- File Upload --}}
                @if(!$useRemissionProof)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">
                        Comprobante de Pago (Imagen) <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-slate-600 border-dashed rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors cursor-pointer relative">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-slate-400">
                                <label for="payment-proof-upload" class="relative cursor-pointer bg-transparent rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none">
                                    <span>Subir un archivo</span>
                                    <input id="payment-proof-upload" name="payment-proof-upload" type="file" wire:model="paymentProofFile" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">o arrastrar y soltar</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-500">PNG, JPG, GIF hasta 2MB</p>
                        </div>
                    </div>
                    @error('paymentProofFile') 
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                    @enderror

                    {{-- Image Preview --}}
                    @if ($paymentProofFile)
                        <div class="mt-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1">Vista previa:</p>
                            <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-slate-700 bg-gray-50 max-h-48 flex items-center justify-center">
                                <img src="{{ $paymentProofFile->temporaryUrl() }}" class="object-contain max-h-44 w-full">
                            </div>
                        </div>
                    @endif
                </div>
                @endif

                {{-- Loading indicator --}}
                <div wire:loading wire:target="paymentProofFile" class="text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando imagen...
                </div>

            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
                <button wire:click="closePaymentModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                    Cancelar
                </button>
                <button wire:click="submitPayment"
                    wire:loading.attr="disabled"
                    wire:target="submitPayment, paymentProofFile"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors disabled:opacity-60">
                    <span wire:loading.remove wire:target="submitPayment">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="submitPayment">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </span>
                    Confirmar Pago
                </button>
            </div>

        </div>{{-- /panel --}}
    </div>{{-- /modal --}}
    @endif

    <!-- Modal de Justificación de Facturas -->
    <template x-teleport="body">
        <div x-show="$wire.showJustificacionModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" @click="$wire.showJustificacionModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 text-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                            Agregar observación
                        </h3>
                    </div>

                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 text-center uppercase tracking-wide">
                            Observación de Facturación
                        </label>
                        <textarea wire:model="justificacionText" 
                                  rows="4" 
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Escribe aquí tu comentario..."></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 flex justify-center space-x-3">
                        <button wire:click="saveJustificacion" 
                                class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-blue-600/20">
                            Guardar
                        </button>
                        <button @click="$wire.showJustificacionModal = false" 
                                class="px-8 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-slate-600/20">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>