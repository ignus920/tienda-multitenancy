<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Solicitudes de Garantía (Chatbot)</h1>
            <p class="text-gray-500 text-sm mt-1">Bandeja de entrada de requerimientos autogestionados por clientes.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col xl:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">Bandeja de Entrada <span class="text-gray-400 font-medium">({{ $requests->total() }})</span></h2>
            
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full xl:w-auto">
                <input wire:model.live="startDate" type="date" class="w-full sm:w-auto text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" title="Fecha Inicio">
                
                <input wire:model.live="endDate" type="date" class="w-full sm:w-auto text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" title="Fecha Fin">
                
                <select wire:model.live="statusFilter" class="w-full sm:w-auto text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="all">Todos los Estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="processed">Procesada</option>
                    <option value="rejected">Rechazada</option>
                </select>

                <input wire:model.live.debounce.300ms="search" type="text" class="w-full sm:w-64 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Buscar Radicado, Ref, Prod...">

                @if($search !== '' || $startDate !== '' || $endDate !== '' || $statusFilter !== 'all')
                    <button wire:click="clearFilters" class="p-2 text-gray-500 hover:text-red-500 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm" title="Limpiar Filtros">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>

            <!-- Botones de Exportación (Regla: fondo blanco, bordes grises suaves, iconos trazo fino gris) -->
            <div class="flex items-center gap-2">
                <button wire:click="exportExcel" class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                    </svg>
                    Excel
                </button>
                <button wire:click="exportPdf" class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                    </svg>
                    PDF
                </button>
            </div>
        </header>

        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-gray-300">
                    <thead class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-sm">
                        <tr>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Fecha</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Empresa</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Radicado</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Ref / Factura</div></th>
                            <th class="p-2"><div class="font-semibold text-left">Productos</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Estado</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Acciones</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($requests as $request)
                            <tr>
                                <td class="p-2 whitespace-nowrap text-gray-500">
                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="p-2 whitespace-nowrap">
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $request->company_name }}</div>
                                <td class="p-2 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                        {{ $request->tracking_code ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-2 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $request->reference_number }}
                                    </span>
                                </td>
                                <td class="p-2">
                                    <div class="text-gray-500 max-w-xs truncate" title="{{ $request->product_details }}">
                                        {{ $request->product_details }}
                                    </div>
                                </td>
                                <td class="p-2 whitespace-nowrap text-center">
                                    @if($request->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pendiente
                                        </span>
                                    @elseif($request->status === 'processed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Procesada
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2 whitespace-nowrap text-center">
                                    @if($request->status === 'pending')
                                        <button wire:click="processRequest({{ $request->id }})" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Abrir Caso
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400">Enlazada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">No hay solicitudes pendientes desde el chatbot.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
