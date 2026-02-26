<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <!-- Status Summary -->
	<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
		@foreach($this->status as $stat)
            <button wire:click="putFilter({{ $stat->{'id'} }})" class="text-left transition-transform transform hover:scale-105">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex items-center p-4 border {{ $filterStatus == $stat->{'id'} ? 'border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="flex-shrink-0 {{ $filterStatus == $stat->{'id'} ? 'bg-indigo-600' : 'bg-indigo-100 dark:bg-indigo-900' }} rounded-lg p-3 mr-4 transition-colors">
                        <x-heroicon-o-document-text class="w-8 h-8 {{ $filterStatus == $stat->{'id'} ? 'text-white' : 'text-indigo-600 dark:text-indigo-400' }}" />
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400 font-semibold text-sm">{{ $stat->{'Nombre del Estado'} }}</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $stat->{'cant'} }}
                        </div>
                    </div>
                </div>
            </button>
		@endforeach
	</div>

    <!-- DataTable Card -->
    <div class="max-w-12xl mx-auto">
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
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por item o código..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                    </div>

                    <div class="flex-1">
                        <div class="w-full sm:w-48">
                            @livewire('selects.generic-select', [
                            'selectedValue' => null,
                            'items' => $labels,
                            'name' => 'selectedLabel',
                            'placeholder' => $selectedLabelName,
                            'label' => '',
                            'required' => false,
                            'showLabel' => false,
                            'class' => 'block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                            'eventName' => 'labelSelected',
                            'displayField' => 'name',
                            'valueField' => 'id',
                            ], key('label-select-' . now()->timestamp))
                            @error('selectedLabel') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Controles -->
                    <div class="flex items-center gap-3">
                        <!-- Registros por página -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                            <select wire:model.live="perPage"
                                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 px-3 py-1">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Item
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Factory Ref
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                $ Last
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Qty Ordered
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Label
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Quoted price
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Coment
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Action
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Qty Shipped
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->orders as $order)
                            <tr wire:key="order-{{ $order->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $order->item}}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->factory_ref ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->exw ?? '0.00' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $order->qty_requested }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->label ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400"></td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <div x-data="{ comment: '' }" class="flex items-center gap-2 max-w-xs mx-auto">
                                        <input type="text" 
                                            x-model="comment"
                                            @change="$wire.saveComment({{ $order->id }}, comment)"
                                            @keydown.enter="$wire.saveComment({{ $order->id }}, comment)"
                                            class="block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Añadir comentario...">
                                        <button 
                                            @click="$wire.viewHistoryComment({{ $order->id }})"
                                            class="inline-flex items-center justify-center p-1.5 border border-transparent rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $order->translated_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->qty_shipped ?? 0 }}
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-4 text-gray-400 dark:text-gray-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium">No se encontraron registros</p>
                                        <p class="text-sm">
                                            {{ $search ? 'Intenta ajustar tu búsqueda' : 'No hay órdenes disponibles en este momento' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($this->orders->hasPages())
            <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando {{ $this->orders->firstItem() }} a {{ $this->orders->lastItem() }} de {{ $this->orders->total() }}
                        resultados
                    </div>
                    <div>
                        {{ $this->orders->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
