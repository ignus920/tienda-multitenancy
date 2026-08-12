<div>
    @if($isOpen)
        <!-- Backdrop del modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 overflow-y-auto">
            <!-- Contenedor del Modal -->
            <div class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col my-8" style="max-height: 90vh;">
                
                <!-- Cabecera -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            Historial de {{ $type === 'quarantine' ? 'Cuarentena' : 'Vitrina / Exhibición' }}
                        </h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $productName }}</p>
                    </div>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo -->
                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <!-- Filtros -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-gray-50 p-4 rounded-lg">
                        <!-- Buscador -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Buscar</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Justificación o usuario..." 
                                    class="w-full pl-8 pr-3 py-1.5 text-sm bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha Inicio -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Desde</label>
                            <input type="date" wire:model.live="startDate" 
                                class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Hasta</label>
                            <input type="date" wire:model.live="endDate" 
                                class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Tabla de Resultados -->
                    <div class="border border-gray-100 rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs font-semibold uppercase">
                                <tr>
                                    <th class="px-4 py-3">Fecha / Hora</th>
                                    <th class="px-4 py-3 text-center">Tipo</th>
                                    <th class="px-4 py-3 text-center">Cantidad</th>
                                    <th class="px-4 py-3">Justificación</th>
                                    <th class="px-4 py-3">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @forelse($movements as $m)
                                    @php
                                        $isAdd = $m->quantity > 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y h:i A') }}
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $isAdd ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                                {{ $isAdd ? 'Ingreso' : 'Liberación' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold {{ $isAdd ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $isAdd ? '+' : '' }}{{ $m->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-xs font-medium max-w-xs break-words">
                                            {{ $m->justification }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                                            <div>
                                                <p class="font-semibold text-gray-800">{{ $m->user?->name ?? 'Usuario Sistema' }}</p>
                                                <p class="text-gray-400 text-[10px]">{{ $m->user?->email }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                            <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <p class="text-sm font-semibold">No se encontraron movimientos registrados</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Intenta cambiando los parámetros de búsqueda o fechas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($movements->hasPages())
                        <div class="mt-4">
                            {{ $movements->links() }}
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                    <button wire:click="close" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none">
                        Cerrar Historial
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
