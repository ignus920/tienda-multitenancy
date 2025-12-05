<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Transferencias
                        <span class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                            | valor dinamico
                        </span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Administración de transferencias entre sucursales</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start sm:justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <button wire:click="create"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$this->canCreateTransfer ? 'disabled' : '' }}>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Agregar
                        </button>
                        @if(!$this->canCreateTransfer)
                            <p class="text-xs text-red-600 dark:text-red-400">
                                {{ $this->transferValidationMessage }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if($successMessage && !$showModal && !$showDetailsModal)
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-green-700 dark:text-green-400">{{ $successMessage }}</p>
            </div>
        </div>
        @endif

        <!-- Error Message -->
        @if($errorMessage && !$showModal && !$showDetailsModal)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
        </div>
        @endif

        <!-- Transfer List Component -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Listado de Transferencias
            </h2>
            <livewire:tenant.transfers.components.transfer-list />
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Nueva Transferencia
                        <span class="text-base font-medium text-gray-700 dark:text-gray-300">
                            | valor dinamico
                        </span>
                    </h3>
                    <button wire:click="closeModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Messages -->
                    @if($successMessage)
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm text-green-700 dark:text-green-400">{{ $successMessage }}</p>
                    </div>
                    @endif

                    @if($errorMessage)
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                    </div>
                    @endif

                    <!-- Form -->
                    <div class="space-y-4">
                    
                       <!-- Warehouse from Select -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sucursal origen  <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="transferForm.warehouseFromId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('transferForm.warehouseFromId') border-red-500 @enderror">
                                <option value="">Seleccionar sucursal</option>
                                @foreach($this->warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }} - {{ $warehouse->company->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('transferForm.warehouseFromId') 
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                       <!-- Warehouse to Select -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sucursal destino <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="transferForm.warehouseToId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('transferForm.warehouseToId') border-red-500 @enderror">
                                <option value="">Seleccionar sucursal</option>
                                @foreach($this->warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }} - {{ $warehouse->company->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('transferForm.warehouseToId') 
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- Store from Select - Only show if multiple stores exist -->
                        @if(!empty($transferForm['warehouseFromId']) && $this->storesFrom->count() > 1)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega origen <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="transferForm.storeFromId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('transferForm.storeFromId') border-red-500 @enderror">
                                <option value="">Seleccionar bodega</option>
                                @foreach($this->storesFrom as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            @error('transferForm.storeFromId') 
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>
                        @elseif(!empty($transferForm['warehouseFromId']) && $this->storesFrom->count() === 1)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega origen <span class="text-red-500">*</span>
                            </label>
                            <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white">
                                {{ $this->storesFrom->first()->name }}
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bodega seleccionada automáticamente</p>
                        </div>
                        @endif

                        <!-- Store to Select - Only show if multiple stores exist -->
                        @php
                            $availableStoresTo = $this->storesTo;
                            // If same warehouse, filter out the origin store
                            if ($transferForm['warehouseFromId'] === $transferForm['warehouseToId'] && !empty($transferForm['storeFromId'])) {
                                $availableStoresTo = $availableStoresTo->filter(function($store) use ($transferForm) {
                                    return $store->id != $transferForm['storeFromId'];
                                });
                            }
                        @endphp
                        
                        @if(!empty($transferForm['warehouseToId']) && $availableStoresTo->count() > 1)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega destino <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="transferForm.storeToId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('transferForm.storeToId') border-red-500 @enderror">
                                <option value="">Seleccionar bodega</option>
                                @foreach($availableStoresTo as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            @error('transferForm.storeToId') 
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                            @enderror
                            @if($transferForm['warehouseFromId'] === $transferForm['warehouseToId'])
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    La bodega de origen está excluida de las opciones
                                </p>
                            @endif
                        </div>
                        @elseif(!empty($transferForm['warehouseToId']) && $availableStoresTo->count() === 1)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega destino <span class="text-red-500">*</span>
                            </label>
                            <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white">
                                {{ $availableStoresTo->first()->name }}
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bodega seleccionada automáticamente</p>
                        </div>
                        @elseif(!empty($transferForm['warehouseToId']) && $availableStoresTo->count() === 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bodega destino <span class="text-red-500">*</span>
                            </label>
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm text-red-700 dark:text-red-400">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                     La bodega de destino no puede ser a la bodega de origen. Seleccione una bodega o sucursal diferente.
                                </p>
                            </div>
                        </div>
                        @endif
                        <!-- Observations -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Observaciones
                            </label>
                            <textarea wire:model.defer="transferForm.observations" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"></textarea>
                            @error('transferForm.observations')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Items Section - Only show when both warehouses and stores are selected -->
                    @if($this->canShowItemsSection)
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Agregar Items</h4>
                        
                        <!-- Add Item Form -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <!-- Item Select -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Item <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.defer="detailForm.itemId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar item</option>
                                    @foreach($this->items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->name }} @if($item->sku) - {{ $item->sku }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="0.01" wire:model.defer="detailForm.quantity"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="0.00">
                            </div>

                            <!-- Unit Measurement -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Unidad <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.defer="detailForm.unitMeasurementId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar</option>
                                    @foreach($this->unitMeasurements as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Add Button -->
                        <div class="flex justify-end mb-4">
                            <button wire:click="addDetail" type="button"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Agregar Item
                            </button>
                        </div>

                        <!-- Items Table -->
                        @if(count($details) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock Actual</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($details as $index => $detail)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $detail['itemName'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $detail['sku'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ number_format($detail['quantity'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $detail['unitMeasurementName'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 text-right">{{ number_format($detail['currentStock'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button wire:click="removeDetail({{ $index }})" type="button"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm text-yellow-700 dark:text-yellow-400 font-medium">
                                    No hay items agregados. Debe agregar al menos un item para poder guardar la transferencia.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm text-blue-700 dark:text-blue-400">
                            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Seleccione la sucursal y bodega de origen y destino para agregar items a la transferencia.
                        </p>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <button wire:click="closeModal" type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveTransfer" type="button" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ empty($transferForm['warehouseFromId']) || empty($transferForm['warehouseToId']) || empty($transferForm['storeFromId']) || empty($transferForm['storeToId']) || count($details) === 0 ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="saveTransfer">Guardar Transferencia</span>
                            <span wire:loading wire:target="saveTransfer">
                                <svg class="animate-spin inline h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Details Modal -->
    @if($showDetailsModal)
    <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Detalle de Transferencia #{{ $transferDetails['consecutive'] ?? 'N/A' }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            @if(isset($transferDetails['status']))
                                @if($transferDetails['status'] == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Anulada
                                    </span>
                                @endif
                            @endif
                        </p>
                    </div>
                    <button wire:click="closeDetailsModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Messages -->
                    @if($successMessage)
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm text-green-700 dark:text-green-400">{{ $successMessage }}</p>
                    </div>
                    @endif

                    @if($errorMessage)
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                    </div>
                    @endif

                    <!-- Transfer Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $transferDetails['date'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuario</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $transferDetails['user_name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal Origen</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $transferDetails['warehouse_from'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal Destino</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $transferDetails['warehouse_to'] ?? 'N/A' }}</p>
                        </div>
                        @if(!empty($transferDetails['observations']))
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $transferDetails['observations'] }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Items Table -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Items Transferidos</h4>
                        @if(isset($transferDetails['details']) && count($transferDetails['details']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad Recibida</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($transferDetails['details'] as $detail)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $detail['item_name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ $detail['quantity'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ $detail['amount_received'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-sm text-gray-600 dark:text-gray-400">No hay items en esta transferencia.</p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-between gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div>
                            @if(isset($transferDetails['status']) && $transferDetails['status'] == 1)
                            <button wire:click="cancelTransfer" 
                                wire:confirm="¿Está seguro que desea anular esta transferencia? Esta acción no se puede deshacer."
                                type="button" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Anular Transferencia
                            </button>
                            @endif

                            @if(isset($transferDetails['status']) && $transferDetails['status'] == 1)
                            <button wire:click="cancelTransfer" 
                                wire:confirm="¿Está seguro que desea recibir esta transferencia? Esta acción no se puede deshacer."
                                type="button" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg transition-colors">
                               <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
</svg>
                                Recibir Transferencia
                            </button>
                            @endif
                        </div>
                        <button wire:click="closeDetailsModal" type="button" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>