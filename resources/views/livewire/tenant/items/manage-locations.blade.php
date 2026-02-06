<div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
    x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto"
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
                        Gestionar Ubicaciones
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Item: <span class="font-medium">{{ $itemName }}</span>
                    </p>
                </div>
                <button wire:click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Mensajes -->
                @if (session()->has('location_message'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('location_message') }}
                    </div>
                </div>
                @endif

                @if (session()->has('location_error'))
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('location_error') }}
                    </div>
                </div>
                @endif

                <!-- Botón para agregar nueva ubicación -->
                <div class="mb-4">
                    <button wire:click="toggleCreateForm"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ $showCreateForm ? 'Cancelar' : 'Agregar Nueva Ubicación' }}
                    </button>
                </div>

                <!-- Formulario para crear nueva ubicación -->
                @if($showCreateForm)
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Nueva Ubicación</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Select de Bodega -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="selectedStoreId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Seleccione una bodega</option>
                                @foreach($this->stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->warehouseName }} - {{ $store->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedStoreId') 
                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Input de Nombre de Ubicación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre de Ubicación <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="newLocationName" type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Ej: Estante A1, Pasillo 3">
                            @error('newLocationName') 
                            <span class="text-red-600 text-xs mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex items-center gap-2 mt-4">
                        <button wire:click="createLocation"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Guardar Ubicación
                        </button>
                        <button wire:click="toggleCreateForm"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </button>
                    </div>
                </div>
                @endif

                <!-- Tabla de ubicaciones -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Sucursal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Bodega
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ubicación
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->locations as $location)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $location->store->warehouseName ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $location->store->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($editingLocationId === $location->id)
                                        <div class="flex items-center gap-2">
                                            <input wire:model="locationName" type="text" 
                                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Ej: Estante A1, Pasillo 3">
                                            @error('locationName') 
                                            <span class="text-red-600 text-xs">{{ $message }}</span> 
                                            @enderror
                                        </div>
                                    @else
                                        <span class="text-gray-900 dark:text-white">
                                            {{ $location->locationId ?? 'Sin ubicación asignada' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($editingLocationId === $location->id)
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="updateLocation({{ $location->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Guardar
                                            </button>
                                            <button wire:click="cancelEdit"
                                                class="inline-flex items-center px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Cancelar
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="editLocation({{ $location->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Editar
                                            </button>
                                            <button wire:click="deleteLocation({{ $location->id }})"
                                                wire:confirm="¿Está seguro de eliminar esta ubicación?"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium">No hay ubicaciones registradas</p>
                                        <p class="text-sm">Este item aún no tiene ubicaciones asignadas</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeModal"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
