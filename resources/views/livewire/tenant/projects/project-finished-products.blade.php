<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-6">
    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Producto Terminado</h2>

    @if(!$isClosed)
    <!-- Formulario de alta -->
    <div class="bg-gray-50 dark:bg-gray-850 rounded-lg p-4 border border-gray-100 dark:border-gray-750 space-y-3">
        <span class="text-2xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agregar producto terminado</span>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
            <input wire:model="description" type="text" placeholder="Descripción *"
                class="md:col-span-2 block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input wire:model="price" type="number" step="0.01" min="0" placeholder="Precio *"
                class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input wire:model="quantity" type="number" step="0.01" min="0.01" placeholder="Cantidad *"
                class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        @error('description') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
        @error('price') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
        @error('quantity') <span class="text-3xs text-red-500 block font-semibold">{{ $message }}</span> @enderror
        <div class="flex justify-end">
            <button wire:click="addFinishedProduct" type="button"
                class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                AGREGAR
            </button>
        </div>
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
                            <td class="py-2 pr-2 text-gray-800 dark:text-gray-200 font-medium">{{ $product->description }}</td>
                            <td class="py-2 pr-2 text-right">{{ rtrim(rtrim(number_format($product->quantity, 2), '0'), '.') }}</td>
                            <td class="py-2 pr-2 text-right">${{ number_format($product->price, 2) }}</td>
                            <td class="py-2 pr-2 text-right font-semibold">${{ number_format($product->price * $product->quantity, 2) }}</td>
                            @if(!$isClosed)
                            <td class="py-2 text-right whitespace-nowrap">
                                <button wire:click="editFinishedProduct({{ $product->id }})" class="text-indigo-600 hover:text-indigo-700 font-semibold text-2xs mr-2">Editar</button>
                                <button type="button"
                                    wire:click="deleteFinishedProduct({{ $product->id }})"
                                    wire:confirm="¿Eliminar este producto terminado?"
                                    class="text-red-500 hover:text-red-600 font-semibold text-2xs">Eliminar</button>
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

    <!-- Total -->
    <div class="flex justify-end mt-4">
        <div class="w-full md:w-72 text-xs">
            <div class="flex justify-between font-bold text-gray-900 dark:text-white pt-1">
                <span>Total</span>
                <span>${{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
