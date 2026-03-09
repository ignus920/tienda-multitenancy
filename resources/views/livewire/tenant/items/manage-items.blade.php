<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Mensajes de Alerta Globales -->
        @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">
                        {{ session('success') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="show = false" class="inline-flex text-green-500 hover:text-green-700 dark:hover:text-green-400 focus:outline-none">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">
                        {{ session('error') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="show = false" class="inline-flex text-red-500 hover:text-red-700 dark:hover:text-red-400 focus:outline-none">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Header -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Parámetros Items</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Gestion de registros</p>
                </div>
                <button wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Crear Nuevo
                </button>
            </div>
        </div>


        <!-- DataTable Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Búsqueda -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar registros..."
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
                        <x-export-buttons />
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Imagen</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Sku</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Codigo Interno</th>
                            <th wire:click="sortBy('name')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    @if($sortField === 'name')
                                    @if($sortDirection === 'asc')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z">
                                        </path>
                                    </svg>
                                    @else
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z">
                                        </path>
                                    </svg>
                                    @endif
                                    @endif
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tipo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Marca</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Casa</th>  
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Stock</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Precios</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Unidad de compra</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Unidad de consumo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Impuesto</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado</th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $it)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <img class="h-12 w-12 rounded-md object-cover border border-gray-300 dark:border-gray-600 mx-auto"
                                    src="{{ $it->getPrincipalThumbnailUrl() }}" alt="{{ $it->name }}">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->sku }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->internal_code ?? $it->internalCode ?? '' }}
                            </td>
                            <td class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $it->name }}
                                @if($it->generic)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase tracking-wide">
                                        Genérico
                                    </span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $it->type }}
                            </td>
                            <td class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->brand->name ?? 'SIN MARCA' }}
                            </td>
                            <td class="px-2 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $it->house->name ?? 'SIN CASA' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($it->inventoriable == 1)
                                    @if($it->invItemsStore->isNotEmpty())
                                        <button wire:click="openStockModal({{ $it->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            Ver Stock
                                            <span class="ml-1.5 px-2 py-0.5 bg-blue-200 dark:bg-blue-800 rounded-full text-xs font-bold">
                                                {{ $it->invItemsStore->sum('stock_items_store') }}
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic text-xs">Sin stock</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 italic text-xs">No maneja inventario</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($it->invValues->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($it->invValues->where('type', 'precio') as $value)
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                    {{ str_replace('Precio ', '', $value->label) }}:
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">${{ number_format($value->values, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 italic">Sin precios</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->purchasingUnit->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->consumptionUnit->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $it->tax->name ?? 'Sin impuesto' }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900 dark:text-white">
                                <!-- Estado Toggle -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <!-- Toggle Switch -->
                                        <button type="button" wire:click="toggleItemStatus({{ $it->id }})"
                                            class="relative inline-flex h-4 w-8 items-center rounded-full transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 hover:shadow-md {{ $it->status ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500' }}"
                                            role="switch" aria-checked="{{ $it->status ? 'true' : 'false' }}"
                                            aria-label="Toggle company status">
                                            <span
                                                class="inline-block h-3 w-3 transform rounded-full bg-white shadow-sm transition-all duration-200 ease-in-out {{ $it->status ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <!-- Menú de tres puntos con Alpine.js -->
                                <div x-data="{ open: false }" @click.outside="open = false"
                                    class="relative inline-block text-left static">
                                    <button @click="open = !open" x-ref="button"
                                        class="flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition-colors">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>

                                    <!-- Menú desplegable -->
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95" @click="open = false"
                                        class="origin-top-left fixed left-auto right-auto mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 z-[60]"
                                        x-anchor="$refs.button"
                                        style="display: none;">

                                        <div class="py-1" role="menu" aria-orientation="vertical">
                                            <button wire:click="edit({{ $it->id }})"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-800 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors flex items-center">
                                                <x-heroicon-o-pencil-square class="w-6 h-6" />
                                                Editar
                                            </button>
                                            <button @click.stop="$dispatch('openTicketModal', { productId: {{ $it->id }} }); open = false"
                                                class="w-full text-left px-4 py-2 text-sm text-indigo-800 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                                </svg>
                                                Solicitud Soporte
                                            </button>
                                            <button wire:click="openLocationsModal({{ $it->id }})"
                                                class="w-full text-left px-4 py-2 text-sm text-orange-800 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                                Ubicaciones
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-400 dark:text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron registros</p>
                                    <p class="text-sm">{{ $search ? 'Intenta ajustar tu búsqueda' : 'Comienza creando un
                                        nuevo registro' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($items->hasPages())
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

    <!-- Modal Registro Item-->
    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
        x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $item_id ? 'Editar Item' : 'Crear Item' }}
                        </h3>
                    </div>
                    <button wire:click="cancel"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>
                
                <!-- Sistema de Pestañas - Solo visible después de guardar cuando hay pestañas adicionales -->
                @if($item_id && ($this->canUseImports() || $type == 'PRODUCIDO'))
                    <div class="px-6 pt-4">
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                                <!-- Pestaña Información General -->
                                <button type="button" wire:click="$set('showProductionSection', false)"
                                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none"
                                    :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': !@js($showProductionSection),
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': @js($showProductionSection)}">
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-information-circle class="w-5 h-5" />
                                        <span>Información General</span>
                                    </div>
                                </button>

                                <!-- Pestaña Importado - Solo si módulo importaciones activo y tipo IMPORTADO -->
                                @if($this->canUseImports() && $type == 'IMPORTADO')
                                <button type="button" wire:click="showImportSection({{$item_id}})"
                                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none"
                                    :class="{'border-amber-500 text-amber-600 dark:text-amber-400': @js($showProductionSection),
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': !@js($showProductionSection)}">
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-truck class="w-5 h-5" />
                                        <span>Importado</span>
                                    </div>
                                </button>
                                @endif

                                <!-- Pestaña Proceso de Producción - Solo si tipo PRODUCIDO -->
                                @if($type == 'PRODUCIDO')
                                <button type="button" wire:click="$set('showProductionSection', true)"
                                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none"
                                    :class="{'border-amber-500 text-amber-600 dark:text-amber-400': @js($showProductionSection),
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': !@js($showProductionSection)}">
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                                        <span>Proceso de Producción</span>
                                    </div>
                                </button>
                                @endif
                            </nav>
                        </div>
                    </div>
                @endif

                <!-- Contenido según la pestaña activa -->
                @if(!$item_id || !$showProductionSection)
                <!-- Form -->
                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="space-y-6">
                        @livewire('tenant.items.categories', [
                            'categoryId' => $category_id,
                            'name' => 'category_id',
                            'label' => 'Categoría',
                            'placeholder' => 'Seleccione una categoria',
                            'required' => true,
                            'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                            dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                            focus:ring-indigo-500 focus:border-indigo-500'
                        ])
                        @error('category_id') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre
                                <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" id="name"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Ingrese nombre del producto">
                            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3 grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código
                                    interno<span class="text-red-500">*</span></label>
                                <input wire:model.live.debounce.400ms="internal_code" type="text" id="internal_code"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Ingrese el código interno">
                                @error('internal_code') <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror

                                @if($internal_codeExists && !$errors->has('internal_code'))
                                <span class="text-red-500 text-sm">
                                    Este código interno ya está registrado
                                </span>
                                @endif
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SKU</label>
                                <input wire:model.live.debounce.400ms="sku" type="text" id="sku"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Ingrese el sku">
                                @error('sku') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                                @if($internal_codeExists && !$errors->has('internal_code'))
                                <span class="text-red-500 text-sm">
                                    Este SKU ya está registrado
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3 grid grid-cols-2 gap-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo <span class="text-red-500">*</span></label>
                                <select wire:model.live="type" {{ $disabled ? 'disabled' : '' }}
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Seleccione --</option>
                                    @foreach($types as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Impuesto <span class="text-red-500">*</span></label>
                                <select wire:model="tax"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Seleccione --</option>
                                    @foreach($this->taxes as $taxItem)
                                    <option value="{{ $taxItem->id }}">{{ $taxItem->name }}</option>
                                    @endforeach
                                </select>
                                @error('tax') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($showCommand)
                            @if ($type == 'PRODUCIDO')  
                                @livewire('tenant.items.command', [
                                'commandId' => $commandId,
                                'name' => 'commandId',
                                'label' => 'Comanda',
                                'placeholder' => 'Seleccione una comanda',
                                'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                                dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                                focus:ring-indigo-500 focus:border-indigo-500'
                                ])
                            @endif
                        @endif

                        @livewire('tenant.items.brand',[
                            'brandId' => $brandId,
                            'name' => 'brandId',
                            'label' => 'Marca',
                            'placeholder' => 'Seleccione una marca',
                            'required' => true,
                            'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                            dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                            focus:ring-indigo-500 focus:border-indigo-500'
                        ])
                        @error('brandId') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror

                        @livewire('tenant.items.house',[
                            'houseId' => $houseId,
                            'name' => 'houseId',
                            'label' => 'Casa',
                            'placeholder' => 'Seleccione una casa',
                            'required' => true,
                            'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                            dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                            focus:ring-indigo-500 focus:border-indigo-500'
                        ])
                        @error('houseId') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @livewire('tenant.items.purchasing-unit', [
                            'purchaseUnitId' => $purchase_unit,
                            'name' => 'purchase_unit',
                            'label' => 'Unidad de compra',
                            'placeholder' => 'Seleccione una unidad de compra',
                            'required' => true,
                            'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                            dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                            focus:ring-indigo-500 focus:border-indigo-500'
                            ])
                            @error('purchase_unit') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror

                            @livewire('tenant.items.consumption-unit', [
                            'consumptionUnitId' => $consumption_unit,
                            'name' => 'consumption_unit',
                            'label' => 'Unidad de consumo',
                            'placeholder' => 'Seleccione una unidad de consumo',
                            'required' => true,
                            'class' => 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white
                            dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2
                            focus:ring-indigo-500 focus:border-indigo-500'
                            ])
                            @error('consumption_unit') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3 grid grid-cols-2 gap-2">
                            @if($this->manageSerials())
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maneja
                                    Serial</label>
                                <select wire:model="handles_serial"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Seleccione --</option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                                @error('handles_serial') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maneja
                                    Inventario</label>
                                <select wire:model="inventoriable"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Seleccione --</option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                                @error('inventoriable') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                            <textarea wire:model="description"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                rows="3">
                            </textarea>
                        </div>

                        <!-- Mensajes dentro del modal -->
                        <div class="px-6 pt-4">
                            <!-- Mensaje de éxito general -->
                            @if (session()->has('message'))
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ session('message') }}
                                </div>
                            </div>
                            @endif

                            <!-- Mensaje de error general -->
                            @if (session()->has('error'))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            </div>
                            @endif

                            <!-- Mensaje de sincronización exitosa -->
                            @if (session()->has('sync_message'))
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ session('sync_message') }}
                                </div>
                            </div>
                            @endif

                            <!-- Mensaje de advertencia de sincronización -->
                            @if (session()->has('sync_warning'))
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 19c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <span>{{ session('sync_warning') }}</span>
                                    </div>
                                    <button wire:click="cancel" class="ml-3 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endif

                            <!-- Mensaje de error de sincronización -->
                            @if (session()->has('sync_error'))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ session('sync_error') }}</span>
                                    </div>
                                    <button wire:click="cancel" class="ml-3 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="border-t border-gray-300 my-6"></div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Valores</h3>

                        {{-- Si el item es NUEVO: mostrar inputs para agregar valores --}}
                        @if (!$item_id)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Etiqueta</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            $staticValues = [
                                                ['label' => 'Costo Inicial', 'type' => 'Costo'],
                                                ['label' => 'Costo', 'type' => 'Costo'],
                                                ['label' => 'Precio Base', 'type' => 'Precio'],
                                                ['label' => 'Precio Regular', 'type' => 'Precio'],
                                                ['label' => 'Precio Crédito', 'type' => 'Precio'],
                                            ];
                                        @endphp
                                        @foreach ($staticValues as $index => $staticValue)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                    {{ $staticValue['label'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $staticValue['type'] === 'Costo' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                                        {{ $staticValue['type'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right">
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        min="0"
                                                        wire:model="tempValues.{{ $staticValue['label'] }}"
                                                        placeholder="0.00"
                                                        class="w-28 px-2 py-1 text-right border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                    >
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            {{-- Si el item YA EXISTE: mostrar botón para abrir modal --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <button type="button" wire:click="openValuesModal({{ $item_id }})"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                        title="Gestionar valores del item">
                                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2" />
                                        Gestionar Valores
                                    </button>
                                </div>
                            </div>
                        @endif                        

                        <!-- Sección de Galería de Imágenes -->
                        @if ($item_id)
                        <div class="border-t border-gray-300 dark:border-gray-600 my-6"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Imágenes del Producto
                            </h3>
                            <button type="button" @click="$dispatch('openImageModal', { productId: {{ $item_id }} })"
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 text-xs font-bold rounded-lg hover:bg-indigo-200 transition-colors">
                                Gestionar Galería
                            </button>
                        </div>
                        @endif

                        <!-- Mensajes -->
                        @if ($messageValues)
                        <div x-data="{ showAlert: true }" x-show="showAlert"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-start">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-700" />
                                <div class="flex-1">
                                    <p class="text-sm text-red-700 dark:text-red-400">{{ $messageValues }}</p>
                                </div>
                                <button type="button" @click="showAlert = false"
                                    class="ml-3 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endif

                        <div
                            class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" wire:click="cancel"
                                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors order-2 sm:order-1">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors order-1 sm:order-2">
                                <span>{{ $item_id ? 'Actualizar' : 'Crear' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
                @elseif($item_id && $showProductionSection)
                <!-- PESTAÑA 2: Contenido según el tipo del item -->
                @if($type == 'IMPORTADO')
                @livewire('tenant.imports.import-reg-item', ['itemId' => $item_id], key($item_id))
                @elseif($type == 'PRODUCIDO')
                @livewire('tenant.production.process-reg-item', ['itemId' => $item_id], key('prod-'.$item_id))
                @endif
                @endif

            </div>
        </div>
    </div>
    @endif

    <!-- Modal Values -->
    @if($showValuesModal)
    @livewire('tenant.items.manage-values', ['ItemId' => $item_id], key($item_id))
    @endif

    <!-- Modal Ubicaciones -->
    @if($showLocationsModal)
    @livewire('tenant.items.manage-locations', ['itemId' => $selectedItemId], key('locations-'.$selectedItemId))
    @endif

    <!-- Modal Stock por Sucursales y Bodegas -->
    @if($showStockModal)
    <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
        x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Stock por Sucursales y Bodegas
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Visualiza el stock disponible en cada bodega de las sucursales
                            </p>
                            
                            @if($selectedItemName)
                            <div class="mt-4 p-3 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-bold text-gray-900 dark:text-white truncate">
                                            {{ $selectedItemName }}
                                        </p>
                                        @if($selectedItemSku)
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                                </svg>
                                                SKU: {{ $selectedItemSku }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <button wire:click="closeStockModal"
                            class="ml-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    @if(empty($stockByWarehouse))
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No hay stock registrado</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Este item no tiene stock en ninguna bodega</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($stockByWarehouse as $warehouseData)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                    <!-- Warehouse Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                                    {{ $warehouseData['warehouse_name'] }}
                                                </h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Sucursal ID: {{ $warehouseData['warehouse_id'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full">
                                            {{ count($warehouseData['stores']) }} {{ count($warehouseData['stores']) == 1 ? 'Bodega' : 'Bodegas' }}
                                        </span>
                                    </div>

                                    <!-- Stores Table -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                            <thead class="bg-gray-100 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Bodega
                                                    </th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Stock Actual
                                                    </th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Stock Mínimo
                                                    </th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Stock Máximo
                                                    </th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Estado
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                @foreach($warehouseData['stores'] as $store)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                                </svg>
                                                                {{ $store['store_name'] }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                                @if($store['stock'] <= 0)
                                                                    bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                                                @elseif($store['stock'] <= $store['stock_min'])
                                                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                                                @else
                                                                    bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                                                @endif
                                                            ">
                                                                {{ number_format($store['stock'], 0) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                                            {{ number_format($store['stock_min'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                                            {{ number_format($store['stock_max'], 0) }}
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if($store['stock'] <= 0)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    Sin Stock
                                                                </span>
                                                            @elseif($store['stock'] <= $store['stock_min'])
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    Bajo
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    Disponible
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end">
                    <button wire:click="closeStockModal"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 border border-transparent rounded-lg font-medium text-sm text-white transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
