<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="max-w-12xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Información de la empresa') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{-- {{ __("Información basica de la empresa.") }} --}}
                    </p>
                </header>

                <form wire:submit="updateCompanyInformation" class="mt-6 space-y-6">
                    <div class="space-y-6">
                        @forelse ($companyInformation as $ci)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="businessName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre</label>
                                    <input wire:model="businessName" type="text" id="businessName" value="{{ $ci->businessName }}" readonly
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
                                    <input wire:model="address" type="text" id="address" value="{{ $ci->address }}" readonly
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ciudad</label>
                                    <input wire:model="city" type="text" id="city" value="{{ $ci->city }}" readonly
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="acronym" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo documento</label>
                                    <input wire:model="acronym" type="text" id="acronym" value="{{ $ci->acronym }}" readonly
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="identification" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Identificación</label>
                                    <input wire:model="identification" type="text" id="identification" value="{{ $ci->identification }}" readonly
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            @empty
                                <p class="text-gray-500">No hay información de empresa disponible</p>
                        @endforelse
                    </div>
                </form>
            </div>
        </div>
        <div class="p-4 sm:p-8 mt-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="max-w-12xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Facturación') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{-- {{ __("Información basica de la empresa.") }} --}}
                    </p>
                </header>

                <!-- Messages -->
                @if (session()->has('message'))
                <div class="mx-6 mt-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('message') }}
                    </div>
                </div>
                @endif

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
                                <!-- Botones de exportar -->
                                <x-export-buttons />
                            </div>
                            <div class="">
                                <button wire:click="createInvoiceInformation" wire:loading.attr="disabled"
                                    class="inline-flex justify-center items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 relative whitespace-nowrap">
                                    <span wire:loading.remove wire:target="createInvoiceInformation" class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Crear
                                    </span>
                                    <span wire:loading wire:target="createInvoiceInformation" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Cargando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th wire:click="sortBy('id')"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none">
                                        <div class="flex items-center gap-1">
                                            ID
                                            @if($sortField === 'id')
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
                                    <th wire:click="sortBy('token')"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none">
                                        <div class="flex items-center gap-1">.
                                            Token
                                            @if($sortField === 'token')
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
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Sucursal
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Númeración
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Facturador
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($infoInvoices as $info)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $info->id}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $info->token}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{$info->warehouseRap->name}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $info->numeracion}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $info->facturador }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
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
                                                        <button wire:click="editCompanyInvoice({{ $info->id }})"
                                                            class="w-full text-left px-4 py-2 text-sm text-yellow-800 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors flex items-center">
                                                            <x-heroicon-o-pencil-square class="w-6 h-6" />
                                                            Editar
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
                    @if ($infoInvoices->hasPages())
                        <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Mostrando {{ $brands->firstItem() }} a {{ $brands->lastItem() }} de {{ $brands->total() }}
                                    resultados
                                </div>
                                <div>
                                    {{ $infoInvoices->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($showModal)
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
                                {{ $cnfInvoiceId ? 'Editar Facturación' : 'Crear Facturación' }}
                            </h3>
                        </div>
                        <button wire:click="cancel"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                    <form wire:submit="saveCompanyInvoice" class="p-6 space-y-6">
                        <div class="mb-3">
                            <label for="token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Token</label>
                            <input wire:model="token" type="text" id="token" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('token') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            
                            <label for="token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sucursal</label>
                            <select wire:model.live="warehouseId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('warehouseId') border-red-500 @enderror">
                                <option value="">Seleccionar sucursal</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }} - {{ $warehouse->company->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouseId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            
                            @if($warehouseIdExists && !$errors->has('warehouseId'))
                            <span class="text-red-500 text-sm">
                                Esta sucursal ya esta registrada.
                            </span>
                            @endif
                            <div class="mt-2 mb-3 grid grid-cols-2 gap-2">
                                <div>
                                    <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Númeración</label>
                                    <input wire:model="numeration" type="number" id="numeration" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('numeration') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" wire:click="cancel"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors order-2 sm:order-1">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                                    <span wire:loading.remove wire:target="save">
                                        {{ $cnfInvoiceId ? 'Actualizar' : 'Crear' }}
                                    </span>
                                    <span wire:loading wire:target="save" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
