<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sobrantes de Laboratorio</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Control de inventario en centímetros de los recortes de materiales que quedan en laboratorio tras realizar ensambles.
            </p>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Toolbar con Buscador, Filtro de Fechas, Paginación y Exportaciones -->
        <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                
                <!-- Búsqueda y Rango de Fechas (a la izquierda) -->
                <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Buscador -->
                    <div class="relative flex-1 min-w-[220px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Buscar por código, SKU o nombre..."
                            class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Filtro Fecha Desde -->
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Desde:</label>
                        <input wire:model.live="dateFrom" type="date"
                            class="text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2.5 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Filtro Fecha Hasta -->
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Hasta:</label>
                        <input wire:model.live="dateTo" type="date"
                            class="text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2.5 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Botón Limpiar Filtros -->
                    @if($search || $dateFrom !== now()->subMonth()->format('Y-m-d') || $dateTo !== now()->format('Y-m-d'))
                        <button type="button" wire:click="resetFilters"
                            title="Restablecer filtros"
                            class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar
                        </button>
                    @endif
                </div>

                <!-- Controles a la derecha: Mostrar y Exportaciones -->
                <div class="flex items-center justify-end gap-3 flex-wrap">
                    <!-- Selector de Paginación -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Mostrar:</label>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 py-1.5 px-2.5">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <!-- Botones de Exportación (Estilo monocromático y minimalista según regla) -->
                    <div class="flex items-center gap-1.5">
                        <!-- Excel -->
                        <button type="button" wire:click="exportExcel"
                            wire:loading.attr="disabled"
                            title="Exportar a Excel"
                            class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </button>
                        <!-- PDF -->
                        <button type="button" wire:click="exportPdf"
                            wire:loading.attr="disabled"
                            title="Exportar a PDF"
                            class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <!-- CSV -->
                        <button type="button" wire:click="exportCsv"
                            wire:loading.attr="disabled"
                            title="Exportar a CSV"
                            class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto min-h-[300px]">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider sm:pl-6">Código / SKU</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nombre del Insumo</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Sobrante Disponible</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Largo Original</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Fecha Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @forelse($leftovers as $left)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="whitespace-nowrap py-3.5 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                {{ $left->item->internal_code ?? $left->item->sku ?? 'N/A' }}
                            </td>
                            <td class="whitespace-normal px-3 py-3.5 text-sm text-gray-600 dark:text-gray-300">
                                {{ $left->item->name ?? $left->item->display_name ?? 'Insumo eliminado' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-center">
                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 text-xs font-bold text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-700/10">
                                    {{ number_format($left->available_cm, 0) }} cm
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-center text-gray-500 dark:text-gray-400">
                                @if($left->item && $left->item->dimensions && $left->item->dimensions->long > 0)
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ number_format($left->item->dimensions->long, 0) }} cm</span>
                                @else
                                    <span class="text-xs italic text-gray-400">No definido</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-xs text-center text-gray-500 dark:text-gray-400">
                                {{ $left->created_at ? $left->created_at->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    No se encontraron sobrantes registrados con los filtros seleccionados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($leftovers->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $leftovers->links() }}
            </div>
        @endif
    </div>
</div>
