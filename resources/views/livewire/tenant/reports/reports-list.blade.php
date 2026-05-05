<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        <!-- Sidebar: Filtros y Botones de Reporte -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card de Filtros -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 overflow-hidden transition-all">
                <div class="bg-indigo-900 -mx-5 -mt-5 px-5 py-3 mb-5 border-b border-indigo-800">
                    <h3 class="text-white font-bold text-xs uppercase tracking-widest">Filtros de consulta</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">desde:</label>
                            <input type="date" wire:model.live="dateFrom"
                                class="block w-full px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">hasta:</label>
                            <input type="date" wire:model.live="dateTo"
                                class="block w-full px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                        </div>
                    </div>

                    <button wire:click="clearFilters" 
                        class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-red-500/20 uppercase tracking-wider">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Borrar consulta
                    </button>
                </div>
            </div>

            <!-- Card de Botones de Reporte -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 transition-all">
                <div class="space-y-3">
                    <button wire:click="loadVentasVendedor" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'ventas_vendedor' ? 'bg-indigo-700 ring-2 ring-indigo-500' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-sm font-semibold transition-all shadow-sm flex items-center group">
                        <span class="flex-1">Informe de ventas por vendedor</span>
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <button wire:click="loadCotizacionesProducto" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'cotizaciones_producto' ? 'bg-indigo-700 ring-2 ring-indigo-500' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-sm font-semibold transition-all shadow-sm flex items-center group">
                        <span class="flex-1">Informe cotización x producto</span>
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <button wire:click="loadProductosCliente" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'productos_cliente' ? 'bg-indigo-700 ring-2 ring-indigo-500' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-sm font-semibold transition-all shadow-sm flex items-center group">
                        <span class="flex-1">Informe productos x cliente</span>
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <button wire:click="loadPedidosEstado" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'pedidos_estado' ? 'bg-indigo-700 ring-2 ring-indigo-500' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-sm font-semibold transition-all shadow-sm flex items-center group">
                        <span class="flex-1">Informe Pedido x estado</span>
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Área Principal de Datos -->
        <div class="lg:col-span-4 min-w-0">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden transition-all min-h-[600px]">
                <!-- Header del Área de Datos -->
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-white font-bold text-sm uppercase tracking-widest">{{ $reportTitle ?: 'Seleccione un informe' }}</h2>
                    @if($activeReport)
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">{{ count($reportData) }} registros encontrados</span>
                        </div>
                    @endif
                </div>

                @if($activeReport == 'ventas_vendedor')
                    <div class="px-6 py-4 bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700">
                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-2">Vendedores:</label>
                        <select wire:model.live="selectedVendedor" 
                            class="w-full md:w-80 px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                            <option value="">Seleccione una opcion</option>
                            @foreach($vendedores as $vendedor)
                                <option value="{{ $vendedor['id'] }}">{{ $vendedor['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Tabla de Resultados -->
                <div class="overflow-x-auto">
                    @if(!$activeReport)
                        <div class="flex flex-col items-center justify-center py-20 opacity-30 select-none text-gray-500">
                            <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-bold">Sin informe activo</p>
                            <p class="text-sm">Seleccione un reporte del menú lateral para comenzar</p>
                        </div>
                    @elseif(empty($reportData))
                        <div class="flex flex-col items-center justify-center py-20 opacity-30 text-gray-500">
                            <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-lg font-bold">No se encontraron datos</p>
                            <p class="text-sm">Pruebe ajustando el rango de fechas</p>
                        </div>
                    @else
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                                    @if($activeReport == 'ventas_vendedor')
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Cot</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Remisión</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Vendedor</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Cant</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                                    @elseif($activeReport == 'cotizaciones_producto')
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Código</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Cotizaciones</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Cant. Total</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Valor Total</th>
                                    @elseif($activeReport == 'productos_cliente')
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Código</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Cant</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                                    @elseif($activeReport == 'pedidos_estado')
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Consecutivo</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-center">Estado</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach($reportData as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        @if($activeReport == 'ventas_vendedor')
                                            <td class="px-4 py-3 text-sm font-medium text-indigo-600">{{ $row->cot }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">{{ $row->remission }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $row->vendedor }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="font-bold text-gray-900 dark:text-gray-200">{{ $row->producto }}</div>
                                                <div class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $row->codigo }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">{{ number_format($row->cantidad) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-green-600">${{ number_format($row->total, 2) }}</td>
                                        @elseif($activeReport == 'cotizaciones_producto')
                                            <td class="px-4 py-3 text-xs font-mono text-gray-600 dark:text-gray-400">{{ $row->codigo }}</td>
                                            <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-200">{{ $row->producto }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-200">{{ $row->total_cotizaciones }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono text-gray-900 dark:text-gray-200">{{ number_format($row->cantidad_total) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-green-600">${{ number_format($row->valor_total, 2) }}</td>
                                        @elseif($activeReport == 'productos_cliente')
                                            <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{ $row->cliente }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">{{ $row->producto }}</td>
                                            <td class="px-4 py-3 text-xs font-mono text-gray-600 dark:text-gray-400">{{ $row->codigo }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-200">{{ number_format($row->cantidad) }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-green-600">${{ number_format($row->total, 2) }}</td>
                                        @elseif($activeReport == 'pedidos_estado')
                                            <td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-200">{{ $row->consecutive }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $row->cliente }}</td>
                                            <td class="px-4 py-3 text-xs text-center">
                                                <span class="px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-tighter">
                                                    {{ $row->estado_texto }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
