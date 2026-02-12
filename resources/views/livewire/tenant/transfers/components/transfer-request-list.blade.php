<div class="space-y-4">
    <!-- Error Message -->
    @if($errorMessage)
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if($successMessage)
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ $successMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Search and Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Search -->
        <div class="flex-1 max-w-md">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por fecha, bodega, observaciones..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Per Page -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
            <select wire:model.live="perPage"
                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left">
                        <button wire:click="sortBy('date')" class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                            Fecha
                            @if($sortField === 'date')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left">
                        <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                            Tipo/Estado
                            @if($sortField === 'status')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Sucursal/Store
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Quote ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->transferRequests as $request)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $request->formatted_date }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($request->status === 'REGISTRADO')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Registrado
                                </span>
                            @elseif($request->status === 'EN PROGRESO')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    En Progreso
                                </span>
                            @elseif($request->status === 'ENTREGADO')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Entregado
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    {{ $request->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $request->store->warehouse->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $request->store->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $request->quoteId ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <!-- Botón Gestionar -->
                            <button wire:click="openDetailsModal({{ $request->id }})"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Gestionar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">
                                    No se encontraron solicitudes de transferencia
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($this->transferRequests->hasPages())
        <div class="mt-4">
            {{ $this->transferRequests->links() }}
        </div>
    @endif

    <!-- Details Modal -->
    <div x-data="{ show: @entangle('showDetailsModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="$wire.closeDetailsModal()"></div>
        
        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="$wire.closeDetailsModal()"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Detalles de Solicitud de Transferencia
                          <span class="text-sm text-gray-500 dark:text-white font-mono">
                                    #{{ str_pad($requestDetails['id'] ?? '', 6, '0', STR_PAD_LEFT) }}
                           </span>
                    </h3>
                    <button @click="$wire.closeDetailsModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Content -->
                @if(!empty($requestDetails))
                    <div class="space-y-4">
                      
                        <!-- Tipo/Estado -->
                        <div class="flex items-start">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo/Estado:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $requestDetails['status_badge_class'] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                    {{ $requestDetails['status'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <!-- Store -->
                        <div class="flex items-start">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Destino:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{$requestDetails['warehouse'] ?? 'N/A' }} - {{ $requestDetails['store'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="flex items-start">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones:</span>
                            </div>
                            <div class="w-2/3">
                                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                                    {{ $requestDetails['observations'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                        <!-- Warehouse Origin Selector -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega de Origen (Desde donde se envían los items)
                            </label>
                            <select wire:model.live="selectedDestinationStoreId"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccione bodega de origen</option>
                                @foreach($this->availableStores as $store)
                                    <option value="{{ $store['id'] }}">{{ $store['display_name'] }}</option>
                                @endforeach
                            </select>
                            
                            @if(!$selectedDestinationStoreId)
                                <p class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Seleccione una bodega de origen para habilitar las transferencias
                                </p>
                            @else
                                <p class="mt-2 text-xs text-green-600 dark:text-green-400">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Los items se enviarán desde esta bodega hacia: <strong>{{ $requestDetails['warehouse'] ?? 'N/A' }} - {{ $requestDetails['store'] ?? 'N/A' }}</strong>
                                </p>
                            @endif
                        </div>

                        <!-- Items Table -->
                        <div class="mt-6">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Items de la Solicitud</h4>
                            
                            @if(!empty($requestDetails['items']) && count($requestDetails['items']) > 0)
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Producto
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Cantidad Pedida
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Cantidad Enviada
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Estado
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Progreso
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($requestDetails['items'] as $item)
                                                @php
                                                    $progress = $item['quantity'] > 0 ? ($item['quantitySend'] / $item['quantity']) * 100 : 0;
                                                    $isComplete = $item['quantitySend'] >= $item['quantity'];
                                                    $isPartial = $item['quantitySend'] > 0 && $item['quantitySend'] < $item['quantity'];
                                                    $isPending = $item['quantitySend'] == 0;
                                                @endphp
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" wire:key="item-{{ $item['id'] }}-{{ $selectedDestinationStoreId ?? 'none' }}">
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                        {{ $item['itemName'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                        {{ $item['quantity'] }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                        {{ $item['quantitySend'] }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($isComplete)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Completo
                                                            </span>
                                                        @elseif($isPartial)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Parcial
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Pendiente
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                                <div class="h-2 rounded-full transition-all {{ $isComplete ? 'bg-green-500' : ($isPartial ? 'bg-yellow-500' : 'bg-gray-400') }}" 
                                                                     style="width: {{ $progress }}%"></div>
                                                            </div>
                                                            <span class="text-xs text-gray-600 dark:text-gray-400 min-w-[3rem] text-right">
                                                                {{ number_format($progress, 0) }}%
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($selectedDestinationStoreId && !$isComplete)
                                                            <button wire:click="openTransferModal({{ $item['id'] }})"
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                                </svg>
                                                                Transferir
                                                            </button>
                                                        @else
                                                            <button disabled
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors text-gray-400 bg-gray-200 dark:bg-gray-700 cursor-not-allowed">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                                </svg>
                                                                Transferir
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                        No hay items registrados en esta solicitud
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>


                        <!-- Divider -->
                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                        <!-- Timestamps -->
                        <div class="flex items-start">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $requestDetails['created_at'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-1/3">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Actualizado:</span>
                            </div>
                            <div class="w-2/3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $requestDetails['updated_at'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Footer -->
                <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="$wire.closeDetailsModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Modal (Secondary Modal) -->
    <div x-data="{ show: @entangle('showTransferModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] overflow-y-auto"
        style="display: none;">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="$wire.closeTransferModal()"></div>
        
        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="$wire.closeTransferModal()"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Transferir Item
                    </h3>
                    <button @click="$wire.closeTransferModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Content -->
                @if(!empty($itemTransferData))
                    <div class="space-y-4">
                        <!-- Item Info -->
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $itemTransferData['itemName'] }}
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad pedida:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                        {{ $itemTransferData['requestedQuantity'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Ya enviado:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                        {{ $itemTransferData['sentQuantity'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Falta enviar:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                        {{ $itemTransferData['remainingToSend'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Stock físico:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                        {{ $itemTransferData['physicalStock'] ?? 0 }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Comprometido:</span>
                                    <span class="ml-2 font-medium text-orange-600 dark:text-orange-400">
                                        {{ $itemTransferData['committedQuantity'] ?? 0 }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Stock disponible:</span>
                                    <span class="ml-2 font-medium {{ $itemTransferData['availableStock'] < $itemTransferData['remainingToSend'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $itemTransferData['availableStock'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Warning -->
                        @if($itemTransferData['availableStock'] < $itemTransferData['remainingToSend'])
                            <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            Stock insuficiente. Disponible: {{ $itemTransferData['availableStock'] }} unidades
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Quantity Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad a transferir
                            </label>
                            <input type="number" 
                                wire:model="transferQuantity"
                                min="1"
                                max="{{ $itemTransferData['maxTransferable'] }}"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Máximo transferible: {{ $itemTransferData['maxTransferable'] }} unidades
                            </p>
                        </div>

                        <!-- Quick Selection Buttons -->
                        <div class="flex gap-2">
                            <button wire:click="setQuantityToAvailable"
                                type="button"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Todo disponible
                            </button>
                            <button wire:click="setQuantityToRequested"
                                type="button"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Cantidad pedida
                            </button>
                        </div>
                    </div>
                @endif
                
                <!-- Footer -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="$wire.closeTransferModal()"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="executeTransfer"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Confirmar Transferencia
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
