<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-6">
    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Producto Terminado</h2>

    @if(!$canManage)
        <p class="text-xs text-gray-400 text-center py-10">No tienes acceso a esta sección.</p>
    @else

    @if(!$isClosed)
    <!-- Formulario de alta -->
    <div class="bg-gray-50 dark:bg-gray-850 rounded-lg p-4 border border-gray-100 dark:border-gray-750 space-y-3">
        <span class="text-2xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agregar producto terminado</span>
        <p class="text-3xs text-gray-400">
            El producto terminado debe existir en el ERP. Al darle "Agregar" solo queda en la lista, en borrador — revisa que todo esté correcto y luego dale "Generar Entrada de Inventario" para confirmar y sincronizar con el ERP y Alegra.
        </p>

        <div class="flex flex-col md:flex-row md:items-start gap-2">
            <div class="flex-1 relative" x-data="{ open: true }" @click.away="open = false">
                <input wire:model.live.debounce.300ms="search" @focus="open = true" type="text" placeholder="Buscar por código, referencia o nombre..."
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @if(!empty($searchResults) && $search)
                    <div x-show="open" class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-40 max-h-56 overflow-y-auto">
                        @foreach($searchResults as $result)
                            <button type="button" wire:click="selectErpProduct({{ $result['id'] }}, '{{ addslashes($result['name']) }}', {{ $result['price'] }}, '{{ addslashes($result['code']) }}')"
                                class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-750 flex items-center justify-between gap-2">
                                <span class="truncate max-w-lg">
                                    <span class="text-gray-400 mr-1">{{ $result['code'] }}</span> - <span class="font-bold ml-1">{{ $result['name'] }}</span>
                                </span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">${{ number_format($result['price'], 2) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <input wire:model="price" type="number" step="0.01" min="0" placeholder="Precio *"
                class="block w-full md:w-28 shrink-0 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input wire:model="quantity" type="number" step="0.01" min="0.01" placeholder="Cantidad *"
                class="block w-full md:w-20 shrink-0 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <button wire:click="addFinishedProduct" type="button"
                class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors shrink-0">
                AGREGAR
            </button>
        </div>
        @error('price') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
        @error('quantity') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
        <p class="text-3xs text-gray-400">Haz clic sobre un resultado de la búsqueda para seleccionarlo. Completa Precio/Cantidad y haz clic en "Agregar".</p>
    </div>
    @endif

    <!-- Tabla de productos terminados -->
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-2xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <th class="text-left py-2 pr-2 w-[50%]">Descripción</th>
                    <th class="text-right py-2 pr-2">Cantidad</th>
                    <th class="text-right py-2 pr-2">Precio</th>
                    <th class="text-right py-2 pr-2">Subtotal</th>
                    @if(!$isClosed)<th class="text-right py-2 w-24">Acciones</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b border-gray-50 dark:border-gray-750">
                        @if($editingId === $product->id)
                            <td class="py-2 pr-2">
                                <input wire:model="editDescription" type="text" class="w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs">
                            </td>
                            <td class="py-2 pr-2 text-right">
                                <input wire:model="editQuantity" type="number" step="0.01" min="0.01" class="w-20 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs text-right">
                            </td>
                            <td class="py-2 pr-2 text-right">
                                <input wire:model="editPrice" type="number" step="0.01" min="0" class="w-24 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs text-right">
                            </td>
                            <td class="py-2 pr-2 text-right font-semibold">${{ number_format($product->price * $product->quantity, 2) }}</td>
                            @if(!$isClosed)
                            <td class="py-2 text-right whitespace-nowrap">
                                <button wire:click="saveEdit" class="text-emerald-600 hover:text-emerald-700 font-semibold text-2xs mr-2">Guardar</button>
                                <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-600 font-semibold text-2xs">Cancelar</button>
                            </td>
                            @endif
                        @else
                            <td class="py-2 pr-2 text-gray-800 dark:text-gray-200 font-medium">
                                {{ $product->description }}
                                @if($product->inventory_adjustment_id)
                                    <span class="ml-1 px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-3xs font-semibold uppercase tracking-wide">Entrada generada</span>
                                @else
                                    <span class="ml-1 px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-3xs font-semibold uppercase tracking-wide">Pendiente</span>
                                @endif
                            </td>
                            <td class="py-2 pr-2 text-right">{{ rtrim(rtrim(number_format($product->quantity, 2), '0'), '.') }}</td>
                            <td class="py-2 pr-2 text-right">${{ number_format($product->price, 2) }}</td>
                            <td class="py-2 pr-2 text-right font-semibold">${{ number_format($product->price * $product->quantity, 2) }}</td>
                            @if(!$isClosed)
                            <td class="py-2 text-right whitespace-nowrap">
                                @if(!$product->inventory_adjustment_id)
                                    <button wire:click="editFinishedProduct({{ $product->id }})" class="text-indigo-600 hover:text-indigo-700 font-semibold text-2xs mr-2">Editar</button>
                                    <button type="button"
                                        wire:click="deleteFinishedProduct({{ $product->id }})"
                                        wire:confirm="¿Eliminar este producto terminado?"
                                        class="text-red-500 hover:text-red-600 font-semibold text-2xs">Eliminar</button>
                                @else
                                    <span class="text-3xs text-gray-400">—</span>
                                @endif
                            </td>
                            @endif
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-400 text-xs">Aún no se ha registrado producto terminado en este proyecto.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!$isClosed && $hasPendingEntry)
    <div class="flex items-center justify-between gap-3 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/60 rounded-lg p-3">
        <p class="text-3xs text-emerald-700 dark:text-emerald-400">
            Revisa la lista antes de confirmar — al generar la entrada se sube el stock del ERP y se sincroniza con Alegra, para todos los productos "Pendiente" de este proyecto.
        </p>
        <button wire:click="generateInventoryEntry" wire:confirm="¿Generar la entrada de inventario para los productos pendientes? Se sincronizará con el ERP y Alegra." type="button"
            class="shrink-0 px-4 py-1.5 text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition-colors">
            Generar Entrada de Inventario
        </button>
    </div>
    @endif

    <!-- Total -->
    <div class="flex justify-end mt-4">
        <div class="w-full md:w-72 text-xs">
            <div class="flex justify-between font-bold text-gray-900 dark:text-white pt-1">
                <span>Total</span>
                <span>${{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>
    @endif
</div>
