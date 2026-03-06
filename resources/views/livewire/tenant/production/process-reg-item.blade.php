<div class="p-6 space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center space-x-3 mb-4">
        <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-amber-600 dark:text-amber-400" />
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Proceso de Producción</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Asigne los procesos de producción y su orden de ruta.</p>
        </div>
    </div>

    <!-- Formulario para agregar proceso -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Agregar proceso</h4>
        <div class="flex gap-3 items-end">

            <!-- Select Proceso -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Proceso <span class="text-red-500">*</span>
                </label>
                <select wire:model="selectedProcessId"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                    <option value="">-- Seleccione un proceso --</option>
                    @foreach($availableProcesses as $process)
                        <option value="{{ $process['id'] }}">{{ $process['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedProcessId')
                    <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Indicador de orden automático -->
            <div class="flex-shrink-0 pb-0.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-hashtag class="w-3.5 h-3.5" />
                    Orden: {{ count($assignedProcesses) + 1 }}
                </span>
            </div>

        </div>

        <div class="mt-3 flex justify-end">
            <button type="button" wire:click="addProcess" wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors">
                <span wire:loading.remove wire:target="addProcess">
                    <x-heroicon-o-plus class="w-4 h-4 mr-1 inline" />
                    Agregar
                </span>
                <span wire:loading wire:target="addProcess" class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 12 0v4a8 8 0 00-8 8H4z"></path>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>
    </div>

    <!-- Tabla de procesos asignados -->
    <div>
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Procesos asignados</h4>

        @if(count($assignedProcesses) === 0)
            <div class="text-center py-8 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <x-heroicon-o-cog-6-tooth class="w-10 h-10 mx-auto mb-2 opacity-40" />
                <p class="text-sm">No hay procesos asignados aún.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Orden
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Proceso
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Notas previas
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($assignedProcesses as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-7 h-7 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full">
                                        {{ $item['process_route_order'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                    {{ $item['process']['name'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item['process']['previous_notes'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button type="button"
                                        wire:click="removeProcess({{ $item['id'] }})"
                                        wire:confirm="¿Está seguro de eliminar este proceso de la ruta?"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                        title="Eliminar">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
