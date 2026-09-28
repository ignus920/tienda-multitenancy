<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-full">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Simulador y Asignador de Precios</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Calcula y actualiza los precios de venta a partir de los costos de importación o mediante ajuste manual.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button wire:click="saveAndSync" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveAndSync">Guardar y Sincronizar</span>
                <span wire:loading wire:target="saveAndSync">Procesando...</span>
            </button>
        </div>
    </div>

    <!-- Filtros de Entrada -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Buscar Importación -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Cargar desde Importación
                </label>
                <select wire:model.live="selectedShipmentId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">-- Seleccionar un Embarque/Importación --</option>
                    @foreach($shipments as $shipment)
                        <option value="{{ $shipment->id }}">Embarque #{{ $shipment->consecutive ?? $shipment->id }} - {{ $shipment->etd }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Al seleccionar, se cargarán todos los productos vinculados.</p>
            </div>

            <!-- Búsqueda Manual -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Agregar Producto Manualmente
                </label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, código o SKU..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                
                @if(!empty($searchResults))
                    <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg rounded-md border border-gray-200 dark:border-gray-600 max-h-60 overflow-y-auto">
                        <ul class="py-1">
                            @foreach($searchResults as $res)
                                <li>
                                    <button wire:click="addItem({{ $res->id }})" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none">
                                        <span class="font-medium">{{ $res->sku }}</span> - {{ $res->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Parámetros Globales Rápidos -->
    @if(count($items) > 0)
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg shadow p-4 mb-6 flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300">Dólar (TRM) Global</label>
            <input type="number" step="0.01" wire:model="globalExchangeRate" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm sm:text-sm py-1">
        </div>
        <div>
            <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300">% Flete Global</label>
            <input type="number" step="0.01" wire:model="globalFreightPercent" class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm sm:text-sm py-1">
        </div>
        <div>
            <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300">Factor Lista</label>
            <input type="number" step="0.01" wire:model="globalFactorList" class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm sm:text-sm py-1">
        </div>
        <div>
            <label class="block text-xs font-semibold text-blue-800 dark:text-blue-300">Factor Min</label>
            <input type="number" step="0.01" wire:model="globalFactorMin" class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm sm:text-sm py-1">
        </div>
        <div>
            <button wire:click="applyGlobalParams" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700">Aplicar a Todos</button>
        </div>
    </div>

    <!-- Tabla Estilo Excel -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th rowspan="2" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-700 w-64 z-10 border-r dark:border-gray-600">Producto</th>
                        
                        <!-- Inputs Amarillos -->
                        <th colspan="3" class="px-3 py-1 text-center text-xs font-medium text-gray-800 bg-yellow-100 border-b border-r border-yellow-200">Variables Base (USD)</th>
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-gray-800 bg-yellow-100 border-b border-r border-yellow-200">Factores (Multiplicador)</th>
                        
                        <!-- Outputs Azules -->
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-blue-800 bg-blue-100 border-b border-r border-blue-200">Cálculos Internos (COP)</th>
                        
                        <!-- Precios ERP -->
                        <th colspan="5" class="px-3 py-1 text-center text-xs font-medium text-green-800 bg-green-100 border-b border-r border-green-200">Precios Finales ERP (COP)</th>
                        
                        <!-- Escalas Web -->
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-purple-800 bg-purple-100 border-b border-r border-purple-200">Escala 1</th>
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-purple-800 bg-purple-100 border-b border-r border-purple-200">Escala 2</th>
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-purple-800 bg-purple-100 border-b border-r border-purple-200">Escala 3</th>
                        <th colspan="2" class="px-3 py-1 text-center text-xs font-medium text-purple-800 bg-purple-100 border-b border-r border-purple-200">Escala 4</th>
                        <th rowspan="2" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-700 w-16">Act</th>
                    </tr>
                    <tr>
                        <th class="px-2 py-1 text-center text-[10px] bg-yellow-50 text-gray-600 w-24">EXW Actual</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-yellow-50 text-gray-600 w-24">Dólar TRM</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-yellow-50 text-gray-600 w-20">% Flete</th>

                        <th class="px-2 py-1 text-center text-[10px] bg-yellow-50 text-gray-600 w-20">Lista</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-yellow-50 text-gray-600 w-20 border-r border-yellow-200">Min</th>

                        <th class="px-2 py-1 text-center text-[10px] bg-blue-50 text-blue-700 w-28">Calc $ Lista</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-blue-50 text-blue-700 w-28 border-r border-blue-200">Calc $ Min</th>

                        <th class="px-2 py-1 text-center text-[10px] bg-green-50 text-green-700 w-28 font-bold" title="Definido por el usuario">$ LISTA (P1)</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-green-50 text-green-700 w-20">% Max Dscto</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-green-50 text-green-700 w-28">$ MÍNIMO (P2)</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-green-50 text-green-700 w-28">$ CRÉDITO (P3)</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-green-50 text-green-700 w-28 border-r border-green-200">$ PÁGINA WEB</th>

                        <!-- Web -->
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-16">Cant</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-24 border-r border-purple-200">% Dscto</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-16">Cant</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-24 border-r border-purple-200">% Dscto</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-16">Cant</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-24 border-r border-purple-200">% Dscto</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-16">Cant</th>
                        <th class="px-2 py-1 text-center text-[10px] bg-purple-50 text-purple-700 w-24 border-r border-purple-200">% Dscto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 text-sm">
                    @foreach($items as $index => $item)
                        @php
                            // Cálculos al vuelo para la vista
                            $exw = floatval($item['exw'] ?: 0);
                            $dolar = floatval($item['exchange_rate'] ?: 0);
                            $freight = floatval($item['freight_percent'] ?: 0) / 100;
                            
                            $factorList = floatval($item['factor_list'] ?: 0);
                            $factorMin = floatval($item['factor_min'] ?: 0);

                            // Formulas
                            $calcLista = (($exw * $dolar * $factorList) + ($exw * $dolar * $freight)) * 1.19;
                            $calcMin = (($exw * $dolar * $factorMin) + ($exw * $dolar * $freight)) * 1.19;

                            $p = floatval($item['manual_price_list'] ?: 0);
                            $maxDscto = floatval($item['max_discount'] ?: 0) / 100;
                            $minimoReal = $p - ($p * $maxDscto);
                            $credito = $p * 1.10;

                            $web = floatval($item['web_price'] ?: 0);
                            $s1 = floatval($item['scale_1_discount'] ?: 0) / 100;
                            $s2 = floatval($item['scale_2_discount'] ?: 0) / 100;
                            $s3 = floatval($item['scale_3_discount'] ?: 0) / 100;
                            $s4 = floatval($item['scale_4_discount'] ?: 0) / 100;

                            $escala1 = $web - ($web * $s1);
                            $escala2 = $web - ($web * $s2);
                            $escala3 = $web - ($web * $s3);
                            $escala4 = $web - ($web * $s4);

                            $minLimit = $minimoReal > 0 ? $minimoReal : $calcMin;
                            $hasError = false;
                            if($web > 0 && ($escala1 < $minLimit || $escala2 < $minLimit || $escala3 < $minLimit || $escala4 < $minLimit)){
                                $hasError = true;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <!-- Producto -->
                            <td class="px-3 py-2 sticky left-0 bg-white dark:bg-gray-800 border-r dark:border-gray-600 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                <div class="font-medium text-gray-900 dark:text-white truncate w-56" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                            </td>

                            <!-- Amarillos Base -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.exw" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded focus:ring-yellow-500 focus:border-yellow-500">
                            </td>
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.exchange_rate" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-yellow-200">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.freight_percent" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded">
                            </td>

                            <!-- Factores -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.factor_list" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-yellow-200">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.factor_min" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded">
                            </td>

                            <!-- Calculados Azules -->
                            <td class="p-1 border-r border-dashed border-gray-200 bg-blue-50/50">
                                <div class="text-right text-xs font-semibold text-blue-700 py-1 px-1">${{ number_format($calcLista, 0, ',', '.') }}</div>
                            </td>
                            <td class="p-1 border-r border-blue-200 bg-blue-50/50">
                                <div class="text-right text-xs font-semibold text-blue-700 py-1 px-1">${{ number_format($calcMin, 0, ',', '.') }}</div>
                            </td>

                            <!-- Precios Oficiales ERP -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.manual_price_list" placeholder="{{ round($calcLista) }}" class="w-full text-right text-xs py-1 px-1 border-green-300 rounded shadow-inner bg-green-50 focus:ring-green-500">
                            </td>
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="0.1" wire:model.live.debounce.500ms="items.{{ $index }}.max_discount" class="w-full text-right text-xs py-1 px-1 bg-yellow-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-dashed border-gray-200 bg-gray-50 dark:bg-gray-700">
                                <div class="text-right text-xs font-semibold text-gray-700 dark:text-gray-200 py-1 px-1">${{ number_format($minimoReal, 0, ',', '.') }}</div>
                            </td>
                            <td class="p-1 border-r border-dashed border-gray-200 bg-gray-50 dark:bg-gray-700">
                                <div class="text-right text-xs font-semibold text-gray-700 dark:text-gray-200 py-1 px-1">${{ number_format($credito, 0, ',', '.') }}</div>
                            </td>
                            <td class="p-1 border-r border-green-200 {{ $hasError ? 'bg-red-100' : '' }}">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.web_price" class="w-full text-right text-xs py-1 px-1 border-green-300 rounded bg-green-50 {{ $hasError ? 'border-red-500 ring-1 ring-red-500' : '' }}">
                            </td>

                            <!-- Escalas Web -->
                            <!-- E1 -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_1_qty" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-purple-200 relative group">
                                <input type="number" step="0.1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_1_discount" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded {{ $escala1 < $minLimit && $web > 0 ? 'bg-red-100 text-red-700' : '' }}">
                                <div class="hidden group-hover:block absolute z-20 bg-gray-800 text-white text-[10px] p-1 rounded -top-6 left-0">
                                    Precio: ${{ number_format($escala1, 0, ',', '.') }}
                                </div>
                            </td>
                            <!-- E2 -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_2_qty" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-purple-200 relative group">
                                <input type="number" step="0.1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_2_discount" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded {{ $escala2 < $minLimit && $web > 0 ? 'bg-red-100 text-red-700' : '' }}">
                                <div class="hidden group-hover:block absolute z-20 bg-gray-800 text-white text-[10px] p-1 rounded -top-6 left-0">
                                    Precio: ${{ number_format($escala2, 0, ',', '.') }}
                                </div>
                            </td>
                            <!-- E3 -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_3_qty" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-purple-200 relative group">
                                <input type="number" step="0.1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_3_discount" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded {{ $escala3 < $minLimit && $web > 0 ? 'bg-red-100 text-red-700' : '' }}">
                                <div class="hidden group-hover:block absolute z-20 bg-gray-800 text-white text-[10px] p-1 rounded -top-6 left-0">
                                    Precio: ${{ number_format($escala3, 0, ',', '.') }}
                                </div>
                            </td>
                            <!-- E4 -->
                            <td class="p-1 border-r border-dashed border-gray-200">
                                <input type="number" step="1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_4_qty" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded">
                            </td>
                            <td class="p-1 border-r border-purple-200 relative group">
                                <input type="number" step="0.1" wire:model.live.debounce.500ms="items.{{ $index }}.scale_4_discount" class="w-full text-center text-xs py-1 px-1 bg-purple-50 border-gray-300 rounded {{ $escala4 < $minLimit && $web > 0 ? 'bg-red-100 text-red-700' : '' }}">
                                <div class="hidden group-hover:block absolute z-20 bg-gray-800 text-white text-[10px] p-1 rounded -top-6 left-0">
                                    Precio: ${{ number_format($escala4, 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="p-2 text-center">
                                <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    @if(count($items) === 0)
                        <tr>
                            <td colspan="21" class="px-6 py-10 text-center text-sm text-gray-500">
                                No has seleccionado ninguna importación ni agregado productos manualmente.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
