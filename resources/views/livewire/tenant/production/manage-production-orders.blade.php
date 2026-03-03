<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="w-full space-y-6">

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                        <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Órdenes de Producción</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona y rastrea las órdenes de producción.</p>
                    </div>
                </div>
                <button wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Nueva orden
                </button>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-4 h-4 flex-shrink-0" />
            {{ session('success') }}
        </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="relative w-full sm:max-w-xs">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar por producto u orden..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <select wire:model.live="filterStatus"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $id => $lbl)
                            <option value="{{ $id }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <span>Mostrar</span>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span>registros</span>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entrega</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Etapa</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($orders as $order)
                        @php
                            // Colores por ID numérico de estado
                            $statusColors = [
                                3 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400',  // Registrado
                                4 => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400',          // En proceso
                                5 => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400',      // Finalizado
                                6 => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400',              // Anulado
                                7 => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-400',  // Pausado
                            ];
                            $stageColors = [
                                'pendiente'  => 'text-gray-500 dark:text-gray-400',
                                'disenio'    => 'text-purple-600 dark:text-purple-400',
                                'preprensa'  => 'text-indigo-600 dark:text-indigo-400',
                                'impresion'  => 'text-blue-600 dark:text-blue-400',
                                'acabados'   => 'text-orange-600 dark:text-orange-400',
                                'despacho'   => 'text-green-600 dark:text-green-400',
                            ];
                            $contactName = $order->contact
                                ? trim("{$order->contact->firstName} {$order->contact->lastName}")
                                : '—';
                            // Vencida si no está finalizada(5) ni anulada(6)
                            $isOverdue = !in_array($order->status, [5, 6])
                                && $order->delivery_date < now();
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $order->date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $order->item?->name ?? '—' }}
                                    </p>
                                    @if($order->customer_order)
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Ref. cliente: {{ $order->customer_order }}
                                    </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $contactName }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($order->requested_qty) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    @if($isOverdue)
                                        <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 text-red-500 flex-shrink-0" />
                                    @endif
                                    <span class="text-sm {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $order->delivery_date->format('d/m/Y') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="openProcessModal({{ $order->id }})"
                                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 hover:underline transition-colors text-left"
                                    title="Ver siguiente proceso">
                                    {{ $order->productiveProcess?->name ?? '—' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $statuses[$order->status] ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="edit({{ $order->id }})"
                                        class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                                        title="Editar">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button wire:click="delete({{ $order->id }})"
                                        wire:confirm="¿Eliminar esta orden de producción?"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                        title="Eliminar">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                <x-heroicon-o-clipboard-document-list class="w-10 h-10 mx-auto mb-2 opacity-40" />
                                <p class="text-sm">No hay órdenes de producción registradas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ── Modal Crear / Editar Orden ──────────────────────────── --}}
    {{-- ── Modal Avance de Etapa ────────────────────────────────── --}}
    @if($showProcessModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        x-data="{ show: true }" x-show="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showProcessModal', false)"></div>

        <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black/10 dark:ring-white/10 w-full max-w-2xl max-h-[92vh] overflow-y-auto my-auto"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            {{-- Cabecera --}}
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-xl">
                <div class="flex items-center gap-3">
                    @if($processModalIsLast)
                    <div class="p-2 bg-green-100 dark:bg-green-900/40 rounded-lg">
                        <x-heroicon-o-check-badge class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Finalizar orden de producción
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Último proceso completado: <span class="font-medium text-green-600 dark:text-green-400">{{ $processModalName }}</span>
                        </p>
                    </div>
                    @else
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                        <x-heroicon-o-arrow-right-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Siguiente proceso: <span class="text-blue-600 dark:text-blue-400">{{ $processModalName }}</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Complete los campos para avanzar la etapa de producción</p>
                    </div>
                    @endif
                </div>
                <button wire:click="$set('showProcessModal', false)"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Formulario --}}
            <form wire:submit.prevent="saveProcessStage" class="p-6 space-y-5">

                @if($processModalIsLast)
                    <div class="flex items-center gap-3 px-4 py-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 flex-shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                Todos los procesos han sido completados.
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">
                                Al confirmar, la orden pasará al estado <span class="font-semibold">Finalizado</span>.
                            </p>
                        </div>
                    </div>
                @elseif(empty($processModalFields))
                    <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400 flex-shrink-0" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Este proceso no tiene campos adicionales configurados.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($processModalFields as $field)
                        @php
                            $isFullWidth = in_array($field['type'], ['text_area', 'textarea']);
                        @endphp
                        <div class="{{ $isFullWidth ? 'sm:col-span-2' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $field['label'] }}
                            </label>

                            @if($field['type'] === 'text')
                                <input type="text"
                                    wire:model="processFieldValues.{{ $field['name'] }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @elseif($field['type'] === 'number')
                                <input type="number"
                                    wire:model="processFieldValues.{{ $field['name'] }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @elseif($field['type'] === 'date')
                                <input type="date"
                                    wire:model="processFieldValues.{{ $field['name'] }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @elseif($field['type'] === 'select')
                                <select wire:model="processFieldValues.{{ $field['name'] }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Seleccione —</option>
                                    @foreach($field['options'] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>

                            @elseif(in_array($field['type'], ['text_area', 'textarea']))
                                <textarea wire:model="processFieldValues.{{ $field['name'] }}" rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>

                            @elseif($field['type'] === 'checkbox')
                                <div class="flex items-center mt-1 gap-2">
                                    <input type="checkbox"
                                        wire:model="processFieldValues.{{ $field['name'] }}"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $field['label'] }}</span>
                                </div>

                            @else
                                <input type="text"
                                    wire:model="processFieldValues.{{ $field['name'] }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                {{-- ── Consumo de materiales (si el proceso lo requiere) ── --}}
                @if($processModalHasInventory)
                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <x-heroicon-o-archive-box class="w-4 h-4 text-orange-500" />
                            Consumo de materiales
                        </h4>
                        <button type="button" wire:click="addMaterialRow"
                            class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            Agregar material
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach($materialConsumptions as $index => $consumption)
                        <div class="flex items-center gap-2">
                            {{-- Selector de material --}}
                            <select wire:model="materialConsumptions.{{ $index }}.material_itemId"
                                class="flex-1 min-w-0 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">— Seleccione material —</option>
                                @foreach($materialItems as $mat)
                                    <option value="{{ $mat['id'] }}">
                                        {{ $mat['name'] }}{{ $mat['internal_code'] ? ' · '.$mat['internal_code'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Cantidad --}}
                            <input type="number" min="0.001" step="0.001"
                                wire:model="materialConsumptions.{{ $index }}.qty"
                                placeholder="Cant."
                                class="w-28 flex-shrink-0 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                            {{-- Eliminar fila --}}
                            @if(count($materialConsumptions) > 1)
                            <button type="button" wire:click="removeMaterialRow({{ $index }})"
                                class="flex-shrink-0 text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                title="Eliminar fila">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                            @else
                            <span class="w-4 flex-shrink-0"></span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Botones --}}
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="$set('showProcessModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    @if($processModalIsLast)
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors">
                        <x-heroicon-o-check-badge class="w-4 h-4" wire:loading.remove wire:target="saveProcessStage" />
                        <span wire:loading.remove wire:target="saveProcessStage">Finalizar orden</span>
                        <span wire:loading wire:target="saveProcessStage">Finalizando...</span>
                    </button>
                    @else
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors">
                        <x-heroicon-o-arrow-right-circle class="w-4 h-4" wire:loading.remove wire:target="saveProcessStage" />
                        <span wire:loading.remove wire:target="saveProcessStage">Avanzar etapa</span>
                        <span wire:loading wire:target="saveProcessStage">Guardando...</span>
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Modal Crear / Editar Orden ──────────────────────────── --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'"
        x-data="{ show: true }" x-show="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black/10 dark:ring-white/10 w-full max-w-2xl max-h-[92vh] overflow-y-auto my-auto"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            {{-- Cabecera fija --}}
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $editingId ? 'Editar orden #'.str_pad($editingId, 4, '0', STR_PAD_LEFT) : 'Nueva orden de producción' }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Complete la información de la orden</p>
                    </div>
                </div>
                <button wire:click="$set('showModal', false)"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Formulario --}}
            <form wire:submit.prevent="save" class="p-6 space-y-6">

                {{-- Sección: Datos básicos --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">
                        Datos de la orden
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Fecha de orden --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <x-heroicon-o-calendar-days class="w-4 h-4 inline-block mr-1 -mt-0.5 text-gray-400" />
                                Fecha de orden <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="date" type="date"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('date') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fecha de entrega --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <x-heroicon-o-truck class="w-4 h-4 inline-block mr-1 -mt-0.5 text-gray-400" />
                                Fecha de entrega <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="delivery_date" type="date"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('delivery_date') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Ref. orden del cliente --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Referencia del cliente
                                <span class="font-normal text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <input wire:model="customer_order" type="text"
                                placeholder="Ej: OC-2024-001"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('customer_order') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Pedido de venta --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pedido de venta
                                <span class="font-normal text-gray-400 text-xs">(opcional)</span>
                            </label>
                            <input wire:model="sales_order" type="text"
                                placeholder="Ej: PV-2024-045"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('sales_order') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                {{-- Sección: Producto --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">
                        Producto a producir
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Producto --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <x-heroicon-o-cube class="w-4 h-4 inline-block mr-1 -mt-0.5 text-gray-400" />
                                Producto <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="item_id"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="">— Seleccione un producto —</option>
                                @foreach($productItems as $item)
                                    <option value="{{ $item['id'] }}">
                                        {{ $item['name'] }}
                                        @if($item['internal_code']) · {{ $item['internal_code'] }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @if(count($productItems) === 0)
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                                    <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                                    No hay productos de tipo "Producido" activos.
                                </p>
                            @endif
                            @error('item_id') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Ruta de procesos del producto seleccionado --}}
                        @if($item_id)
                        <div class="sm:col-span-2" wire:loading.class="opacity-50" wire:target="updatedItemId">
                            @if(count($itemProcesses) > 0)
                                <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 overflow-hidden">
                                    <div class="flex items-center gap-2 px-4 py-2.5 border-b border-blue-200 dark:border-blue-800 bg-blue-100/60 dark:bg-blue-900/40">
                                        <x-heroicon-o-map class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">
                                            Ruta de producción — {{ count($itemProcesses) }} {{ count($itemProcesses) === 1 ? 'proceso' : 'procesos' }}
                                        </span>
                                    </div>
                                    <div class="divide-y divide-blue-100 dark:divide-blue-800/60">
                                        @foreach($itemProcesses as $proc)
                                        <div x-data="{ open: false }" class="px-4 py-3">
                                            <button type="button" @click="open = !open"
                                                class="w-full flex items-center justify-between gap-3 text-left">
                                                <div class="flex items-center gap-3">
                                                    {{-- Número de orden --}}
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 dark:bg-blue-500 text-white text-xs font-bold flex-shrink-0">
                                                        {{ $proc['route_order'] }}
                                                    </span>
                                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                                        {{ $proc['process_name'] }}
                                                    </span>
                                                    @if(count($proc['fields']) > 0)
                                                    <span class="text-xs text-blue-500 dark:text-blue-400">
                                                        {{ count($proc['fields']) }} {{ count($proc['fields']) === 1 ? 'campo' : 'campos' }}
                                                    </span>
                                                    @else
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">sin campos</span>
                                                    @endif
                                                </div>
                                                @if(count($proc['fields']) > 0)
                                                <x-heroicon-o-chevron-down class="w-4 h-4 text-blue-400 transition-transform duration-200 flex-shrink-0"
                                                    x-bind:class="open ? 'rotate-180' : ''" />
                                                @endif
                                            </button>

                                            {{-- Campos del proceso --}}
                                            @if(count($proc['fields']) > 0)
                                            <div x-show="open" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 pl-9">
                                                @foreach($proc['fields'] as $field)
                                                @php
                                                    $typeLabels = [
                                                        'text'     => ['label' => 'Texto', 'icon' => 'heroicon-o-pencil'],
                                                        'number'   => ['label' => 'Número', 'icon' => 'heroicon-o-hashtag'],
                                                        'date'     => ['label' => 'Fecha', 'icon' => 'heroicon-o-calendar'],
                                                        'select'   => ['label' => 'Selección', 'icon' => 'heroicon-o-list-bullet'],
                                                        'textarea' => ['label' => 'Área de texto', 'icon' => 'heroicon-o-bars-3-bottom-left'],
                                                        'checkbox' => ['label' => 'Casilla', 'icon' => 'heroicon-o-check-circle'],
                                                    ];
                                                    $typeInfo = $typeLabels[$field['type']] ?? ['label' => $field['type'], 'icon' => 'heroicon-o-question-mark-circle'];
                                                @endphp
                                                <div class="flex items-start gap-2 px-3 py-2 rounded-md bg-white dark:bg-gray-700/50 border border-blue-100 dark:border-blue-800/40">
                                                    <x-dynamic-component :component="$typeInfo['icon']" class="w-3.5 h-3.5 text-blue-400 dark:text-blue-500 mt-0.5 flex-shrink-0" />
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $field['label'] }}</p>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $typeInfo['label'] }}
                                                            @if($field['type'] === 'select' && count($field['options']) > 0)
                                                                · {{ count($field['options']) }} opciones
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2 px-4 py-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 flex-shrink-0" />
                                    <p class="text-xs text-amber-700 dark:text-amber-400">
                                        Este producto no tiene procesos asignados. Puedes asignarlos desde el módulo de <strong>Procesos</strong>.
                                    </p>
                                </div>
                            @endif
                        </div>
                        @endif

                        {{-- Cantidad --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <x-heroicon-o-hashtag class="w-4 h-4 inline-block mr-1 -mt-0.5 text-gray-400" />
                                Cantidad solicitada <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="requested_qty" type="number" min="1"
                                placeholder="0"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('requested_qty') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                {{-- Sección: Cliente --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">
                        Cliente
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <x-heroicon-o-user class="w-4 h-4 inline-block mr-1 -mt-0.5 text-gray-400" />
                            Cliente / Contacto <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="warehouse_customer_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">— Seleccione un cliente —</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact['id'] }}">{{ $contact['fullName'] }}</option>
                            @endforeach
                        </select>
                        @if(count($contacts) === 0)
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                                <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                                No hay contactos activos disponibles.
                            </p>
                        @endif
                        @error('warehouse_customer_id') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Indicadores automáticos al crear --}}
                @if(!$editingId)
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-information-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        Estado inicial:
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400">
                            Registrado
                        </span>
                    </div>
                    @if($productive_stage !== null && !empty($itemProcesses))
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-arrow-right-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        Primera etapa:
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">
                            {{ $itemProcesses[0]['process_name'] }}
                        </span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Botones --}}
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors">
                        <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="save" />
                        <span wire:loading.remove wire:target="save">
                            {{ $editingId ? 'Actualizar orden' : 'Crear orden' }}
                        </span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
