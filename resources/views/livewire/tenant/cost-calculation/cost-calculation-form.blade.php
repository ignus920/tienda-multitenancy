<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="{}">
    <div class="w-full space-y-4">

        <div>
            <a href="{{ route('tenant.cost-calculations') }}" wire:navigate class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cálculo de Costos
            </a>
        </div>

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex justify-between gap-4 flex-wrap items-start">
                <div class="flex-1 min-w-[260px]">
                    <div class="flex items-center gap-2">
                        @if($assignedFinishedProductCode)
                            <span class="inline-flex items-center gap-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 px-2.5 py-1 rounded-md text-xs font-bold border border-indigo-200 dark:border-indigo-800 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                {{ $assignedFinishedProductCode }}
                            </span>
                        @endif
                        
                        <input wire:model="name" type="text" @if(!$canEdit) disabled @endif
                            class="w-full text-lg font-bold bg-transparent border-b-2 border-transparent focus:border-indigo-500 focus:outline-none text-gray-900 dark:text-white py-1"
                            placeholder="Nombre del cálculo de costos *">
                    </div>
                    @error('name') <span class="text-2xs text-red-500 font-semibold">{{ $message }}</span> @enderror

                    <div class="flex gap-4 mt-2 text-2xs text-gray-500 dark:text-gray-400 flex-wrap">
                        @if($calculationId)
                            <span>Creado por <b class="text-gray-700 dark:text-gray-300">{{ $creatorName }}</b></span>
                            @if($updatedAtDisplay)
                                <span>Última edición: <b class="text-gray-700 dark:text-gray-300">{{ $updatedAtDisplay }}</b>{{ $updatedByName ? ' por '.$updatedByName : '' }}</span>
                            @else
                                <span>Creado: <b class="text-gray-700 dark:text-gray-300">{{ $createdAtDisplay }}</b></span>
                            @endif
                        @else
                            <span>Nuevo — se guardará a tu nombre ({{ $creatorName }})</span>
                        @endif
                    </div>
                </div>

                <div class="shrink-0">
                    @if($canEdit)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-2xs font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 9V7a5 5 0 00-10 0v2M5 9h14l1 12H4L5 9z"/></svg>
                            Puedes editar
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-2xs font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 9V7a5 5 0 00-10 0v2M5 9h14l1 12H4L5 9z"/></svg>
                            Solo lectura — de {{ $creatorName }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex gap-3 mt-4 flex-wrap">
                <div>
                    <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Lista de precio del ERP</label>
                    <select wire:model.live="priceListLabel" @if(!$canEdit) disabled @endif
                        class="border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs min-w-[150px]">
                        @forelse($priceListOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @empty
                            <option value="">Sin listas de precio configuradas</option>
                        @endforelse
                    </select>
                </div>
                <p class="text-3xs text-gray-400 self-end pb-2 max-w-sm">Los precios se recalculan con lo que diga el ERP en este momento — no quedan congelados del día que se creó.</p>
            </div>
        </div>

        @if($canEdit)
        <!-- Buscador -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-3 flex-wrap items-start">
            <div class="flex-1 min-w-[240px] relative" x-data="{ open: true }" @click.away="open = false">
                <input wire:model.live.debounce.300ms="search" @focus="open = true" type="text" placeholder="Buscar producto del ERP por código o nombre..."
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @if(!empty($searchResults) && $search)
                <div x-show="open" class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-40 max-h-64 overflow-y-auto">
                    @foreach($searchResults as $r)
                        <button type="button" wire:click="selectErpItem({{ $r['id'] }})"
                            class="w-full text-left px-4 py-2.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-750 flex items-center justify-between gap-2">
                            <span class="truncate max-w-md">
                                <span class="text-gray-400 mr-1">{{ $r['code'] }}</span> - <span class="font-bold">{{ $r['name'] }}</span>
                                <br>
                                <span class="text-gray-400">
                                    ${{ number_format($r['price'], 0) }}
                                    @if($r['cuttable'])
                                        @if($r['cmPrice'] !== null)
                                            · ${{ number_format($r['cmPrice'], 0) }}/cm
                                        @else
                                            · <span class="text-amber-500">sin longitud registrada</span>
                                        @endif
                                    @endif
                                </span>
                            </span>
                            <span class="shrink-0 text-3xs font-bold px-2 py-0.5 rounded-full {{ $r['cuttable'] ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $r['cuttable'] ? 'Por cm' : 'Por unidad' }}
                            </span>
                        </button>
                    @endforeach
                </div>
                @endif
            </div>
            <button type="button" wire:click="$set('showExternalForm', true)"
                class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-650 rounded-lg transition-colors shrink-0">
                + Producto externo
            </button>
        </div>
        
        <div class="mt-3 flex justify-end">
            <button type="button" wire:click="calculatePowerSupplies" wire:loading.attr="disabled"
                class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition-colors shrink-0 flex items-center gap-2">
                <svg wire:loading.remove wire:target="calculatePowerSupplies" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <svg wire:loading wire:target="calculatePowerSupplies" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                CALCULAR FUENTES
            </button>
        </div>

        @if($showExternalForm)
        <div class="bg-gray-50 dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex gap-2 flex-wrap items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Descripción</label>
                <input wire:model="extDescription" type="text" class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-xs">
                @error('extDescription') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Cantidad</label>
                <input wire:model="extQuantity" type="number" step="0.01" min="0.01" class="w-24 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Precio unitario</label>
                <input wire:model="extPrice" type="number" step="0.01" min="0" class="w-28 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-xs">
                @error('extPrice') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
            </div>
            <button type="button" wire:click="addExternalItem" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">Agregar</button>
            <button type="button" wire:click="$set('showExternalForm', false)" class="px-3 py-2 text-2xs font-semibold text-gray-500 hover:text-gray-700">Cancelar</button>
        </div>
        @endif
        @endif

        <!-- Tabla de líneas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[820px]">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-850">
                            <th class="text-left py-2.5 px-3">Origen</th>
                            <th class="text-left py-2.5 px-3 w-[38%]">Descripción</th>
                            <th class="text-right py-2.5 px-3">Cantidad</th>
                            <th class="text-right py-2.5 px-3">Precio unit.</th>
                            <th class="text-right py-2.5 px-3">Subtotal</th>
                            @if($canEdit)<th class="w-10"></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($computedLines as $i => $line)
                        <tr class="border-b border-gray-50 dark:border-gray-750 {{ !empty($line['missing']) ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                            <td class="py-2.5 px-3 flex items-center gap-2">
                                <span class="text-2xs font-bold px-2 py-0.5 rounded {{ $line['origin'] === 'erp' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                    {{ $line['origin'] === 'erp' ? 'ERP' : 'EXT' }}
                                </span>
                                @if($canEdit && $line['origin'] === 'erp')
                                    <label class="flex items-center gap-1 cursor-pointer group" @mouseenter="showTip($event, 'Marcar este producto como Variable (con opciones seleccionables en el cotizador).')" @mouseleave="tipVisible = false">
                                        <input type="checkbox" wire:click="toggleVariable({{ $i }})" {{ ($lines[$i]['is_variable'] ?? false) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </label>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-gray-800 dark:text-gray-200">
                                {{ $line['description'] }}
                                @if(!empty($line['missing']))
                                    <span class="block text-2xs text-red-500">Este producto ya no existe en el ERP</span>
                                @elseif($line['mode'] === 'cm')
                                    <span class="block text-2xs text-gray-400">Se cotiza por centímetro
                                        @if(!empty($line['no_length'])) — <span class="text-amber-500">falta longitud en el ERP</span> @endif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($canEdit)
                                    @if($line['origin'] === 'externo')
                                        <input type="number" step="0.01" min="0.01" value="{{ $line['quantity'] }}"
                                            wire:change="$set('lines.{{ $i }}.quantity', $event.target.value)"
                                            class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-sm text-right">
                                    @elseif($line['mode'] === 'cm')
                                        @php
                                            $cutStep = !empty($line['min_cut_length']) ? floatval($line['min_cut_length']) : 1;
                                            $meters = ($line['cm_quantity'] ?? 0) / 100;
                                        @endphp
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-[11px] text-gray-500 font-medium bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-1.5 py-1 rounded whitespace-nowrap" title="Equivalente en metros">
                                                {{ number_format($meters, 2) }} m
                                            </span>
                                            <div class="flex items-center gap-1">
                                                <input type="number" step="{{ $cutStep }}" min="{{ $cutStep }}" 
                                                    wire:model.live.debounce.300ms="lines.{{ $i }}.cm_quantity"
                                                    class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-sm text-right"
                                                    title="La cantidad debe ser múltiplo de {{ $cutStep }} cm">
                                                <span class="text-2xs text-gray-400">cm</span>
                                            </div>
                                        </div>
                                    @else
                                        <input type="number" step="1" min="1" value="{{ $line['quantity'] }}"
                                            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                            wire:change="$set('lines.{{ $i }}.quantity', $event.target.value)"
                                            class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-sm text-right">
                                    @endif
                                @else
                                    {{ rtrim(rtrim(number_format($line['qty_display'], 2), '0'), '.') }}{{ $line['mode'] === 'cm' ? ' cm' : '' }}
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right tabular-nums">
                                ${{ number_format($line['unit_display'], 0) }}
                                @if($line['mode'] === 'cm')<span class="block text-2xs text-gray-400">por cm</span>@endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold tabular-nums">${{ number_format($line['subtotal'], 0) }}</td>
                            @if($canEdit)
                            <td class="py-2.5 px-3 text-right">
                                <button type="button" wire:click="removeLine({{ $i }})" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                            @endif
                        </tr>

                        @if($canEdit && ($line['is_variable'] ?? false))
                        <tr class="bg-indigo-50/20 dark:bg-indigo-900/10 border-b border-indigo-100 dark:border-indigo-900/50">
                            <td colspan="6" class="px-5 py-3">
                                <div class="pl-6 border-l-2 border-indigo-300 dark:border-indigo-700">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                            Opciones de variables
                                        </span>
                                        <button type="button" wire:click="openOptionSearch({{ $i }})" class="text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 dark:hover:bg-gray-650 text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1 transition-colors">
                                            <span>+ Agregar Opción</span>
                                        </button>
                                    </div>
                                    @if(empty($line['options']))
                                        <div class="text-xs text-gray-500 dark:text-gray-400 italic bg-white dark:bg-gray-800 p-2 rounded border border-gray-100 dark:border-gray-700">No has agregado opciones. El cotizador solo mostrará el producto principal.</div>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($line['options'] as $optIdx => $opt)
                                                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-900/50 px-2.5 py-1.5 rounded shadow-sm text-xs group">
                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $opt['code'] }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ $opt['name'] }}</span>
                                                    <button type="button" wire:click="removeOption({{ $i }}, {{ $optIdx }})" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity" @mouseenter="showTip($event, 'Quitar esta opción')" @mouseleave="tipVisible = false">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Todavía no hay productos en este cálculo de costos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totales + Venta -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 py-1.5 border-b border-gray-50 dark:border-gray-750">
                    <span>Líneas del ERP</span><b class="text-gray-800 dark:text-gray-200">${{ number_format($totals['erp'], 0) }}</b>
                </div>
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 py-1.5">
                    <span>Líneas externas</span><b class="text-gray-800 dark:text-gray-200">${{ number_format($totals['ext'], 0) }}</b>
                </div>
                <div class="flex justify-between items-baseline pt-3 mt-2 border-t-2 border-gray-100 dark:border-gray-700">
                    <span class="text-base font-bold text-gray-900 dark:text-white">Total costo</span>
                    <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">${{ number_format($totals['total'], 0) }}</span>
                </div>

                <!-- Tiempos de ensamble -->
                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mb-3">Tiempo Estimado ensamble:</h3>
                    <div class="grid grid-cols-3 gap-2 text-center pb-2">
                        <!-- 1 Unidad -->
                        <div>
                            <div class="flex justify-center gap-2 mb-1">
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Horas</span>
                                    <input type="number" wire:model="time_1_hours" min="0" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Minutos</span>
                                    <input type="number" wire:model="time_1_minutes" min="0" max="59" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold border-b-2 border-indigo-100 dark:border-indigo-900/50 pb-1">1 Unidad</span>
                        </div>
                        
                        <!-- 3 Unidades -->
                        <div>
                            <div class="flex justify-center gap-2 mb-1">
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Horas</span>
                                    <input type="number" wire:model="time_3_hours" min="0" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Minutos</span>
                                    <input type="number" wire:model="time_3_minutes" min="0" max="59" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold border-b-2 border-indigo-100 dark:border-indigo-900/50 pb-1">3 Unidades</span>
                        </div>

                        <!-- 5 Unidades -->
                        <div>
                            <div class="flex justify-center gap-2 mb-1">
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Horas</span>
                                    <input type="number" wire:model="time_5_hours" min="0" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-3xs text-gray-400">Minutos</span>
                                    <input type="number" wire:model="time_5_minutes" min="0" max="59" @if(!$canEdit) disabled @endif class="w-16 h-10 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded text-sm text-center focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold border-b-2 border-indigo-100 dark:border-indigo-900/50 pb-1">5 Unidades</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Precio de venta</label>
                        <input wire:model.live="salePrice" type="number" step="0.01" @if(!$canEdit) disabled @endif
                            class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-2xs font-bold text-gray-400 uppercase mb-1">Descuento máximo (%)</label>
                        <input wire:model.live="maxDiscountPercent" type="number" step="0.01" min="0" max="100" @if(!$canEdit) disabled @endif
                            class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-xs">
                    </div>
                </div>

                @php
                    $sale = is_numeric($salePrice) ? (float) $salePrice : null;
                    $disc = is_numeric($maxDiscountPercent) ? (float) $maxDiscountPercent : 0;
                    $saleOver = $sale !== null ? $sale - $totals['total'] : null;
                    $saleWithDiscount = $sale !== null ? $sale * (1 - $disc / 100) : null;
                    $discOver = $saleWithDiscount !== null ? $saleWithDiscount - $totals['total'] : null;
                @endphp

                <div class="flex justify-between items-center px-3 py-2.5 rounded-lg text-sm font-semibold
                    {{ $sale === null ? 'bg-gray-50 dark:bg-gray-750 text-gray-400' : ($saleOver < 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400') }}">
                    <span>Precio de venta vs. costo</span>
                    <span class="font-extrabold">{{ $sale === null ? '—' : '$'.number_format($sale, 0) }}</span>
                </div>
                <div class="flex justify-between items-center px-3 py-2.5 rounded-lg text-sm font-semibold
                    {{ $saleWithDiscount === null ? 'bg-gray-50 dark:bg-gray-750 text-gray-400' : ($discOver < 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400') }}">
                    <span>Con descuento máximo</span>
                    <span class="font-extrabold">{{ $saleWithDiscount === null ? '—' : '$'.number_format($saleWithDiscount, 0) }}</span>
                </div>
                <p class="text-3xs text-gray-400">Rojo: por debajo del costo · Azul: por encima del costo</p>
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                @if($calculationId && $canEdit)
                <button type="button" wire:click="confirmDelete" class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Eliminar
                </button>
                @endif
            </div>
            <div class="flex gap-2">
                @if($calculationId)
                <button type="button" wire:click="exportPdf" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                    Imprimir PDF
                </button>
                <button type="button" wire:click="exportExcel" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                    Descargar Excel
                </button>
                @endif
                @if($canEdit)
                @if($type !== 'finished_product')
                <button type="button" wire:click="openFinishedProductModal" class="px-5 py-2 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow transition-colors">
                    Crear Producto Terminado
                </button>
                @else
                <span class="px-4 py-2 text-xs font-bold text-green-700 bg-green-100 rounded-lg border border-green-200 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Es un Producto Terminado
                </span>
                @endif
                <button type="button" wire:click="save" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                    Guardar
                </button>
                @endif
            </div>
        </div>

    </div>

    @if($showDeleteModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-sm w-full p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Eliminar cálculo de costos</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Escribe la razón por la que se elimina — queda guardada.</p>
            <textarea wire:model="deleteReason" rows="3" placeholder="Ej: cliente canceló, se duplicó por error..."
                class="w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-lg px-3 py-2 text-xs"></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" wire:click="$set('showDeleteModal', false)" class="px-3 py-2 text-2xs font-semibold text-gray-500 hover:text-gray-700">Cancelar</button>
                <button type="button" wire:click="deleteCalculation" class="px-4 py-2 text-2xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg">Eliminar de todas formas</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Cálculo de Fuentes -->
    <div x-data="{ show: @entangle('showPowerSupplyModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                 @click="show = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-full sm:max-w-5xl lg:max-w-6xl xl:max-w-7xl">
                
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2" id="modal-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        Informe: Cálculo de Fuentes
                    </h3>
                    <button type="button" @click="show = false" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="px-6 py-6 bg-gray-50 dark:bg-gray-900 max-h-[75vh] overflow-y-auto space-y-6">
                    @if(!empty($powerSupplyResults))
                        @foreach($powerSupplyResults as $result)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                            
                            <!-- Resumen por Voltaje -->
                            <div class="bg-gray-100 dark:bg-gray-750 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-lg font-extrabold text-gray-900 dark:text-white uppercase mb-3">
                                    VOLTAJE: <span class="text-indigo-600 dark:text-indigo-400">{{ $result['voltage'] }} VDC</span>
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 text-center">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Potencia Instalada</p>
                                        <p class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ number_format($result['installed_power'], 2) }} W</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-600 text-center relative">
                                        <div class="absolute -left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xl">+</div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Margen Adicional</p>
                                        <p class="text-lg font-bold text-gray-800 dark:text-gray-200">20%</p>
                                        <div class="absolute -right-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xl">=</div>
                                    </div>
                                    <div class="bg-indigo-50 dark:bg-indigo-900/30 p-3 rounded-lg border border-indigo-200 dark:border-indigo-800 text-center">
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold uppercase">Potencia Requerida</p>
                                        <p class="text-xl font-extrabold text-indigo-700 dark:text-indigo-300">{{ number_format($result['required_power'], 2) }} W</p>
                                    </div>
                                    <div class="bg-amber-50 dark:bg-amber-900/30 p-2 rounded-lg border border-amber-200 dark:border-amber-800 text-center" title="Solo aplica para productos de la gama de Blancos.">
                                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase leading-tight">Intensidad Teórica</p>
                                        <p class="text-xl font-extrabold text-amber-700 dark:text-amber-300">{{ number_format($result['theoretical_intensity'], 0) }} Lm</p>
                                        <p class="text-3xs text-amber-500/80 mt-0.5 leading-none">Solo aplica a gama de blancos.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Alternativas -->
                            <div class="p-5">
                                <h5 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase mb-4">Alternativas Encontradas:</h5>
                                
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600">
                                            <tr>
                                                <th scope="col" class="px-4 py-3">Grupo Comercial</th>
                                                <th scope="col" class="px-3 py-2 w-[40%]">Código / Descripción</th>
                                                <th scope="col" class="px-2 py-2 text-center">Cantidad</th>
                                                <th scope="col" class="px-2 py-2 text-right">Precio unit.</th>
                                                <th scope="col" class="px-2 py-2 text-right">Potencia unit.</th>
                                                <th scope="col" class="px-2 py-2 text-right">Potencia total</th>
                                                <th scope="col" class="px-2 py-2 w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($result['brands'] as $brand)
                                                @php
                                                    // Asignar colores por marca (dinámico)
                                                    $bName = strtolower(trim($brand['brand_name']));
                                                    $bgClass = 'bg-gray-50 dark:bg-gray-800/50';
                                                    if(str_contains($bName, 'mean well') || str_contains($bName, 'meanwell')) $bgClass = 'bg-blue-50/50 dark:bg-blue-900/10';
                                                    elseif(str_contains($bName, 'cl')) $bgClass = 'bg-orange-50/50 dark:bg-orange-900/10';
                                                    elseif(str_contains($bName, 'genérica') || str_contains($bName, 'generica')) $bgClass = 'bg-green-50/50 dark:bg-green-900/10';
                                                    elseif(str_contains($bName, 'slim')) $bgClass = 'bg-purple-50/50 dark:bg-purple-900/10';
                                                @endphp
                                                
                                                @foreach($brand['options'] as $idx => $opt)
                                                    <tr class="border-b border-gray-100 dark:border-gray-750 {{ $bgClass }} transition-colors {{ $opt['stock'] < $opt['quantity'] ? 'opacity-60 bg-gray-200/50 dark:bg-gray-800' : 'hover:bg-gray-100 dark:hover:bg-gray-700/50' }}">
                                                        @if($idx === 0)
                                                            <td rowspan="{{ count($brand['options']) }}" class="px-4 py-3 font-extrabold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700 align-middle">
                                                                {{ $brand['brand_name'] }}
                                                            </td>
                                                        @endif
                                                        <td class="px-3 py-2">
                                                            <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $opt['code'] }}</span>
                                                            <div class="text-xs text-gray-500 truncate max-w-[250px]" title="{{ $opt['description'] ?? '' }}">
                                                                {{ $opt['description'] ?? '' }}
                                                            </div>
                                                        </td>
                                                        <td class="px-2 py-2 text-center font-bold text-gray-900 dark:text-white">{{ $opt['quantity'] }}</td>
                                                        <td class="px-2 py-2 text-right font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">${{ number_format($opt['unit_price'] ?? 0, 0) }}</td>
                                                        <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format($opt['unit_power'], 0) }} W</td>
                                                        <td class="px-2 py-2 text-right font-extrabold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">{{ number_format($opt['total_power'], 0) }} W</td>
                                                        <td class="px-2 py-2 text-right">
                                                            @if($opt['stock'] < $opt['quantity'])
                                                                <span class="text-xs font-bold text-red-500 dark:text-red-400 block whitespace-nowrap text-center">Sin stock ({{ $opt['stock'] }})</span>
                                                            @else
                                                                <button type="button" wire:click="addPowerSupplyToLines({{ $opt['item_id'] }}, {{ $opt['quantity'] }})" class="px-3 py-1.5 text-xs font-bold text-white bg-green-500 hover:bg-green-600 rounded shadow-sm transition-colors flex items-center gap-1">
                                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                                    Agregar
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        @endforeach
                    @endif
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button type="button" @click="show = false" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                        Cerrar Informe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Opciones Variables -->
    <div x-data="{ show: @entangle('showOptionSearchModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                 @click="show = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2" id="modal-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar y Agregar Opciones
                    </h3>
                    <button type="button" @click="show = false" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="px-6 py-6 bg-white dark:bg-gray-900 min-h-[400px]">
                    <div class="mb-6 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="optionSearch" type="text"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            placeholder="Buscar producto en ERP por código o nombre...">
                        
                        <div wire:loading wire:target="optionSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    @if(!empty($optionSearchResults))
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Resultados de Búsqueda</h4>
                            @foreach($optionSearchResults as $r)
                                <button type="button" wire:click="selectOptionItem({{ $r['id'] }})"
                                    class="w-full text-left px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:shadow-md transition-all flex items-center justify-between group">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ $r['code'] }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $r['name'] }}
                                        </span>
                                    </div>
                                    <div class="shrink-0 text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @elseif(strlen($optionSearch) >= 2)
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No se encontraron productos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Intenta buscar con otras palabras o código.</p>
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <p class="text-sm">Escribe al menos 2 caracteres para buscar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Producto Terminado -->
    <div x-data="{ show: @entangle('showFinishedProductModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="show = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="show" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <div class="bg-green-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Convertir en Producto Terminado
                    </h3>
                    <button type="button" @click="show = false" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="px-6 py-5 bg-white dark:bg-gray-900 space-y-4 overflow-y-auto max-h-[70vh]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Al confirmar, esta receta se guardará y se creará un nuevo ítem oficial en el inventario listo para ser cotizado por los vendedores.
                    </p>

                    {{-- Categoría --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                            Categoría <span class="text-red-500">*</span>
                            <div x-data="{ show: false }" class="relative inline-block ml-1">
                                <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                    Categoría a la que pertenece el producto en el inventario.
                                </div>
                            </div>
                        </label>
                        <select wire:model="fp_category_id" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Seleccione una categoría...</option>
                            @foreach($categoriesList as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('fp_category_id') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                            Nombre <span class="text-red-500">*</span>
                            <div x-data="{ show: false }" class="relative inline-block ml-1">
                                <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                    Nombre comercial o descripción corta del producto.
                                </div>
                            </div>
                        </label>
                        <input type="text" wire:model="fp_name" placeholder="Ingrese nombre del producto"
                            class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        @error('fp_name') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Código interno + SKU --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Código interno <span class="text-red-500">*</span>
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Código de identificación interno del producto.
                                    </div>
                                </div>
                            </label>
                            <input type="text" wire:model="fp_code" placeholder="Ej: PT-001"
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            @error('fp_code') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                SKU
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Código SKU. Si se deja vacío, se usará el código interno.
                                    </div>
                                </div>
                            </label>
                            <input type="text" wire:model="fp_sku" placeholder="Ej: PT-001"
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    {{-- Tipo + Impuesto --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Tipo <span class="text-red-500">*</span>
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Define el tipo de artículo (Ensamblado, Importado, Producido, etc.).
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_type" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">-- Seleccione --</option>
                                @foreach($fpItemTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('fp_type') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Impuesto <span class="text-red-500">*</span>
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Porcentaje de IVA o impuesto aplicable al producto.
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_tax_id" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">-- Seleccione --</option>
                                @foreach($taxesList as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->percentage, 2) }}%)</option>
                                @endforeach
                            </select>
                            @error('fp_tax_id') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Marca --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                            Marca
                            <div x-data="{ show: false }" class="relative inline-block ml-1">
                                <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                    Marca comercial del producto.
                                </div>
                            </div>
                        </label>
                        <select wire:model="fp_brand_id" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Seleccione una marca</option>
                            @foreach($brandsList as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Casa --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                            Casa
                            <div x-data="{ show: false }" class="relative inline-block ml-1">
                                <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                    Casa fabricante o distribuidora del producto.
                                </div>
                            </div>
                        </label>
                        <select wire:model="fp_house_id" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Seleccione una casa</option>
                            @foreach($housesList as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unidad de compra + Unidad de consumo --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Unidad de compra
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Unidad en la que se compra el producto al proveedor.
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_purchasing_unit" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccione</option>
                                @foreach($purchasingUnitsList as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Unidad de consumo
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Unidad en la que se consume o vende el producto.
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_consumption_unit" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccione</option>
                                @foreach($consumptionUnitsList as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Maneja Serial + Maneja Inventario --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Maneja Serial
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Indica si el producto requiere seguimiento por número de serie.
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_handles_serial" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                                Maneja Inventario
                                <div x-data="{ show: false }" class="relative inline-block ml-1">
                                    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-green-600 focus:outline-none transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                    <div x-show="show" x-cloak x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-[10px] rounded-lg shadow-xl z-50 text-center">
                                        Indica si se controlan las existencias físicas en el inventario.
                                    </div>
                                </div>
                            </label>
                            <select wire:model="fp_inventoriable" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="1">SI</option>
                                <option value="0">NO</option>
                            </select>
                        </div>
                    </div>

                    {{-- Proveedor --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                            Proveedor <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="fp_supplier_id" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Seleccionar Proveedor</option>
                            @foreach($suppliersList as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('fp_supplier_id') <span class="text-3xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Sección Valores --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-3">Valores</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Etiqueta</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @php
                                        $fpStaticValues = [
                                            ['label' => 'Costo Inicial',          'display' => 'Costo Inicial',          'type' => 'Costo'],
                                            ['label' => 'Costo',                  'display' => 'Costo',                  'type' => 'Costo'],
                                            ['label' => 'Precio Base',            'display' => 'Precio Lista',           'type' => 'Precio'],
                                            ['label' => 'Precio Regular',         'display' => 'Precio Mínimo',          'type' => 'Precio'],
                                            ['label' => 'Precio Crédito',         'display' => 'Precio Crédito',         'type' => 'Precio'],
                                            ['label' => 'Precio unitario x caja', 'display' => 'Precio unitario x caja', 'type' => 'Precio'],
                                        ];
                                    @endphp
                                    @foreach($fpStaticValues as $fpVal)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-white">{{ $fpVal['display'] }}</td>
                                            <td class="px-3 py-2 text-xs">
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $fpVal['type'] === 'Costo' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                                    {{ $fpVal['type'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-xs text-right">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    wire:model="fp_temp_values.{{ $fpVal['label'] }}"
                                                    placeholder="0.00"
                                                    class="w-28 px-2 py-1 text-right border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white text-xs"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" @click="show = false" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="saveAsFinishedProduct" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="saveAsFinishedProduct" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span wire:loading wire:target="saveAsFinishedProduct">Procesando...</span>
                        <span wire:loading.remove wire:target="saveAsFinishedProduct">Guardar y Crear Producto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
