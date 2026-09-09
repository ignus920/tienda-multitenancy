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
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Bandeja de Entrada <span class="text-gray-400 font-medium">({{ $requests->total() }})</span></h2>
            
            <div class="w-1/3">
                <input wire:model.live.debounce.300ms="search" type="text" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Buscar por OP o Empresa...">
            </div>
        </header>

        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-gray-300">
                    <thead class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-sm">
                        <tr>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Fecha</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Empresa</div></th>
                            <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Ref / OP</div></th>
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
                                        <button wire:click="processRequest({{ $request->id }})" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
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
