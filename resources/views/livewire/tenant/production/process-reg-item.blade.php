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

    <!-- Lista de procesos asignados con drag & drop -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Procesos asignados</h4>
            @if(count($assignedProcesses) > 1)
                <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                    <x-heroicon-o-arrows-up-down class="w-3.5 h-3.5" />
                    Arrastra para reordenar
                </span>
            @endif
        </div>

        @if(count($assignedProcesses) === 0)
            <div class="text-center py-8 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <x-heroicon-o-cog-6-tooth class="w-10 h-10 mx-auto mb-2 opacity-40" />
                <p class="text-sm">No hay procesos asignados aún.</p>
            </div>
        @else
            <ul id="sortable-processes-{{ $itemId }}" class="space-y-2">
                @foreach($assignedProcesses as $item)
                    <li data-id="{{ $item['id'] }}"
                        class="flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 group transition-colors hover:border-amber-300 dark:hover:border-amber-600">

                        {{-- Handle de arrastre --}}
                        <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 flex-shrink-0" title="Arrastrar para reordenar">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM8 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM8 22a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                            </svg>
                        </span>

                        {{-- Número de orden --}}
                        <span class="inline-flex items-center justify-center w-7 h-7 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full flex-shrink-0">
                            {{ $item['process_route_order'] }}
                        </span>

                        {{-- Nombre del proceso --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $item['process']['name'] ?? '—' }}
                            </p>
                            @if(!empty($item['process']['previous_notes']))
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                    {{ $item['process']['previous_notes'] }}
                                </p>
                            @endif
                        </div>

                        {{-- Botón eliminar --}}
                        <button type="button"
                            wire:click="removeProcess({{ $item['id'] }})"
                            wire:confirm="¿Está seguro de eliminar este proceso de la ruta?"
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

@script
<script>
    (function () {
        function initSortable() {
            var listId = 'sortable-processes-{{ $itemId }}';
            var el = document.getElementById(listId);
            if (!el) return;

            if (el._sortableInstance) {
                el._sortableInstance.destroy();
                el._sortableInstance = null;
            }

            el._sortableInstance = new Sortable(el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd: function () {
                    var ids = Array.from(el.querySelectorAll('[data-id]')).map(function (e) {
                        return parseInt(e.dataset.id);
                    });
                    $wire.reorderProcesses(ids);
                }
            });
        }

        initSortable();
    })();
</script>
@endscript
