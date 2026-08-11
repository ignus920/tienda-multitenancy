<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-full mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Justificaciones de Cantidad</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Informe y auditoría mensual de las causas de cambio de cantidades a precio x caja</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-colors uppercase">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z" />
                    </svg>
                    Exportar Excel
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-750 text-gray-900 dark:text-white shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-750 text-gray-900 dark:text-white shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Comercial / Vendedor</label>
                    <select wire:model.live="selectedVendedor" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-750 text-gray-900 dark:text-white shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos los vendedores</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Buscador</label>
                    <input type="text" wire:model.live="search" placeholder="Buscar cotización, producto, texto..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-750 text-gray-900 dark:text-white shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300 border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3.5">Fecha</th>
                            <th class="px-6 py-3.5">Cotización</th>
                            <th class="px-6 py-3.5">Vendedor</th>
                            <th class="px-6 py-3.5">Producto</th>
                            <th class="px-6 py-3.5 text-center">Cant Solicitada</th>
                            <th class="px-6 py-3.5 text-center">Cant x Caja</th>
                            <th class="px-6 py-3.5">Justificación del Cambio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 dark:divide-gray-700">
                        @forelse($reportData as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-indigo-600 dark:text-indigo-400 text-xs">
                                    #{{ $row->cotizacion }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-white">
                                    {{ $row->vendedor }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white text-xs">{{ $row->codigo }}</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-[10px] truncate max-w-xs" title="{{ $row->producto }}">{{ $row->producto }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-semibold text-gray-950 dark:text-white text-xs">
                                    {{ number_format($row->cantidad) }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-500 dark:text-slate-400">
                                    {{ number_format($row->unidades_x_caja ?: 0) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-dashed border-amber-200 dark:border-amber-900 rounded-lg text-amber-900 dark:text-amber-300 text-xs italic font-medium max-w-xl leading-relaxed">
                                        {{ $row->justificacion }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="text-sm font-bold text-gray-950 dark:text-white">Ningún registro encontrado</div>
                                    <p class="text-xs text-gray-500 mt-1">No hay justificaciones registradas en las fechas seleccionadas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($reportData->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                    {{ $reportData->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
