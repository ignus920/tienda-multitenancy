<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6" 
     x-data="{ 
         showLabelModal: false, 
         selectedLabel: null,
         selectedLabelData: null,
         selectedItemData: null,
         lastUpdate: null,
         showUpdateNotification: false,
         showWarningNotification: false,
         warningMessage: '',
         showSuccessNotification: false,
         successMessage: '',
         selectedLabelId: null
     }"
     @quantity-updated.window="
         console.log('Cantidad actualizada:', $event.detail);
         lastUpdate = $event.detail;
         showUpdateNotification = true;
         setTimeout(() => showUpdateNotification = false, 3000);
     "
     @item-selected.window="
         console.log('Item seleccionado:', $event.detail);
         selectedItemData = $event.detail[0];
     "
     @label-assigned.window="
         console.log('Etiqueta asignada:', $event.detail);
         successMessage = 'Etiqueta ' + $event.detail[0].labelName + ' asignada correctamente';
         showSuccessNotification = true;
         setTimeout(() => showSuccessNotification = false, 4000);
     "
     @quantity-input-clicked.window="
         console.log('Input de cantidad clickeado:', $event.detail);
         if (selectedLabelId) {
             const btn = document.querySelector('[data-label-id=\'' + selectedLabelId + '\']');
             const labelName = btn ? btn.getAttribute('data-label-name') : '';
             
             // Llamar directamente al método Livewire del componente import-services
             const importServicesComponent = Livewire.find(document.querySelector('[wire\\\\:id]').getAttribute('wire:id'));
             if (importServicesComponent) {
                 importServicesComponent.call('assignLabelToItemById', $event.detail.itemId, selectedLabelId, labelName);
             }
         }
     "
     @quantity-input-changed.window="
         console.log('Input de cantidad cambiado:', $event.detail);
         if (selectedLabelId) {
             const btn = document.querySelector('[data-label-id=\'' + selectedLabelId + '\']');
             const labelName = btn ? btn.getAttribute('data-label-name') : '';
             
             // Llamar directamente al método Livewire del componente import-services
             const importServicesComponent = Livewire.find(document.querySelector('[wire\\\\:id]').getAttribute('wire:id'));
             if (importServicesComponent) {
                 importServicesComponent.call('assignLabelToItemById', $event.detail.itemId, selectedLabelId, labelName);
                 selectedLabelId = null;
             }
         }
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

        <!-- Notificación de advertencia (sin item seleccionado) -->
        <div x-show="showWarningNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-300 px-4 py-3 rounded-lg shadow-lg max-w-md"
             style="display: none;">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-sm" x-text="warningMessage"></p>
                </div>
                <button @click="showWarningNotification = false" class="flex-shrink-0 text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Notificación de éxito (etiqueta asignada) -->
        <div x-show="showSuccessNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg shadow-lg max-w-md"
             style="display: none;">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-sm" x-text="successMessage"></p>
                </div>
                <button @click="showSuccessNotification = false" class="flex-shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
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
                    <button wire:click="showModalRegis"
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



                    <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start sm:justify-between gap-4">
                        {{--
                        <!-- Administrar Etiquetas -->
                        <a href="{{ route('imports.imports-labels' )}}"
                            class="inline-flex items-center px-4 py-2 
                            bg-emerald-500 hover:bg-emerald-600 
                            dark:bg-emerald-600 dark:hover:bg-emerald-500
                            text-white rounded-lg font-semibold text-xs uppercase
                            border border-transparent
                            focus:outline-none focus:ring-2 focus:ring-emerald-400 
                            focus:ring-offset-2 dark:focus:ring-offset-gray-800
                            transition-all duration-200">
                            <x-heroicon-o-tag class="w-5 h-5 mr-2" />
                            Administrar Etiquetas
                        </a>
                        <!-- Instrucciones -->
                        <button wire:click=""
                            class="inline-flex items-center px-4 py-2 
                            bg-gray-200 hover:bg-gray-300 
                            dark:bg-gray-700 dark:hover:bg-gray-600
                            text-gray-800 dark:text-gray-200
                            rounded-lg font-semibold text-xs uppercase
                            border border-gray-300 dark:border-gray-600
                            focus:outline-none focus:ring-2 focus:ring-gray-400
                            focus:ring-offset-2 dark:focus:ring-offset-gray-800
                            transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 20V4m0 0l4 4m-4-4l-4 4"></path>
                            </svg>
                            Instrucciones
                        </button>
                        --}}
                    </div>
                    @include('livewire.tenant.parameters.dynamic-buttons', ['buttons' => $this->dynamicButtons])
                </div>
            </div>

        <!-- Item seleccionado (opcional - posicionado arriba para prioridad) -->
        @if($selectedItemId)
         <div x-data 
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 transform scale-95"
              x-transition:enter-end="opacity-100 transform scale-100"
              class="sticky top-0 z-[100] mb-4 p-3 sm:p-4 bg-blue-50 dark:bg-slate-900 border border-blue-200 dark:border-blue-800 rounded-lg shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <!-- Izquierda: Prioridades y cantidades -->
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <!-- Contenedor Horizontal de Tarjetas de Prioridades (ASAP, Second, Third) -->
                    <div class="flex flex-wrap items-end gap-4">
                        @forelse($selectedItemPriorities as $prior)
                            <div class="flex flex-col items-center w-16">
                                <!-- Badge de Prioridad arriba -->
                                <span class="inline-flex items-center justify-center px-1 py-1 rounded-t-md text-[10px] font-extrabold text-white uppercase tracking-wider w-full text-center shadow-sm
                                    {{ $prior['priority'] === 'ASAP' ? 'bg-rose-600' : '' }}
                                    {{ $prior['priority'] === 'Second' ? 'bg-amber-600' : '' }}
                                    {{ $prior['priority'] === 'Third' ? 'bg-blue-600' : '' }}">
                                    {{ $prior['priority'] }}
                                </span>
                                <!-- Caja blanca con cantidad abajo -->
                                <div class="bg-white dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700 rounded-b-md w-full py-1.5 text-center shadow-sm font-mono font-bold text-sm text-gray-900 dark:text-white">
                                    {{ $prior['qty_requested'] }}
                                </div>
                                <!-- Fecha debajo de la caja -->
                                @if($prior['priority_assigned_at'])
                                    <span class="text-[9px] text-gray-500 dark:text-gray-400 font-mono mt-1">
                                        {{ \Carbon\Carbon::parse($prior['priority_assigned_at'])->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <span class="text-xs text-gray-400 italic mt-1">Sin prioridades programadas en este momento</span>
                        @endforelse
                    </div>
                </div>

                <!-- Derecha: Información del producto y botón de cerrar -->
                <div class="flex items-center gap-4 text-right">
                    <div class="max-w-md sm:max-w-xl md:max-w-2xl">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            <span class="font-bold text-blue-950 dark:text-blue-200">{{ $selectedItemData['sku'] ?? 'N/A' }}</span>
                            <span class="text-blue-800 dark:text-blue-300">- {{ $selectedItemData['description'] ?? $selectedItemData['name'] ?? '' }}</span>
                        </p>
                    </div>
                    <button wire:click="$set('selectedItemId', null)" 
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tabla de meses -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-700 overflow-hidden mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-700">
                        <thead class="bg-blue-100 dark:bg-blue-900/30">
                            <tr>
                                @foreach($monthlyQuantities as $data)
                                <th scope="col" class="px-3 py-2 text-center text-xs font-semibold text-blue-900 dark:text-blue-100 uppercase tracking-wider">
                                    {{ $data['label'] }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-blue-100 dark:divide-blue-800">
                            <tr>
                                @foreach($monthlyQuantities as $data)
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="block w-full px-2 py-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center">
                                        {{ $data['qty'] }}
                                    </span>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- 
        <!-- Sección de Etiquetas (Comentado temporalmente por requerimiento del cliente) -->
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
                            d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z" />
                    </svg>
                    <h3 class="text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300">
                        Control de etiquetas
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
                $isAssigned = $this->isLabelAssigned($label->id);
                $assignedQty = $isAssigned ? $this->getAssignedQuantity($label->id) : 0;
                @endphp

                <div class="relative inline-flex items-center">
                    <button
                        @click="
                            selectedLabelId = {{ $isAssigned ? 'null' : $label->id }};
                        "
                        @if($isAssigned) disabled @endif
                        class="label-btn inline-flex items-center gap-2 px-2 py-1.5 sm:px-3 sm:py-1 rounded-full 
                       text-xs sm:text-sm font-light uppercase tracking-wide
                       bg-white dark:bg-gray-800 border transition-all duration-200 ease-in-out
                       focus:outline-none focus:ring-2 focus:ring-offset-2 
                       dark:focus:ring-offset-gray-900
                       {{ $isAssigned ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105 hover:shadow-md cursor-pointer' }}"
                        :class="selectedLabelId === {{ $label->id }} ? 'ring-4 ring-offset-2 !scale-110 shadow-lg' : ''"
                        style="border-color: {{ $colors['light'] }}; color: {{ $colors['light'] }};"
                        data-light-color="{{ $colors['light'] }}"
                        data-dark-color="{{ $colors['dark'] }}"
                        data-label-id="{{ $label->id }}"
                        data-label-name="{{ $label->name }}">
                        <span>{{ $label->name }}</span>
                    </button>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center w-full py-6 sm:py-8">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay etiquetas disponibles</p>
                </div>
                @endforelse
            </div>
        </div>
        --}}

        <!-- Import List Component -->
        @livewire('tenant.imports.import-list')
    </div>

    <!-- Modal de Etiqueta -->
    <div 
        x-show="showLabelModal"
        x-cloak
        @keydown.escape.window="showLabelModal = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
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
            @click="showLabelModal = false"></div>

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
                @click.stop>
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
                                    d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z" />
                            </svg>
                            Detalles de Etiqueta
                        </h3>
                        <button
                            type="button"
                            @click="showLabelModal = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="mt-4">
                        <template x-if="selectedLabelData && selectedItemData">
                            <div class="space-y-4">
                                <!-- Etiqueta Preview -->
                                <div class="flex items-center justify-center py-4">
                                    <span class="inline-flex items-center px-6 py-2 sm:px-8 sm:py-3 rounded-full 
                                                 text-base sm:text-lg font-semibold uppercase tracking-wide
                                                 bg-indigo-100 dark:bg-indigo-900/30 
                                                 text-indigo-800 dark:text-indigo-200
                                                 border-2 border-indigo-300 dark:border-indigo-700"
                                        x-text="selectedLabelData.name">
                                    </span>
                                </div>

                                <!-- Información del item seleccionado -->
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800">
                                    <h4 class="text-sm font-semibold text-indigo-900 dark:text-indigo-100 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                        </svg>
                                        Item a etiquetar
                                    </h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-xs font-medium text-indigo-700 dark:text-indigo-300">ID</label>
                                            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100" x-text="selectedItemData.itemId"></p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-indigo-700 dark:text-indigo-300">SKU</label>
                                            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100" x-text="selectedItemData.sku || 'N/A'"></p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-indigo-700 dark:text-indigo-300">Nombre</label>
                                            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100" x-text="selectedItemData.name || 'N/A'"></p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-indigo-700 dark:text-indigo-300">Cantidad</label>
                                            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100" x-text="selectedItemData.quantity"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información de la etiqueta -->
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
                                    <div>
                                        <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Nombre de Programación</label>
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1" x-text="selectedLabelData.name"></p>
                                    </div>
                                    <div>
                                        <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Fecha estimada</label>
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1" x-text="selectedLabelData.estimated_date || 'N/A'"></p>
                                    </div>
                                    
                                    <template x-if="selectedLabelData.description">
                                        <div>
                                            <label class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</label>
                                            <p class="text-sm sm:text-base text-gray-900 dark:text-white mt-1" x-text="selectedLabelData.description"></p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Mensaje de advertencia cuando cantidad es cero -->
                                <template x-if="selectedItemData.quantity == 0">
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                                    No se puede asignar programación
                                                </p>
                                                <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                                    La cantidad del item debe ser mayor a cero para poder asignar una programación.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Botón de asignar etiqueta -->
                                <div class="pt-2">
                                    <button
                                        type="button"
                                        @click="
                                            $wire.assignLabelToItem(selectedLabelData.id, selectedLabelData.name);
                                            showLabelModal = false;
                                        "
                                        :disabled="selectedItemData.quantity == 0"
                                        :class="selectedItemData.quantity == 0 ? 'opacity-50 cursor-not-allowed bg-gray-400 dark:bg-gray-600' : 'bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 hover:shadow-md'"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 
                                               text-white text-sm font-semibold rounded-lg 
                                               shadow-sm
                                               transition-all duration-200
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M7 7h.01M3 11l8.586 8.586a2 2 0 002.828 0l6.172-6.172a2 2 0 000-2.828L12 3H5a2 2 0 00-2 2v6z"/>
                                        </svg>
                                        <span x-text="selectedLabelData.name"></span>
                                    </button>
                                </div>
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
            // Mapa de colores
            window.colorMap = {
                'blue': {
                    light: '#3b82f6',
                    dark: '#60a5fa'
                },
                'red': {
                    light: '#ef4444',
                    dark: '#f87171'
                },
                'green': {
                    light: '#10b981',
                    dark: '#34d399'
                },
                'yellow': {
                    light: '#f59e0b',
                    dark: '#fbbf24'
                },
                'purple': {
                    light: '#a855f7',
                    dark: '#c084fc'
                },
                'pink': {
                    light: '#ec4899',
                    dark: '#f472b6'
                },
                'indigo': {
                    light: '#6366f1',
                    dark: '#818cf8'
                },
                'gray': {
                    light: '#6b7280',
                    dark: '#9ca3af'
                },
                'emerald': {
                    light: '#10b981',
                    dark: '#34d399'
                },
                'cyan': {
                    light: '#06b6d4',
                    dark: '#22d3ee'
                },
                'orange': {
                    light: '#f97316',
                    dark: '#fb923c'
                }
            };

            // Función para obtener color según el modo
            window.getColorForLabel = function(colorName, isDark) {
                const colors = window.colorMap[colorName] || window.colorMap['blue'];
                const mode = isDark || document.documentElement.classList.contains('dark');
                return mode ? colors.dark : colors.light;
            };

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

    @if ($showModalRegisItem)
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
                                Crear Item
                            </h3>
                        </div>
                        <button wire:click="cancel"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    @livewire('tenant.imports.import-reg-item')
                </div>
            </div>
        </div>
    @endif
</div>
