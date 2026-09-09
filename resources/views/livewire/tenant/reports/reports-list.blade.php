<div class="p-6 bg-gray-50 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        <!-- Sidebar: Filtros y Botones de Reporte -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card de Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 overflow-hidden">
                <div class="bg-gray-50 -mx-5 -mt-5 px-5 py-3 mb-5 border-b border-gray-200">
                    <h3 class="text-gray-700 font-bold text-xs uppercase tracking-widest">Filtros de consulta</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">desde:</label>
                            <input type="date" wire:model.live="dateFrom"
                                class="block w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">hasta:</label>
                            <input type="date" wire:model.live="dateTo"
                                class="block w-full px-3 py-2 border border-gray-200 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="space-y-3">
                    <button wire:click="loadVentasVendedor" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'ventas_vendedor' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-semibold transition-all flex items-center group">
                        <span class="flex-1">Informe detallado por vendedor</span>
                    </button>
                    
                    <button wire:click="loadVentasVendedorResumido" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'ventas_vendedor_resumido' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-semibold transition-all flex items-center group">
                        <span class="flex-1">Informe resumido por Vendedor</span>
                    </button>

                    <button wire:click="loadCotizacionesProducto" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'cotizaciones_producto' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-semibold transition-all flex items-center group">
                        <span class="flex-1">Informe cotización x producto</span>
                    </button>

                    <button wire:click="loadProductosCliente" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'productos_cliente' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-semibold transition-all flex items-center group">
                        <span class="flex-1">Informe productos x cliente</span>
                    </button>

                    <button wire:click="loadPedidosEstado" 
                        class="w-full text-left px-4 py-3 {{ $activeReport == 'pedidos_estado' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-semibold transition-all flex items-center group">
                        <span class="flex-1">Informe Pedido x estado</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Área Principal de Datos -->
        <div class="lg:col-span-4 min-w-0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden min-h-[600px]">
                <!-- Header del Área de Datos -->
                <div class="bg-white px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-gray-800 font-bold text-sm uppercase tracking-widest">{{ $reportTitle ?: 'Seleccione un informe' }}</h2>
                    @if($activeReport)
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 font-medium">@if(isset($reportData) && method_exists($reportData, 'total')) {{ $reportData->total() }} @else {{ count($reportData ?? []) }} @endif registros encontrados</span>
                        </div>
                    @endif
                </div>

                @if($activeReport)
                    <!-- Toolbar -->
                    <div class="p-6 border-b border-gray-200 bg-white">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Búsqueda -->
                            <div class="flex-1 max-w-md">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.300ms="search"
                                           type="text"
                                           placeholder="Buscar en este reporte..."
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            </div>

                            <!-- Controles -->
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Registros por página -->
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mostrar:</label>
                                    <select wire:model.live="perPage"
                                            class="border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 py-1">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>

                                <!-- Botones de exportar -->
                                <div class="flex items-center gap-2 border-l pl-3 border-gray-200">
                                    <x-export-buttons />
                                    
                                    <button onclick="window.print()"
                                            title="Imprimir reporte"
                                            class="inline-flex items-center justify-center p-2 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($activeReport == 'ventas_vendedor' || $activeReport == 'ventas_vendedor_resumido')
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <div class="max-w-xs">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filtrar Vendedor:</label>
                            <select wire:model.live="selectedVendedor" 
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm transition-all">
                                <option value="">Todos los vendedores</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                @if($activeReport == 'pedidos_estado')
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <div class="max-w-xs">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Estado:</label>
                            <select wire:model.live="selectedEstado" 
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm transition-all">
                                <option value="">Seleccione una opcion</option>
                                <option value="1">Alistamiento</option>
                                <option value="2">Empacado</option>
                                <option value="3">En ruta</option>
                                <option value="4">Entregado</option>
                                <option value="5">Imposibilidad</option>
                                <option value="6">Anulado</option>
                                <option value="7">Cartera</option>
                            </select>
                        </div>
                    </div>
                @endif

                <!-- Tabla de Resultados -->
                <div class="overflow-x-auto">
                    @if(!$activeReport)
                        <div class="flex flex-col items-center justify-center py-20 opacity-30 select-none text-gray-400">
                            <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-bold">Sin informe activo</p>
                            <p class="text-sm">Seleccione un reporte del menú lateral para comenzar</p>
                        </div>
                    @elseif(empty($reportData))
                        <div class="flex flex-col items-center justify-center py-20 opacity-30 text-gray-400">
                            <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-lg font-bold">No se encontraron datos</p>
                            <p class="text-sm">Pruebe ajustando el rango de fechas o filtros</p>
                        </div>
                    @else
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    @if($activeReport == 'ventas_vendedor')
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Vendedor</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cotización</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Remisión</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Factura</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Clasif.</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pago</th>
                                    @elseif($activeReport == 'ventas_vendedor_resumido')
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Vendedor</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Remisión</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Factura</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    @elseif($activeReport == 'cotizaciones_producto')
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cotiz.</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Ped.</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">% Efec.</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cant. Cotizada</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cant. Pedida</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Detalle</th>
                                    @elseif($activeReport == 'productos_cliente')
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cotizaciones</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pedidos</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">V cotizados</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">V pedidos</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">% Pedidos</th>
                                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Selecciona</th>
                                    @elseif($activeReport == 'pedidos_estado')
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">#OP</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Entrega</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Factura</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Reimpresion</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Entregado</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cancelar</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pedido</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($reportData as $row)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        @if($activeReport == 'ventas_vendedor')
                                            <td class="px-4 py-3 text-[11px] text-gray-700 font-medium">{{ $row->vendedor }}</td>
                                            <td class="px-4 py-3 text-[11px] font-bold text-indigo-600">{{ $row->cot }}</td>
                                            <td class="px-4 py-3 text-[10px]">
                                                <span class="px-2 py-0.5 rounded {{ str_contains(strtoupper($row->estado), 'ENTREGADO') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }} font-bold">
                                                    {{ $row->estado }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-gray-600 font-mono">{{ $row->remission }}</td>
                                            <td class="px-4 py-3 text-[11px] text-gray-900 font-bold">{{ $row->factura ?: '---' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($row->fecha)->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3 text-[10px] text-gray-600 uppercase">{{ $row->descripcion }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right font-mono text-gray-500">${{ number_format($row->subtotal, 2) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right font-bold text-gray-900">${{ number_format($row->total, 2) }}</td>
                                            <td class="px-4 py-3 text-[10px] text-gray-500 uppercase">{{ $row->clasificacion ?: '---' }}</td>
                                            <td class="px-4 py-3 text-[10px] text-gray-500">{{ $row->forma_pago ?: '---' }}</td>
                                        @elseif($activeReport == 'ventas_vendedor_resumido')
                                            <td class="px-4 py-3 text-[11px] text-gray-700 font-medium">{{ $row->vendedor }}</td>
                                            <td class="px-4 py-3 text-[10px]">
                                                <span class="px-2 py-0.5 rounded {{ str_contains(strtoupper($row->estado_texto), 'ENTREGADO') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }} font-bold">
                                                    {{ $row->estado_texto }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-gray-600 font-mono">{{ $row->remission }}</td>
                                            <td class="px-4 py-3 text-[11px] text-gray-900 font-bold">{{ $row->factura ?: '---' }}</td>
                                            <td class="px-4 py-3 text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($row->fecha)->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3 text-[11px] text-right font-mono font-bold text-gray-900">${{ number_format($row->subtotal, 2) }}</td>
                                        @elseif($activeReport == 'cotizaciones_producto')
                                            <td class="px-4 py-3 text-[11px] text-gray-700">{{ $row->codigo }} - {{ $row->producto }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center font-bold text-gray-900">{{ $row->cotizaciones }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-700">{{ $row->pedidos }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-500 font-bold">{{ $row->porcentaje_pedidos }}%</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-700">{{ number_format($row->unidades, 2) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center font-bold text-indigo-600">{{ number_format($row->unidades_pedidas, 2) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" wire:model.live="selectedItemCodes" value="{{ $row->codigo }}" 
                                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            </td>
                                        @elseif($activeReport == 'productos_cliente')
                                            <td class="px-4 py-3 text-[11px] font-bold text-indigo-600">{{ $row->nombre }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-700">{{ $row->cotizaciones }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-700">{{ $row->pedidos }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-500 font-mono">${{ number_format($row->v_cotizados, 2) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-900 font-bold font-mono">${{ number_format($row->v_pedidos, 2) }}</td>
                                            <td class="px-4 py-3 text-[11px] text-center text-gray-500 font-bold">{{ $row->porcentaje_pedidos }}%</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" wire:model.live="selectedCustomerIds" value="{{ $row->id }}" 
                                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            </td>
                                        @elseif($activeReport == 'pedidos_estado')
                                            <td class="px-4 py-3 text-[11px] text-gray-700 font-bold">{{ $row->consecutive }}</td>
                                            <td class="px-4 py-3 text-[10px] text-gray-500">
                                                {{ \Carbon\Carbon::parse($row->fecha)->format('Y-m-d') }}<br>
                                                {{ \Carbon\Carbon::parse($row->fecha)->format('H:i:s') }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px]">
                                                <div class="text-gray-900 font-bold uppercase">{{ $row->cliente }}</div>
                                                <div class="text-indigo-600 font-medium text-[10px] uppercase">ERP{{ $row->erp_quote }}</div>
                                                <div class="text-gray-500 text-[9px] mt-1 italic">Tipo de entrega: {{ $row->entrega ?: 'No especificado' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-[10px]">
                                                <span class="font-bold text-gray-700">{{ $row->estado_texto }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-[10px] text-gray-600 max-w-xs truncate">{{ $row->entrega }}</td>
                                            <td class="px-4 py-3 text-[11px] text-gray-900 font-bold">{{ $row->factura ?: '---' }}</td>
                                            <td class="px-4 py-3 text-center">---</td>
                                            <td class="px-4 py-3 text-center">---</td>
                                            <td class="px-4 py-3 text-center">---</td>
                                            <td class="px-4 py-3 text-[10px] text-right text-gray-500">
                                                {{ \Carbon\Carbon::parse($row->fecha)->format('d-m-y') }} | {{ $row->creator }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Paginación -->
                @if($activeReport && !empty($reportData) && method_exists($reportData, 'links'))
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $reportData->links() }}
                    </div>
                @endif

                <!-- Tabla de Detalles (Seleccionados) -->
                @if($activeReport == 'cotizaciones_producto' && !empty($itemDetails))
                    <div class="mt-8 border-t-2 border-indigo-100">
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                            <h3 class="text-indigo-900 font-bold text-[11px] uppercase tracking-wider">Desglose de productos seleccionados</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Código</th>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Cotización</th>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Cliente</th>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Asesor</th>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">NIT/ID</th>
                                        <th class="px-4 py-2 text-right text-[9px] font-bold text-gray-500 uppercase">Cant.</th>
                                        <th class="px-4 py-2 text-center text-[9px] font-bold text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @php $currentGroup = null; @endphp
                                    @foreach($itemDetails as $detail)
                                        @if($currentGroup !== $detail->codigo)
                                            <tr class="bg-indigo-50/50">
                                                <td colspan="8" class="px-4 py-1.5 text-[10px] font-bold text-indigo-700 border-y border-indigo-100">
                                                    PRODUCTO: {{ $detail->codigo }} - {{ $detail->producto_nombre }}
                                                </td>
                                            </tr>
                                            @php $currentGroup = $detail->codigo; @endphp
                                        @endif
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-2 text-[10px] text-gray-500">{{ $detail->codigo }}</td>
                                            <td class="px-4 py-2 text-[10px] text-gray-500">{{ $detail->fecha }}</td>
                                            <td class="px-4 py-2 text-[10px] font-bold text-indigo-600">{{ $detail->cotizacion }}</td>
                                            <td class="px-4 py-2 text-[10px] text-gray-800 uppercase">{{ $detail->cliente_nombre }}</td>
                                            <td class="px-4 py-2 text-[10px] text-gray-600">{{ $detail->asesor }}</td>
                                            <td class="px-4 py-2 text-[10px] text-gray-500 font-mono">{{ $detail->identidad ?: '---' }}</td>
                                            <td class="px-4 py-2 text-[10px] text-right font-bold text-gray-900">{{ number_format($detail->cantidad, 2) }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-bold text-[9px] uppercase border border-gray-200">
                                                    {{ $detail->estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Tabla de Detalles Cliente (Seleccionados) -->
                @if($activeReport == 'productos_cliente' && !empty($customerDetails))
                    <div class="mt-8 border-t-2 border-indigo-100">
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                            <h3 class="text-indigo-900 font-bold text-[11px] uppercase tracking-wider">Desglose de productos por cliente seleccionado</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-[9px] font-bold text-gray-500 uppercase">Descripción</th>
                                        <th class="px-4 py-2 text-center text-[9px] font-bold text-gray-500 uppercase">Cotización</th>
                                        <th class="px-4 py-2 text-center text-[9px] font-bold text-gray-500 uppercase">Pedidos</th>
                                        <th class="px-4 py-2 text-center text-[9px] font-bold text-gray-500 uppercase">Cotizados</th>
                                        <th class="px-4 py-2 text-center text-[9px] font-bold text-gray-500 uppercase">Pedido</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @php $currentCustomer = null; @endphp
                                    @foreach($customerDetails as $detail)
                                        @if($currentCustomer !== $detail->cliente_id)
                                            <tr class="bg-indigo-50/50">
                                                <td colspan="5" class="px-4 py-1.5 text-[10px] font-bold text-indigo-700 border-y border-indigo-100 uppercase">
                                                    CLIENTE: {{ $detail->cliente_nombre }}
                                                </td>
                                            </tr>
                                            @php $currentCustomer = $detail->cliente_id; @endphp
                                        @endif
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-2 text-[10px] text-gray-700 font-medium">{{ $detail->codigo }} - {{ $detail->descripcion }}</td>
                                            <td class="px-4 py-2 text-[10px] text-center text-gray-600">{{ $detail->cotizacion }}</td>
                                            <td class="px-4 py-2 text-[10px] text-center text-gray-600 font-bold">{{ $detail->pedidos }}</td>
                                            <td class="px-4 py-2 text-[10px] text-center text-gray-500">{{ number_format($detail->cotizados, 2) }}</td>
                                            <td class="px-4 py-2 text-[10px] text-center font-bold text-indigo-600">{{ number_format($detail->pedido, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
