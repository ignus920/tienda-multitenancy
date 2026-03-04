<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Mensajes de Alerta -->
        @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Campañas</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Gestión de registros</p>
                </div>
                <button wire:click="openModal" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    + CREAR NUEVO
                </button>
            </div>
        </div>

        <!-- DataTable -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar registros..." 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-gray-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300 font-medium">Mostrar:</label>
                            <select wire:model.live="perPage" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-1">
                            <button class="p-2 text-gray-500 hover:text-gray-700 border rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600"><x-heroicon-o-table-cells class="w-5 h-5"/></button>
                            <button class="p-2 text-gray-500 hover:text-gray-700 border rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600"><x-heroicon-o-document-text class="w-5 h-5"/></button>
                            <button class="p-2 text-gray-500 hover:text-gray-700 border rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600"><x-heroicon-o-document-arrow-down class="w-5 h-5"/></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th wire:click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-1">
                                    NOMBRE @if($sortField === 'name') <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $sortDirection === 'asc' ? 'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' : 'M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z' }}"></path></svg> @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DESCRIPCIÓN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">FECHAS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">REGALOS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ASIGNACIÓN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ESTADO</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $campaign->description }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="block">📅 Inicia: {{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : '-' }}</span>
                                <span class="block">🏁 Fin: {{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $campaign->gifts_sent }} / {{ $campaign->gift_quantity }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">{{ $campaign->assignment_type }}</td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="toggleCampaignStatus({{ $campaign->id }})"
                                    class="relative inline-flex h-4 w-8 items-center rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $campaign->status === 'activo' ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                                    <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-all duration-200 {{ $campaign->status === 'activo' ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500">
                                <button wire:click="edit({{ $campaign->id }})" class="inline-flex items-center px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-all duration-200 group">
                                    <x-heroicon-o-pencil-square class="w-4 h-4 mr-1.5 group-hover:scale-110 transition-transform"/>
                                    <span class="text-xs font-bold uppercase tracking-wider text-black dark:text-gray-100">Editar</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-megaphone class="w-12 h-12 mb-4 text-gray-300"/>
                                    <p class="text-lg font-medium">No se encontraron campañas</p>
                                    <p class="text-sm">Comienza creando un nuevo registro para tu gestión.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($campaigns->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $campaigns->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Formulario Campaña -->
    @if($isModalOpen)
    <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header Modal -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 rounded-t-lg">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                        {{ $campaignId ? 'Editar Campaña' : 'Crear Nueva Campaña' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Nombre de la Campaña <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Ej: Campaña de Verano">
                            @error('name') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo de Asignación -->
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Tipo de Asignación <span class="text-red-500">*</span></label>
                            <select wire:model="assignment_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">Seleccione un tipo</option>
                                <option value="todos">Todos los clientes</option>
                                <option value="manual">Manual (Selección específica)</option>
                                <option value="asesor">Por Asesor</option>
                                <option value="antiguos_frecuentes">Antiguos / Frecuentes</option>
                            </select>
                            @error('assignment_type') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Descripción</label>
                            <textarea wire:model="description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Detalles de la campaña..."></textarea>
                            @error('description') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fechas -->
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Fecha Inicio <span class="text-red-500">*</span></label>
                            <input wire:model="start_date" type="date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('start_date') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Fecha Fin <span class="text-red-500">*</span></label>
                            <input wire:model="end_date" type="date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('end_date') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Cantidad de Regalos -->
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-tight">Total Regalos Disponibles <span class="text-red-500">*</span></label>
                            <input wire:model="gift_quantity" type="number" min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Ej: 100">
                            @error('gift_quantity') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="closeModal" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold transition-colors uppercase text-xs tracking-widest">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-500 text-white rounded-lg font-bold shadow-md transition-all uppercase text-xs tracking-widest">
                            {{ $campaignId ? 'Guardar Cambios' : 'Crear Registro' }}
                        </button>
                    </div>
                </form>

                <!-- Sección: Clientes con regalo -->
                @if($campaignId)
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <h4 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase">Clientes con regalo:</h4>
                        
                        <div class="flex items-center gap-2">
                             <!-- Botones de exportación (Estéticos como en el ejemplo) -->
                             <div class="hidden sm:flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5 border border-gray-200 dark:border-gray-600">
                                <button class="px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-all">Copy</button>
                                <button class="px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-all">Excel</button>
                                <button class="px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-all">PDF</button>
                                <button class="px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-all">Print</button>
                            </div>

                            <div class="relative min-w-[200px]">
                                <input wire:model.live.debounce.300ms="customerSearch" type="text" placeholder="Buscar..." 
                                    class="w-full pl-3 pr-8 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 focus:outline-none placeholder-gray-400">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Entrega</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($deliveredCustomers as $index => $customer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $deliveredCustomers->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $customer->customer_name }}
                                        <br><small class="text-xs text-gray-500">{{ $customer->identification }}</small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-500 text-white uppercase tracking-tighter">
                                            Entregado {{ $customer->pivot->delivered_at ? \Carbon\Carbon::parse($customer->pivot->delivered_at)->format('Y-m-d') : '-' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-xs">
                                        No se registran entregas para esta campaña aún.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($deliveredCustomers->hasPages())
                    <div class="mt-4">
                        {{ $deliveredCustomers->links() }}
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
