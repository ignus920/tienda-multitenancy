<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Confirmaciones de Inventario</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestiona y confirma las solicitudes de stock de los productos.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
            <!-- Búsqueda Rápida -->
            <div style="flex: 1.5; min-width: 200px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Búsqueda rápida</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input wire:model.live="search" type="text" 
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" 
                        placeholder="Buscar por producto o código...">
                </div>
            </div>

            <!-- Estado -->
            <div style="flex: 1; min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Estado</label>
                <select wire:model.live="status" 
                    class="block w-full py-2 pl-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    <option value="">Todos los estados</option>
                    <option value="1">Pendientes</option>
                    <option value="2">Confirmados</option>
                </select>
            </div>

            <!-- Desde -->
            <div style="flex: 1; min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">desde:</label>
                <input type="date" wire:model.live="filterDateFrom"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
            </div>

            <!-- Hasta -->
            <div style="flex: 1; min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">hasta:</label>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="filterDateTo"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    <button wire:click="clearFilters" title="Limpiar filtros"
                        class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cant. Solicitada</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Sistema</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($confirmations as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->item->name }}</div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $item->item->internal_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                {{ number_format($item->requested_quantity) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($item->system_stock) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $item->requester->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->requested_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status == 1)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        Pendiente
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Confirmado
                                    </span>
                                    <div class="text-[10px] text-gray-500 mt-1">Por: {{ $item->confirmer->name }}</div>
                                    @if($item->confirmation_observations)
                                        <div class="text-[10px] text-gray-400 mt-1 italic italic">"{{ $item->confirmation_observations }}"</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($item->status == 1)
                                    <div x-data="{ quantity: '', obs: '' }" class="flex items-center justify-end gap-2">
                                        <div class="flex flex-col gap-1">
                                            <input type="number" x-model="quantity" title="Cantidad"
                                                class="w-20 px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                                placeholder="Cant.">
                                            <input type="text" x-model="obs" title="Observaciones de confirmación"
                                                class="w-32 px-2 py-1 text-[10px] border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                                placeholder="Nota (opcional)">
                                        </div>
                                        <button @click="$wire.confirm({{ $item->id }}, quantity, obs)" 
                                            :disabled="!quantity"
                                            :class="!quantity ? 'opacity-30 cursor-not-allowed grayscale' : 'hover:bg-indigo-100 dark:hover:bg-indigo-900/50'"
                                            class="text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded-lg transition-all self-center">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                No se encontraron solicitudes de confirmación.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            {{ $confirmations->links() }}
        </div>
    </div>
</div>
