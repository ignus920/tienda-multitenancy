<div class="p-6 space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center space-x-3 mb-4">
        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg">
            <x-heroicon-o-sparkles class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Productos Sugeridos</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Estos productos aparecerán automáticamente debajo de la foto de este producto en el PDF de cotización.</p>
        </div>
    </div>

    <!-- Formulario para agregar sugerido -->
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Agregar producto sugerido</h4>

        @if(count($assignedSuggestions) >= \App\Livewire\Tenant\Items\ItemSuggestedProducts::MAX_SUGGESTIONS)
            <p class="text-sm text-gray-400 dark:text-gray-500">Ya se alcanzó el máximo de {{ \App\Livewire\Tenant\Items\ItemSuggestedProducts::MAX_SUGGESTIONS }} productos sugeridos.</p>
        @else
            <div class="grid grid-cols-1 gap-3">
                <div class="flex flex-col md:flex-row gap-3 md:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Producto <span class="text-red-500">*</span>
                        </label>
                        <div class="w-full relative" x-data="{ open: true }" @click.away="open = false">
                            <input wire:model.live.debounce.300ms="search" @focus="open = true" type="text"
                                placeholder="Buscar por código o nombre..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @if(!empty($searchResults) && $search)
                                <div x-show="open" class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-40 max-h-56 overflow-y-auto">
                                    @foreach($searchResults as $result)
                                        <button type="button"
                                            wire:click="selectSuggestedItem({{ $result['id'] }}, '{{ addslashes($result['name']) }}', '{{ addslashes($result['code']) }}')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-750">
                                            <span class="text-gray-400">{{ $result['code'] }}</span> - <span class="font-medium">{{ $result['name'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('selectedSuggestedItemId')
                            <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <button type="button" wire:click="addSuggestion" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors">
                            <span wire:loading.remove wire:target="addSuggestion">
                                <x-heroicon-o-plus class="w-4 h-4 mr-1 inline" />
                                Agregar
                            </span>
                            <span wire:loading wire:target="addSuggestion" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 12 0v4a8 8 0 00-8 8H4z"></path>
                                </svg>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Haz clic sobre un resultado de la búsqueda para seleccionarlo.</p>
            </div>
        @endif
    </div>

    <!-- Lista de productos sugeridos asignados -->
    <div>
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Sugeridos asignados
            <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">({{ count($assignedSuggestions) }}/{{ \App\Livewire\Tenant\Items\ItemSuggestedProducts::MAX_SUGGESTIONS }})</span>
        </h4>

        @if(count($assignedSuggestions) === 0)
            <div class="text-center py-8 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <x-heroicon-o-sparkles class="w-10 h-10 mx-auto mb-2 opacity-40" />
                <p class="text-sm">No hay productos sugeridos asignados aún.</p>
            </div>
        @else
            <ul class="space-y-2">
                @foreach($assignedSuggestions as $suggestion)
                    <li class="flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 group transition-colors hover:border-indigo-300 dark:hover:border-indigo-600">

                        <div class="p-1 bg-gray-50 dark:bg-indigo-900/20 rounded-lg flex-shrink-0 overflow-hidden w-12 h-12 border border-gray-100 dark:border-slate-700 flex items-center justify-center text-indigo-400">
                            <x-heroicon-o-cube class="w-6 h-6" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                {{ $suggestion['suggested_item']['name'] ?? '—' }}
                            </p>
                            @if(!empty($suggestion['suggested_item']['internal_code']))
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Código: {{ $suggestion['suggested_item']['internal_code'] }}
                                </p>
                            @endif
                        </div>

                        <button type="button"
                            x-data
                            x-on:click="
                                Swal.fire({
                                    title: '¿Quitar producto sugerido?',
                                    text: 'Dejará de aparecer en el PDF de cotización.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#4f46e5',
                                    confirmButtonText: 'Sí, quitar',
                                    cancelButtonText: 'Cancelar',
                                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                                    color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.removeSuggestion({{ $suggestion['id'] }})
                                    }
                                })
                            "
                            class="flex-shrink-0 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100"
                            title="Quitar">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
