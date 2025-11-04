<div>
    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Items</h3>
            <div>
                <input wire:model.debounce="search" type="text" placeholder="Buscar..." class="border rounded px-2 py-1 mr-2">
                <button wire:click="create" class="bg-indigo-600 text-white px-3 py-1 rounded">Nuevo Item</button>
            </div>
        </div>

        <table class="min-w-full table-auto">
            <thead>
                <tr class="text-left">
                    <th class="px-2">Nombre</th>
                    <th class="px-2">Código</th>
                    <th class="px-2">SKU</th>
                    <th class="px-2">Tipo</th>
                    <th class="px-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $it)
                    <tr class="border-t">
                        <td class="px-2 py-2">{{ $it->name }}</td>
                        <td class="px-2 py-2">{{ $it->internal_code ?? $it->internalCode ?? '' }}</td>
                        <td class="px-2 py-2">{{ $it->sku }}</td>
                        <td class="px-2 py-2">{{ $it->type }}</td>
                        <td class="px-2 py-2">
                            <button wire:click="edit({{ $it->id }})" class="text-indigo-600 mr-2">Editar</button>
                            <button wire:click="confirmItemDeletion({{ $it->id }})" class="text-red-600">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">No se encontraron items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>

    <!-- Modal: Create / Edit -->
    <div x-data="{ open: @entangle('showModal') }" x-show="open" style="display:none;" class="fixed inset-0 z-40 flex items-center justify-center">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="bg-white rounded shadow p-6 z-50 w-full max-w-lg">
            <h4 class="text-lg font-medium mb-4">{{ $item_id ? 'Editar Item' : 'Crear Item' }}</h4>

            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="block text-sm">Categoría</label>
                    <select wire:model="category_id" class="w-full border rounded px-2 py-1">
                        <option value="">-- Seleccione --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Nombre</label>
                    <input wire:model="name" type="text" class="w-full border rounded px-2 py-1">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm">Código interno</label>
                        <input wire:model="internal_code" type="text" class="w-full border rounded px-2 py-1">
                        @error('internal_code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">SKU</label>
                        <input wire:model="sku" type="text" class="w-full border rounded px-2 py-1">
                        @error('sku') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-sm">Tipo</label>
                    <select wire:model="type" class="w-full border rounded px-2 py-1">
                        <option value="">-- Seleccione --</option>
                        @foreach($types as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                @livewire('tenant.items.command', [
                    'commandId' => $commandId,
                    'name' => 'commandId',
                    'label' => 'Comanda',
                    'placeholder' => 'Seleccione una comanda',
                    'class' => 'w-full border rounded px-2 py-1'
                ])

                @livewire('tenant.items.brand',[
                    'brandId' => $brandId,
                    'name' => 'brandId',
                    'label' => 'Marca',
                    'placeholder' => 'Seleccione una marca',
                    'class' => 'w-full border rounded px-2 py-1'
                ])

                @livewire('tenant.items.house',[
                    'houseId' => $houseId,
                    'name' => 'houseId',
                    'label' => 'Casa',
                    'placeholder' => 'Seleccione una casa',
                    'class' => 'w-full border rounded px-2 py-1'
                ])

                @livewire('tenant.items.purchasing-unit', [
                    'purchaseUnitId' => $purchase_unit,
                    'name' => 'purchase_unit',
                    'label' => 'Unidad de compra',
                    'placeholder' => 'Seleccione una unidad de compra',
                    'class' => 'w-full border rounded px-2 py-1'
                ])

                @livewire('tenant.items.consumption-unit', [
                    'consumptionUnitId' => $consumption_unit,
                    'name' => 'consumption_unit',
                    'label' => 'Unidad de consumo',
                    'placeholder' => 'Seleccione una unidad de consumo',
                    'class' => 'w-full border rounded px-2 py-1'
                ])

                <div class="mb-3">
                    <label class="block text-sm">Descripción</label>
                    <textarea wire:model="description" class="w-full border rounded px-2 py-1" rows="3"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="cancel" class="px-3 py-1 border rounded">Cancelar</button>
                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">{{ $item_id ? 'Actualizar' : 'Crear' }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete confirmation (simple) -->
    <div x-data="{ open: @entangle('confirmingItemDeletion') }" x-show="open" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="bg-white rounded shadow p-6 z-50 w-full max-w-md">
            <h4 class="text-lg font-medium mb-4">Confirmar eliminación</h4>
            <p class="mb-4">¿Deseas eliminar este item?</p>
            <div class="flex justify-end space-x-2">
                <button type="button" wire:click="cancel" class="px-3 py-1 border rounded">Cancelar</button>
                <button type="button" wire:click="deleteItem" class="px-3 py-1 bg-red-600 text-white rounded">Eliminar</button>
            </div>
        </div>
    </div>
</div>
