<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6" 
     x-data="{ 
         showLabelModal: false, 
         selectedLabel: null,
         lastUpdate: null,
         showUpdateNotification: false
     }"
     @quantity-updated.window="
         console.log('🔔 Cantidad actualizada:', $event.detail);
         lastUpdate = $event.detail;
         showUpdateNotification = true;
         setTimeout(() => showUpdateNotification = false, 3000);
     "
     @item-selected.window="
         console.log('🔔 Item seleccionado:', $event.detail);
     ">
    <div class="max-w-12xl mx-auto">
        <!-- Notificación de actualización de cantidad -->
        <div x-show="showUpdateNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg shadow-lg"
             style="display: none;">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">Cantidad actualizada</span>
                <template x-if="lastUpdate">
                    <span class="text-sm" x-text="'Item #' + lastUpdate.itemId + ': ' + lastUpdate.quantity"></span>
                </template>
            </div>
        </div>

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Importaciones
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">Administración de items del sistema</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                    <!-- Botón Principal -->
                    <button wire:click=""
                        class="inline-flex items-center justify-center px-4 py-2 
                               bg-indigo-600 hover:bg-indigo-700 
                               dark:bg-indigo-500 dark:hover:bg-indigo-600
                               border border-transparent rounded-lg 
                               font-semibold text-xs text-white uppercase tracking-widest 
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 
                               focus:ring-offset-2 dark:focus:ring-offset-gray-800 
                               transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Agregar Nuevo Item
                    </button>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <!-- Administrar Etiquetas -->
                        <button wire:click=""
                            class="inline-flex items-center justify-center px-4 py-2 
                                   bg-emerald-500 hover:bg-emerald-600 
                                   dark:bg-emerald-600 dark:hover:bg-emerald-500
                                   text-white rounded-lg font-semibold text-xs uppercase
                                   border border-transparent
                                   focus:outline-none focus:ring-2 focus:ring-emerald-400 
                                   focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                   transition-all duration-200">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-4-4m4 4l4-4"></path>
                            </svg>
                            <span class="hidden sm:inline">Administrar Etiquetas</span>
                            <span class="sm:hidden">Etiquetas</span>
                        </button>

                        <!-- Instrucciones -->
                        <button wire:click=""
                            class="inline-flex items-center justify-center px-4 py-2 
                                   bg-gray-200 hover:bg-gray-300 
                                   dark:bg-gray-700 dark:hover:bg-gray-600
                                   text-gray-800 dark:text-gray-200
                                   rounded-lg font-semibold text-xs uppercase
                                   border border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-gray-400
                                   focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                   transition-all duration-200">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m0 0l4 4m-4-4l-4 4"></path>
                            </svg>
                            Instrucciones
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Etiquetas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4 mb-3 sm:mb-4">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500 dark:text-gray-400"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z"/>
                    </svg>
                    <h3 class="text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300">
                        Etiquetas disponibles
                    </h3>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ count($labels) }} {{ count($labels) === 1 ? 'etiqueta' : 'etiquetas' }}
                </span>
            </div>

            <div class="flex flex-wrap gap-2 sm:gap-3 mb-2">
                @forelse($labels as $label)
                    @php
                        $colorMap = [
                            'blue' => ['light' => '#3b82f6', 'dark' => '#60a5fa'],
                            'red' => ['light' => '#ef4444', 'dark' => '#f87171'],
                            'green' => ['light' => '#10b981', 'dark' => '#34d399'],
                            'yellow' => ['light' => '#f59e0b', 'dark' => '#fbbf24'],
                            'purple' => ['light' => '#a855f7', 'dark' => '#c084fc'],
                            'pink' => ['light' => '#ec4899', 'dark' => '#f472b6'],
                            'indigo' => ['light' => '#6366f1', 'dark' => '#818cf8'],
                            'gray' => ['light' => '#6b7280', 'dark' => '#9ca3af'],
                            'emerald' => ['light' => '#10b981', 'dark' => '#34d399'],
                            'cyan' => ['light' => '#06b6d4', 'dark' => '#22d3ee'],
                            'orange' => ['light' => '#f97316', 'dark' => '#fb923c'],
                        ];
                        $colors = $colorMap[$label->color] ?? $colorMap['blue'];
                    @endphp
                    <button
                        @click="selectedLabel = {{ $label->id }}; showLabelModal = true"
                        class="label-btn inline-flex items-center px-3 py-1.5 sm:px-5 sm:py-2 rounded-full 
                               text-xs sm:text-sm font-medium uppercase tracking-wide
                               bg-white dark:bg-gray-800 border-2 transition-all duration-200 ease-in-out
                               hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 
                               dark:focus:ring-offset-gray-900 cursor-pointer"
                        style="border-color: {{ $colors['light'] }}; color: {{ $colors['light'] }};"
                        data-light-color="{{ $colors['light'] }}"
                        data-dark-color="{{ $colors['dark'] }}">
                        {{ $label->name }}
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center w-full py-6 sm:py-8">
                        <svg class="h-10 w-10 sm:h-12 sm:w-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No hay etiquetas disponibles
                        </p>
                    </div>
                @endforelse
            </div>
            <!-- Item seleccionado (opcional - para debug) -->
            @if($selectedItemId)
            <div x-data 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="mb-4 p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Item seleccionado: <span class="font-bold">{{ $selectedItemData['sku'] ?? 'N/A' }}</span>
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                Cantidad: <span class="font-semibold">{{ $selectedItemQuantity }}</span> | 
                                ID: {{ $selectedItemId }} |
                                Stock: {{ $selectedItemData['stock'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('selectedItemId', null)" 
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Tabla de meses -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-700">
                            <thead class="bg-blue-100 dark:bg-blue-900/30">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Enero</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Febrero</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Marzo</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Abril</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Mayo</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Junio</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Julio</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Agosto</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Septiembre</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Octubre</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Noviembre</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-blue-900 dark:text-blue-100 uppercase tracking-wider">Diciembre</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-blue-100 dark:divide-blue-800">
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <input type="number" min="0" step="1" value="0" disabled
                                               class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Import List Component -->
        @livewire('tenant.imports.import-list')
    </div>

    <!-- Modal de Etiqueta -->
    <div 
        x-show="showLabelModal"
        x-cloak
        @keydown.escape.window="showLabelModal = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Overlay -->
        <div 
            x-show="showLabelModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="showLabelModal = false"
        ></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="showLabelModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 w-full sm:max-w-lg mx-4 sm:mx-0"
                @click.stop
            >
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-indigo-600 dark:text-indigo-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z"/>
                            </svg>
                            Detalles de Etiqueta
                        </h3>
                        <button 
                            type="button"
                            @click="showLabelModal = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="mt-4">
                        <template x-if="selectedLabel">
                            <div class="space-y-4">
                                @foreach($labels as $label)
                                    @php
                                        $colorMap = [
                                            'blue' => ['light' => '#3b82f6', 'dark' => '#60a5fa'],
                                            'red' => ['light' => '#ef4444', 'dark' => '#f87171'],
                                            'green' => ['light' => '#10b981', 'dark' => '#34d399'],
                                            'yellow' => ['light' => '#f59e0b', 'dark' => '#fbbf24'],
                                            'purple' => ['light' => '#a855f7', 'dark' => '#c084fc'],
                                            'pink' => ['light' => '#ec4899', 'dark' => '#f472b6'],
                                            'indigo' => ['light' => '#6366f1', 'dark' => '#818cf8'],
                                            'gray' => ['light' => '#6b7280', 'dark' => '#9ca3af'],
                                            'emerald' => ['light' => '#10b981', 'dark' => '#34d399'],
                                            'cyan' => ['light' => '#06b6d4', 'dark' => '#22d3ee'],
                                            'orange' => ['light' => '#f97316', 'dark' => '#fb923c'],
                                        ];
                                        $colors = $colorMap[$label->color] ?? $colorMap['blue'];
                                    @endphp
                                    <div x-show="selectedLabel === {{ $label->id }}" class="space-y-4">
                                        <!-- Etiqueta Preview -->
                                        <div class="flex items-center justify-center py-4">
                                            <span class="label-preview inline-flex items-center px-6 py-2 sm:px-8 sm:py-3 rounded-full 
                                                         text-base sm:text-lg font-semibold uppercase tracking-wide
                                                         bg-white dark:bg-gray-800 border-2"
                                                  style="border-color: {{ $colors['light'] }}; color: {{ $colors['light'] }};"
                                                  data-light-color="{{ $colors['light'] }}"
                                                  data-dark-color="{{ $colors['dark'] }}">
                                                {{ $label->name }}
                                            </span>
                                        </div>

                                        <!-- Información de la etiqueta -->
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
                                            <div>
                                                <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</label>
                                                <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1">{{ $label->name }}</p>
                                            </div>
                                            <div>
                                                <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Color</label>
                                                <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1 capitalize">{{ $label->color }}</p>
                                            </div>
                                            @if(isset($label->description))
                                            <div>
                                                <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</label>
                                                <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1">{{ $label->description }}</p>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Acciones -->
                                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                            <button
                                                type="button"
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 
                                                       bg-yellow-100 dark:bg-yellow-900/30 
                                                       text-yellow-800 dark:text-yellow-300 
                                                       text-sm font-medium rounded-lg 
                                                       hover:bg-yellow-200 dark:hover:bg-yellow-900/50 
                                                       transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 
                                                       bg-red-100 dark:bg-red-900/30 
                                                       text-red-800 dark:text-red-300 
                                                       text-sm font-medium rounded-lg 
                                                       hover:bg-red-200 dark:hover:bg-red-900/50 
                                                       transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button
                        type="button"
                        @click="showLabelModal = false"
                        class="w-full sm:w-auto inline-flex justify-center rounded-md 
                               bg-white dark:bg-gray-600 px-3 py-2 text-sm font-semibold 
                               text-gray-900 dark:text-white shadow-sm 
                               ring-1 ring-inset ring-gray-300 dark:ring-gray-500 
                               hover:bg-gray-50 dark:hover:bg-gray-500 
                               transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para manejar colores dinámicos en modo oscuro -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateLabelColors() {
                const isDark = document.documentElement.classList.contains('dark');
                
                // Actualizar botones de etiquetas
                document.querySelectorAll('.label-btn').forEach(btn => {
                    const lightColor = btn.getAttribute('data-light-color');
                    const darkColor = btn.getAttribute('data-dark-color');
                    const color = isDark ? darkColor : lightColor;
                    btn.style.borderColor = color;
                    btn.style.color = color;
                });
                
                // Actualizar preview en modal
                document.querySelectorAll('.label-preview').forEach(preview => {
                    const lightColor = preview.getAttribute('data-light-color');
                    const darkColor = preview.getAttribute('data-dark-color');
                    const color = isDark ? darkColor : lightColor;
                    preview.style.borderColor = color;
                    preview.style.color = color;
                });
            }
            
            // Actualizar al cargar
            updateLabelColors();
            
            // Observar cambios en el modo oscuro
            const observer = new MutationObserver(updateLabelColors);
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    </script>
</div>
