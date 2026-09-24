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
                    <input wire:model="name" type="text" @if(!$canEdit) disabled @endif
                        class="w-full text-lg font-bold bg-transparent border-b-2 border-transparent focus:border-indigo-500 focus:outline-none text-gray-900 dark:text-white py-1"
                        placeholder="Nombre del cálculo de costos *">
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
                            <td class="py-2.5 px-3">
                                <span class="text-2xs font-bold px-2 py-0.5 rounded {{ $line['origin'] === 'erp' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                    {{ $line['origin'] === 'erp' ? 'ERP' : 'EXT' }}
                                </span>
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
                                        <input type="number" step="1" min="1" value="{{ $line['cm_quantity'] }}"
                                            wire:change="$set('lines.{{ $i }}.cm_quantity', $event.target.value)"
                                            class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-sm text-right">
                                        <span class="text-2xs text-gray-400">cm</span>
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
</div>
