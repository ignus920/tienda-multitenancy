<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Solicitud de transferencias
                        {{-- <span class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                            | valor dinamico
                        </span> --}}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Solicitud de transferencias entre bodegas</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
            <!--CARD IZQUIERDO-->
            <div class="lg:col-span-5 xl:col-span-5 order-1 lg:order-1">
                <!-- Tab System -->
                <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
                    <!-- Toolbar -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Búsqueda -->
                            <div class="flex-1 max-w-md">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.300ms="search" type="text"
                                        placeholder="Buscar registros..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
        
                            <!-- Controles -->
                            <div class="flex items-center gap-3">
                                <!-- Registros por página -->
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                                    <select wire:model.live="perPage"
                                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla -->
                    <div class="relative overflow-visible">
                        <div class="min-w-full overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Código
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Item
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Saldo
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Máx. Stock Origen
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Cantidad
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center font-medium text-gray-900 dark:text-white">
                                                {{ $item->internal_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-left font-medium text-gray-900 dark:text-white">
                                                {{ $item->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                                {{ $item->invItemsStore->first()->stock_items_store ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                                {{ $item->total_stock_by_warehouse ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <input type="number" min="1" wire:model="value"
                                                    placeholder="0.00"
                                                    class="w-28 px-2 py-1 text-right border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button wire:click="agregarItem({{ $item->id }})"
                                                        class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                                        <x-heroicon-o-plus class="w-4 h-4" />
                                                        Agregar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                                No hay elementos para mostrar.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if ($items->hasPages())
                        <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Mostrando {{ $items->firstItem() }} a {{ $items->lastItem() }} de {{ $items->total() }}
                                    resultados
                                </div>
                                <div>
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--CARD DERECHO-->
            <div class="lg:col-span-7 xl:col-span-7 order-2 lg:order-2">
                <div class="space-y-6">
                    @if ($showAddItem)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-colors">
                            <!-- Header for Selected Items -->
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-ms font-bold text-gray-900 dark:text-white">Items Seleccionados</h3>
                            </div>

                            <div class="relative overflow-visible">
                                <div class="min-w-full overflow-x-auto">
                                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900">
                                            <tr>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Item
                                                </th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Cantidad Solicitada
                                                </th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($selectedItems as $selectedItem)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                                        {{ $selectedItem['name'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                                        <input type="number" min="1" wire:model.live="quantities.{{ $selectedItem['id'] }}"
                                                            class="w-28 px-2 py-1 text-right border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <button wire:click="removeItem({{ $selectedItem['id'] }})"
                                                                class="inline-flex items-center px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-xs font-medium rounded-full hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                                Quitar
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                                        No hay items seleccionados.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea wire:model.defer="transferForm.observations" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"></textarea>
                                    @error('transferForm.observations')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                    <div class="flex flex-col sm:flex-row justify-end mt-4 gap-3">
                                        <button wire:click="clearSelection" type="button"
                                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 text-white rounded-lg transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4 mr-2" />
                                            Limpiar Selección
                                        </button>
                                        <button wire:click="createTransferRequest" type="button"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg transition-colors">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4 mr-2" />
                                            Crear Solicitud
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>