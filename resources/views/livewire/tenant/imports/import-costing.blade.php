<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-full mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Costeo de Importaciones</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Prorrateo automatizado de gastos de importación sobre el precio EXW</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Selector de Embarque -->
                <div class="w-72">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Seleccionar Embarque (Shipment)</label>
                    <select wire:model.live="selectedShippmentId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Seleccionar embarque --</option>
                        @foreach($shippments as $shipp)
                            <option value="{{ $shipp['id'] }}">
                                {{ $shipp['way'] }} - {{ $shipp['operation_number'] }} (#{{ $shipp['consecutive'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(!empty($items))
                    <button wire:click="processCosting" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-colors mt-5">
                        Procesar y Guardar Costeo
                    </button>
                @endif
            </div>
        </div>

        @if(!empty($items))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Panel de Gastos e Inputs (Izquierda) -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3 uppercase tracking-wide">Gastos del Embarque</h3>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">TRM Costeo *</label>
                    <input type="number" wire:model.live="trm_costeo" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Flete Internacional (USD) *</label>
                    <input type="number" wire:model.live="flete_internacional_usd" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Seguro de Carga (USD) *</label>
                    <input type="number" wire:model.live="seguro_usd" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Arancel Aduanero (COP) *</label>
                    <input type="number" wire:model.live="arancel_cop" step="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Otros Gastos Locales (COP)</label>
                    <input type="number" wire:model.live="otros_gastos_cop" step="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                </div>
            </div>

            <!-- Tabla de Prorrateo y Resultados (Derecha) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-wide">Desglose de Ítems Prorrateados</h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                        {{ count($items) }} Ítems
                    </span>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300 border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3">Código / Producto</th>
                                <th class="px-4 py-3 text-center">Cant</th>
                                <th class="px-4 py-3 text-center">EXW USD</th>
                                <th class="px-4 py-3 text-right">Flete (USD)</th>
                                <th class="px-4 py-3 text-right">Seguro (USD)</th>
                                <th class="px-4 py-3 text-right">Arancel (COP)</th>
                                <th class="px-4 py-3 text-right">Costo Final Unitario (COP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 dark:divide-gray-700">
                            @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-semibold text-gray-900 dark:text-white text-xs">{{ $item['internal_code'] }}</div>
                                        <div class="text-slate-500 dark:text-slate-400 text-[10px] truncate max-w-[200px]" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-mono">{{ number_format($item['qty']) }}</td>
                                    <td class="px-4 py-3.5 text-center font-mono text-xs">${{ number_format($item['exw_usd'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-xs">${{ number_format($item['prorated_flete_usd'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-xs">${{ number_format($item['prorated_seguro_usd'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-xs">${{ number_format($item['prorated_arancel_cop'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-indigo-600 dark:text-indigo-400 font-mono">
                                        ${{ number_format($item['costo_unitario_cop'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totales Consolidados -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase">Unidades Totales</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white font-mono">{{ number_format($totals['qty']) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase">FOB Total (USD)</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white font-mono">${{ number_format($totals['exw_total_usd'], 2) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase">FOB Equivalente (COP)</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white font-mono">${{ number_format($totals['exw_total_cop'], 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase">Costo Total Final (COP)</span>
                        <span class="text-xl font-extrabold text-green-600 dark:text-green-400 font-mono">${{ number_format($totals['costo_final_total_cop'], 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

        </div>
        @else
            <!-- Estado Vacío -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2-2 4 4m0-7v3m-3-3h3m-9 9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ningún Embarque Seleccionado</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-sm mx-auto">Selecciona un embarque en la parte superior derecha para listar sus productos y prorratear los costos.</p>
            </div>
        @endif

    </div>
</div>
