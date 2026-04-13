<div class="p-6 space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center space-x-3 mb-4">
        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg">
            <x-heroicon-o-puzzle-piece class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Accesorios</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Asigne los insumos que complementan este ítem.</p>
        </div>
    </div>

    <!-- Formulario para agregar accesorio -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Agregar accesorio</h4>

        @if(count($availableInsumos) === 0)
            <p class="text-sm text-gray-400 dark:text-gray-500">No hay ítems de tipo <strong>Insumo</strong> registrados.</p>
        @else
            <div class="grid grid-cols-1 gap-3">
                <!-- Insumo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Insumo <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="selectedInsumoId"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="">-- Seleccione un insumo --</option>
                        @foreach($availableInsumos as $insumo)
                            <option value="{{ $insumo['id'] }}">
                                {{ $insumo['name'] }}
                                @if($insumo['internal_code'])
                                    ({{ $insumo['internal_code'] }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('selectedInsumoId')
                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Observación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Observación
                    </label>
                    <input type="text" wire:model="observacion" placeholder="Opcional"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                    @error('observacion')
                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botón -->
                <div class="flex justify-end">
                    <button type="button" wire:click="addAccesorio" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors">
                        <span wire:loading.remove wire:target="addAccesorio">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1 inline" />
                            Agregar
                        </span>
                        <span wire:loading wire:target="addAccesorio" class="flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 12 0v4a8 8 0 00-8 8H4z"></path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Lista de accesorios asignados -->
    <div>
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Accesorios asignados</h4>

        @if(count($assignedAccesorios) === 0)
            <div class="text-center py-8 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <x-heroicon-o-puzzle-piece class="w-10 h-10 mx-auto mb-2 opacity-40" />
                <p class="text-sm">No hay accesorios asignados aún.</p>
            </div>
        @else
            <ul class="space-y-2">
                @foreach($assignedAccesorios as $accesorio)
                    <li class="flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 group transition-colors hover:border-indigo-300 dark:hover:border-indigo-600">

                        <div class="p-1.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-md flex-shrink-0">
                            <x-heroicon-o-puzzle-piece class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $accesorio['insumo']['name'] ?? '—' }}
                            </p>
                            <div class="flex gap-3 flex-wrap">
                                @if(!empty($accesorio['insumo']['internal_code']))
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Código: {{ $accesorio['insumo']['internal_code'] }}
                                    </p>
                                @endif
                                @if(!empty($accesorio['observacion']))
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Obs: {{ $accesorio['observacion'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <button type="button"
                            wire:click="removeAccesorio({{ $accesorio['id'] }})"
                            wire:confirm="¿Está seguro de quitar este accesorio?"
                            class="flex-shrink-0 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100"
                            title="Eliminar">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
