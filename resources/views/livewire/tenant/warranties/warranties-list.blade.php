<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Garantías</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de reclamos de garantías de productos</p>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto">
        <!-- Dashboard de Contadores -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <!-- Pendiente Admin -->
            <div wire:click="applyFilter(1)" 
                 class="cursor-pointer bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md transition-all flex items-center gap-4 {{ $filterStatus === 1 ? 'ring-2 ring-yellow-500' : '' }}">
                <div class="w-12 h-12 flex-shrink-0 bg-yellow-500 rounded-lg flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-yellow-500/20">
                    {{ $countPending }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pendiente Admin</p>
                </div>
            </div>

            <!-- Laboratorio -->
            <div wire:click="applyFilter(2)" 
                 class="cursor-pointer bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md transition-all flex items-center gap-4 {{ $filterStatus === 2 ? 'ring-2 ring-red-500' : '' }}">
                <div class="w-12 h-12 flex-shrink-0 bg-red-500 rounded-lg flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-red-500/20">
                    {{ $countLab }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">En Laboratorio</p>
                </div>
            </div>

            <!-- Importaciones -->
            <div wire:click="applyFilter(3)" 
                 class="cursor-pointer bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md transition-all flex items-center gap-4 {{ $filterStatus === 3 ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="w-12 h-12 flex-shrink-0 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-indigo-500/20">
                    {{ $countImports }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">En Importaciones</p>
                </div>
            </div>

            <!-- Resuelto -->
            <div wire:click="applyFilter(4)" 
                 class="cursor-pointer bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md transition-all flex items-center gap-4 {{ $filterStatus === 4 ? 'ring-2 ring-green-500' : '' }}">
                <div class="w-12 h-12 flex-shrink-0 bg-green-500 rounded-lg flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-green-500/20">
                    {{ $countResolved }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Resueltas</p>
                </div>
            </div>
        </div>

        <!-- Barra de Herramientas y Filtros -->
        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-slate-700 transition-colors shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por OP, cliente o producto..." 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Desde:</span>
                        <input wire:model.live="dateFrom" type="date" class="block px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Hasta:</span>
                        <input wire:model.live="dateTo" type="date" class="block px-3 py-2 border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all">
                    </div>
                    
                    @if($filterStatus)
                    <button wire:click="applyFilter(null)" class="text-xs font-bold text-red-500 hover:text-red-600 uppercase">Limpiar Filtro</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"># Consecutivo</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pedido / OP</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Sol.</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-4 class-color text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($warranties as $warranty)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $warranty->consecutive }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">OP #{{ $warranty->remission->consecutive ?? $warranty->remission->id ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">{{ $warranty->created_at->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">{{ $warranty->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white truncate max-w-xs">{{ $warranty->remission->quote->customer->customer_name ?? 'Cliente Desconocido' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $warranty->status_color }}">
                                        {{ $warranty->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="$dispatch('openWarrantyDetail', { id: {{ $warranty->id }} })" 
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detalles / Procesar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                    No se encontraron solicitudes de garantía registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($warranties->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $warranties->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modales del flujo de Garantías -->
    @livewire('tenant.warranties.warranty-detail-modal')
</div>
