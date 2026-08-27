<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Lista de Materiales</h2>
        <div class="flex items-center gap-2">
            <button wire:click="exportPdf" type="button"
                class="px-3 py-1.5 text-2xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-650 rounded-lg transition-colors">
                Descargar PDF
            </button>
            <button wire:click="exportExcel" type="button"
                class="px-3 py-1.5 text-2xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-650 rounded-lg transition-colors">
                Descargar Excel
            </button>
        </div>
    </div>

    <!-- Buscador de productos ERP -->
    <div class="bg-gray-50 dark:bg-gray-850 rounded-lg p-4 border border-gray-100 dark:border-gray-750 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <span class="text-2xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Agregar producto del ERP</span>
            
            <div class="flex items-center gap-2 flex-1 md:justify-end">
                <div class="w-20 shrink-0">
                    <input wire:model="quantity" type="number" step="0.01" min="0.01" placeholder="Cant."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div class="flex-1 max-w-xs">
                    <input wire:model="observations" type="text" placeholder="Observaciones (opcional)"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div class="shrink-0">
                    <button type="button" wire:click="addErpMaterial" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition-colors h-full">
                        AGREGAR
                    </button>
                </div>
            </div>
        </div>

        <div class="w-full relative" x-data="{ open: true }" @click.away="open = false">
            <input wire:model.live.debounce.300ms="search" @focus="open = true" type="text" placeholder="Buscar por código, referencia o nombre..."
                class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            @if(!empty($searchResults) && $search)
                <div x-show="open" class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-40 max-h-56 overflow-y-auto">
                    @foreach($searchResults as $result)
                        <button type="button" wire:click="selectErpMaterial({{ $result['id'] }}, '{{ addslashes($result['name']) }}', {{ $result['price'] }})"
                            class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-750 flex items-center justify-between gap-2">
                            <span>
                                <span class="font-bold block truncate max-w-lg">{{ $result['name'] }}</span>
                                <span class="text-gray-400 text-3xs">{{ $result['code'] }}</span>
                            </span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">${{ number_format($result['price'], 2) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        
        <div>
            @error('quantity') <span class="text-3xs text-red-500 block mb-1 font-semibold">{{ $message }}</span> @enderror
            <p class="text-3xs text-gray-400">Haz clic sobre un resultado de la búsqueda para seleccionarlo. Completa los datos y haz clic en "Agregar".</p>
        </div>
    </div>

    <!-- Producto externo -->
    <div class="space-y-3 mt-4">
        @if(!$showExternalForm)
            <button wire:click="$set('showExternalForm', true)" type="button"
                class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                + Agregar producto externo
            </button>
        @else
            <div class="bg-gray-50 dark:bg-gray-850 rounded-lg p-4 border border-gray-100 dark:border-gray-750 space-y-3">
                <span class="text-2xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto externo (no está en el ERP)</span>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <input wire:model="externalDescription" type="text" placeholder="Descripción *"
                        class="md:col-span-2 block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <input wire:model="externalQuantity" type="number" step="0.01" min="0.01" placeholder="Cantidad *"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <input wire:model="externalUnitValue" type="number" step="0.01" min="0" placeholder="Valor unitario *"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <input wire:model="externalObservations" type="text" placeholder="Observaciones (opcional)"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('externalDescription') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
                @error('externalUnitValue') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
                @error('externalQuantity') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showExternalForm', false)" type="button" class="px-3 py-1.5 text-2xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancelar</button>
                    <button wire:click="addExternalMaterial" type="button" class="px-3 py-1.5 text-2xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow">Agregar</button>
                </div>
            </div>
        @endif
    </div>

    <!-- Tabla de materiales -->
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-2xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <th class="text-left py-2 pr-2">Origen</th>
                    <th class="text-left py-2 pr-2">Descripción</th>
                    <th class="text-right py-2 pr-2">Cantidad</th>
                    <th class="text-right py-2 pr-2">Precio Unit.</th>
                    <th class="text-right py-2 pr-2">Costo</th>
                    <th class="text-left py-2 pr-2">Observaciones</th>
                    <th class="text-right py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $material)
                    <tr class="border-b border-gray-50 dark:border-gray-750">
                        <td class="py-2 pr-2">
                            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative inline-block">
                                <span class="cursor-help px-1.5 py-0.5 rounded text-3xs font-bold {{ $material->origin === 'erp' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                    {{ $material->origin === 'erp' ? 'ERP' : 'Externo' }}
                                </span>
                                <div x-show="show" x-transition.opacity style="display: none;"
                                     class="absolute z-50 left-0 bottom-full mb-1 w-max px-2 py-1.5 bg-gray-800 text-white text-[10px] rounded shadow-lg leading-tight text-left">
                                    <span class="font-bold">{{ $material->creator ? $material->creator->name : 'Usuario Desconocido' }}</span><br>
                                    <span class="text-gray-300">{{ $material->created_at->format('d/m/Y h:i A') }}</span>
                                    <div class="absolute w-2 h-2 bg-gray-800 rotate-45 left-4 -bottom-1"></div>
                                </div>
                            </div>
                        </td>

                        @if($editingMaterialId === $material->id)
                            <td class="py-2 pr-2">
                                @if($material->origin === 'externo')
                                    <input wire:model="editDescription" type="text" class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs">
                                @else
                                    {{ $material->description }}
                                @endif
                            </td>
                            <td class="py-2 pr-2 text-right">
                                <input wire:model="editQuantity" type="number" step="0.01" min="0.01" class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs text-right">
                            </td>
                            <td class="py-2 pr-2 text-right">
                                @if($material->origin === 'externo')
                                    <input wire:model="editUnitValue" type="number" step="0.01" min="0" class="w-24 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs text-right">
                                @else
                                    ${{ number_format($material->unit_value, 2) }}
                                @endif
                            </td>
                            <td class="py-2 pr-2 text-right font-semibold">${{ number_format($material->line_cost, 2) }}</td>
                            <td class="py-2 pr-2 text-gray-500">{{ $material->observations }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button wire:click="saveEdit" class="text-emerald-600 hover:text-emerald-700 font-semibold text-2xs mr-2">Guardar</button>
                                <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600 font-semibold text-2xs">Cancelar</button>
                            </td>
                        @else
                            <td class="py-2 pr-2 text-gray-800 dark:text-gray-200 font-medium">{{ $material->description }}</td>
                            <td class="py-2 pr-2 text-right">{{ rtrim(rtrim(number_format($material->quantity, 2), '0'), '.') }}</td>
                            <td class="py-2 pr-2 text-right">${{ number_format($material->unit_value, 2) }}</td>
                            <td class="py-2 pr-2 text-right font-semibold">${{ number_format($material->line_cost, 2) }}</td>
                            <td class="py-2 pr-2 text-gray-500">{{ $material->observations }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button wire:click="editMaterial({{ $material->id }})" class="text-indigo-600 hover:text-indigo-700 font-semibold text-2xs mr-2">Editar</button>
                                <button wire:click="deleteMaterial({{ $material->id }})" wire:confirm="¿Eliminar esta línea de materiales?" class="text-red-500 hover:text-red-600 font-semibold text-2xs">Eliminar</button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-400 text-xs">Aún no se han agregado materiales a este proyecto.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Subtotales -->
    <div class="flex justify-end">
        <div class="w-full md:w-72 space-y-1 text-xs">
            <div class="flex justify-between text-gray-500">
                <span>Subtotal ERP</span>
                <span>${{ number_format($subtotalErp, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Subtotal Externos</span>
                <span>${{ number_format($subtotalExterno, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 dark:text-white border-t border-gray-100 dark:border-gray-700 pt-1">
                <span>Costo Total</span>
                <span>${{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
