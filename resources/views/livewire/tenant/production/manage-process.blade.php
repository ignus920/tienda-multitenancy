<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 relative">
    <div class="w-full space-y-6">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                        <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Procesos de Producción</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona los procesos del módulo de producción.</p>
                    </div>
                </div>
                <button wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Nuevo proceso
                </button>
            </div>
        </div>

        <!-- Flash -->
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

            <!-- Barra de búsqueda -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="relative w-full sm:max-w-xs">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar proceso..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
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

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Notas previas</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Consume inventario</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Genera documentos</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($processes as $process)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $process->id }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $process->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $process->previous_notes ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($process->inventory_consumption)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400">Sí</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($process->documents_generates)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-400">Sí</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleStatus({{ $process->id }})"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-colors
                                    {{ $process->status
                                        ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-400 hover:bg-green-200'
                                        : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-400 hover:bg-red-200' }}">
                                    {{ $process->status ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openFields({{ $process->id }})"
                                        class="text-amber-500 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 transition-colors"
                                        title="Campos del proceso">
                                        <x-heroicon-o-list-bullet class="w-4 h-4" />
                                    </button>
                                    <button wire:click="edit({{ $process->id }})"
                                        class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                                        title="Editar">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button wire:click="delete({{ $process->id }})"
                                        wire:confirm="¿Está seguro de eliminar este proceso?"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                        title="Eliminar">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                <x-heroicon-o-cog-6-tooth class="w-10 h-10 mx-auto mb-2 opacity-40" />
                                <p class="text-sm">No hay procesos registrados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($processes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $processes->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Crear / Editar -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'"
        x-data="{ show: true }" x-show="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl ring-1 ring-black/10 dark:ring-white/10 max-w-lg w-full max-h-[90vh] overflow-y-auto"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                <!-- Cabecera -->
                <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between rounded-t-lg">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $editingId ? 'Editar proceso' : 'Nuevo proceso' }}
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="save" class="p-6 space-y-4">

                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text"
                            placeholder="Nombre del proceso"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        @error('name') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Notas previas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas previas
                        </label>
                        <textarea wire:model="previous_notes" rows="3"
                            placeholder="Notas o instrucciones previas al proceso..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"></textarea>
                        @error('previous_notes') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Operarios asignados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <x-heroicon-o-users class="w-4 h-4 inline-block mr-1 -mt-0.5" />
                            Operarios responsables
                            <span class="font-normal text-gray-400 text-xs">(opcional)</span>
                        </label>

                        @if(count($operatorUsers) === 0)
                            <p class="text-xs text-gray-400 italic">No hay usuarios con perfil operario disponibles.</p>
                        @else
                            <div class="grid grid-cols-1 gap-1.5 max-h-40 overflow-y-auto pr-1
                                border border-gray-200 dark:border-gray-600 rounded-lg p-2 bg-gray-50 dark:bg-gray-700/50">
                                @foreach($operatorUsers as $user)
                                <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg cursor-pointer
                                    hover:bg-white dark:hover:bg-gray-700 transition-colors
                                    {{ in_array((string)$user['id'], array_map('strval', $selectedOperators))
                                        ? 'bg-white dark:bg-gray-700 ring-1 ring-amber-400'
                                        : '' }}">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedOperators"
                                        value="{{ $user['id'] }}"
                                        class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user['name'] }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user['email'] }}</p>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @if(count($selectedOperators) > 0)
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                {{ count($selectedOperators) }} operario(s) seleccionado(s)
                            </p>
                            @endif
                        @endif
                    </div>

                    <!-- Checkboxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <input wire:model="inventory_consumption" type="checkbox" value="1"
                                class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Consume inventario</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Descuenta materiales del stock</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <input wire:model="documents_generates" type="checkbox" value="1"
                                class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Genera documentos</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Crea documentos al ejecutarse</p>
                            </div>
                        </label>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input wire:model="status" type="checkbox" value="1" class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors">
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId ? 'Actualizar' : 'Guardar' }}
                            </span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>
                </form>

            </div>
    </div>
    @endif

    {{-- ── Modal Campos del Proceso ─────────────────────────── --}}
    @if($showFieldsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'"
        x-data="{ show: true }" x-show="show"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl ring-1 ring-black/10 dark:ring-white/10 w-full max-w-4xl max-h-[90vh] overflow-y-auto my-auto"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                {{-- Cabecera --}}
                <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                            <x-heroicon-o-list-bullet class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Campos del proceso</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedProcessName }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showFieldsModal', false)"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-6">

                    {{-- Formulario crear/editar campo --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 p-5 space-y-5">

                        {{-- Título del panel --}}
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                                @if($editingFieldId)
                                    <x-heroicon-o-pencil class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                @else
                                    <x-heroicon-o-plus-circle class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                @endif
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ $editingFieldId ? 'Editando campo' : 'Nuevo campo' }}
                            </h4>
                        </div>

                        {{-- PASO 1: Nombre visible del campo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                ¿Cómo se llamará este campo?
                                <span class="text-red-500">*</span>
                            </label>
                            <input wire:model.live="fieldLabel" type="text"
                                placeholder="Ej: Tamaño del papel, Color, Cantidad..."
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('fieldLabel') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- PASO 2: Tipo de campo - tarjetas visuales --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                ¿Qué tipo de información va a capturar?
                                <span class="text-red-500">*</span>
                            </label>
                            @php
                                $typeCards = [
                                    'text' => [
                                        'icon'  => 'heroicon-o-pencil',
                                        'title' => 'Texto corto',
                                        'desc'  => 'Una línea de texto libre',
                                        'color' => 'blue',
                                    ],
                                    'text_area' => [
                                        'icon'  => 'heroicon-o-document-text',
                                        'title' => 'Texto largo',
                                        'desc'  => 'Varias líneas de descripción',
                                        'color' => 'purple',
                                    ],
                                    'select' => [
                                        'icon'  => 'heroicon-o-chevron-up-down',
                                        'title' => 'Lista de opciones',
                                        'desc'  => 'Elegir entre varias opciones fijas',
                                        'color' => 'green',
                                    ],
                                    'number' => [
                                        'icon'  => 'heroicon-o-hashtag',
                                        'title' => 'Número',
                                        'desc'  => 'Cantidad, medida o valor numérico',
                                        'color' => 'orange',
                                    ],
                                    'date' => [
                                        'icon'  => 'heroicon-o-calendar-days',
                                        'title' => 'Fecha',
                                        'desc'  => 'Día, mes y año',
                                        'color' => 'pink',
                                    ],
                                    'checkbox' => [
                                        'icon'  => 'heroicon-o-check-circle',
                                        'title' => 'Sí / No',
                                        'desc'  => 'Casilla para marcar o desmarcar',
                                        'color' => 'indigo',
                                    ],
                                ];
                                $colorMap = [
                                    'blue'   => ['border' => 'border-blue-400',   'bg'   => 'bg-blue-50 dark:bg-blue-900/20',   'icon' => 'text-blue-500 dark:text-blue-400',   'title' => 'text-blue-700 dark:text-blue-300'],
                                    'purple' => ['border' => 'border-purple-400', 'bg'   => 'bg-purple-50 dark:bg-purple-900/20','icon' => 'text-purple-500 dark:text-purple-400','title' => 'text-purple-700 dark:text-purple-300'],
                                    'green'  => ['border' => 'border-green-400',  'bg'   => 'bg-green-50 dark:bg-green-900/20',  'icon' => 'text-green-500 dark:text-green-400',  'title' => 'text-green-700 dark:text-green-300'],
                                    'orange' => ['border' => 'border-orange-400', 'bg'   => 'bg-orange-50 dark:bg-orange-900/20','icon' => 'text-orange-500 dark:text-orange-400','title' => 'text-orange-700 dark:text-orange-300'],
                                    'pink'   => ['border' => 'border-pink-400',   'bg'   => 'bg-pink-50 dark:bg-pink-900/20',   'icon' => 'text-pink-500 dark:text-pink-400',   'title' => 'text-pink-700 dark:text-pink-300'],
                                    'indigo' => ['border' => 'border-indigo-400', 'bg'   => 'bg-indigo-50 dark:bg-indigo-900/20','icon' => 'text-indigo-500 dark:text-indigo-400','title' => 'text-indigo-700 dark:text-indigo-300'],
                                ];
                            @endphp
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($typeCards as $val => $card)
                                @php $c = $colorMap[$card['color']]; @endphp
                                <label class="cursor-pointer group">
                                    <input type="radio" wire:model.live="fieldType" value="{{ $val }}" class="sr-only peer">
                                    <div class="flex flex-col items-center text-center p-3 rounded-xl border-2
                                        {{ $fieldType === $val ? $c['border'].' '.$c['bg'] : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500' }}
                                        transition-all duration-150 select-none">
                                        <div class="mb-1.5 {{ $fieldType === $val ? $c['icon'] : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500' }} transition-colors">
                                            <x-dynamic-component :component="$card['icon']" class="w-6 h-6 mx-auto" />
                                        </div>
                                        <span class="text-xs font-semibold {{ $fieldType === $val ? $c['title'] : 'text-gray-600 dark:text-gray-300' }} leading-tight">
                                            {{ $card['title'] }}
                                        </span>
                                        <span class="text-xs {{ $fieldType === $val ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }} leading-tight mt-0.5">
                                            {{ $card['desc'] }}
                                        </span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('fieldType') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Opciones para lista (solo tipo select) --}}
                        @if($fieldType === 'select')
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 p-4">
                            <label class="block text-sm font-medium text-green-800 dark:text-green-300 mb-1">
                                <x-heroicon-o-list-bullet class="w-4 h-4 inline-block mr-1 -mt-0.5" />
                                ¿Cuáles son las opciones disponibles?
                                <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-green-700 dark:text-green-400 mb-2">Escríbelas separadas por coma.</p>
                            <input wire:model.live="fieldOptions" type="text"
                                placeholder="Ej: Pequeño, Mediano, Grande, Extra grande"
                                class="w-full px-3 py-2 text-sm border border-green-300 dark:border-green-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500">
                            @error('fieldOptions') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        {{-- Condicional: mostrar solo cuando otro campo tenga cierto valor --}}
                        @php
                            $parentSelectFields = collect($processFields)
                                ->filter(fn($f) => $f['type'] === 'select' && $f['id'] != $editingFieldId)
                                ->values();
                        @endphp
                        @if($parentSelectFields->count() > 0)
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-4 space-y-3">
                            <label class="block text-sm font-medium text-blue-800 dark:text-blue-300">
                                <x-heroicon-o-eye class="w-4 h-4 inline-block mr-1 -mt-0.5" />
                                Mostrar solo cuando...
                                <span class="font-normal text-blue-500">(opcional)</span>
                            </label>
                            <div class="flex flex-wrap gap-2 items-center">
                                <select wire:model.live="fieldParentField"
                                    class="flex-1 min-w-0 px-3 py-2 text-sm border border-blue-300 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Siempre visible —</option>
                                    @foreach($parentSelectFields as $sf)
                                        <option value="{{ $sf['name'] }}">{{ $sf['label'] }}</option>
                                    @endforeach
                                </select>
                                @if($fieldParentField)
                                    <span class="text-sm text-blue-600 dark:text-blue-400 font-medium whitespace-nowrap">sea igual a</span>
                                    @php
                                        $parentField = $parentSelectFields->firstWhere('name', $fieldParentField);
                                        $parentOpts  = $parentField && $parentField['options']
                                            ? array_map('trim', explode(',', $parentField['options']))
                                            : [];
                                    @endphp
                                    @if(count($parentOpts) > 0)
                                        <select wire:model="fieldParentValue"
                                            class="flex-1 min-w-0 px-3 py-2 text-sm border border-blue-300 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Seleccione valor —</option>
                                            @foreach($parentOpts as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input wire:model="fieldParentValue" type="text" placeholder="valor exacto"
                                            class="flex-1 min-w-0 px-3 py-2 text-sm border border-blue-300 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @endif
                                @endif
                            </div>
                            @if($fieldParentField && $fieldParentValue)
                                <p class="text-xs text-blue-600 dark:text-blue-400">
                                    Este campo solo aparecerá cuando
                                    <strong>{{ $parentSelectFields->firstWhere('name', $fieldParentField)['label'] ?? $fieldParentField }}</strong>
                                    sea <strong>"{{ $fieldParentValue }}"</strong>.
                                </p>
                            @endif
                        </div>
                        @endif

                        {{-- Vista previa en tiempo real --}}
                        @if($fieldLabel)
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-dashed border-amber-300 dark:border-amber-700 p-4">
                            <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1">
                                <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                Vista previa — así verá el campo el operario
                            </p>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                {{ $fieldLabel }}
                            </label>
                            @if($fieldType === 'text')
                                <input type="text" disabled placeholder="Escriba aquí..."
                                    class="w-full sm:w-64 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed">
                            @elseif($fieldType === 'text_area')
                                <textarea disabled rows="3" placeholder="Escriba aquí..."
                                    class="w-full sm:w-96 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 resize-none cursor-not-allowed"></textarea>
                            @elseif($fieldType === 'select')
                                <select disabled class="w-full sm:w-64 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 cursor-not-allowed">
                                    @if($fieldOptions)
                                        @foreach(explode(',', $fieldOptions) as $opt)
                                            <option>{{ trim($opt) }}</option>
                                        @endforeach
                                    @else
                                        <option>Seleccione una opción...</option>
                                    @endif
                                </select>
                            @elseif($fieldType === 'number')
                                <input type="number" disabled placeholder="0"
                                    class="w-32 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed">
                            @elseif($fieldType === 'date')
                                <input type="date" disabled
                                    class="w-48 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed">
                            @elseif($fieldType === 'checkbox')
                                <label class="inline-flex items-center gap-2 cursor-not-allowed">
                                    <input type="checkbox" disabled class="w-4 h-4 border-gray-300 rounded text-amber-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $fieldLabel }}</span>
                                </label>
                            @endif
                        </div>
                        @endif

                        {{-- Opciones avanzadas (colapsable) --}}
                        <div x-data="{ open: {{ $editingFieldId ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                <x-heroicon-o-cog-6-tooth class="w-3.5 h-3.5" />
                                Opciones avanzadas
                                <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Nombre interno del campo
                                    </label>
                                    <input wire:model="fieldName" type="text" placeholder="nombre_campo"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
                                    <p class="text-xs text-gray-400 mt-0.5">Se genera automáticamente. Solo minúsculas, números y _</p>
                                    @error('fieldName') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Clases de estilo
                                        <span class="font-normal text-gray-400">(opcional)</span>
                                    </label>
                                    <input wire:model="fieldClass" type="text" placeholder="w-full, col-span-2..."
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <p class="text-xs text-gray-400 mt-0.5">Para controlar el ancho en el formulario de producción</p>
                                </div>
                            </div>
                        </div>

                        {{-- Estado + botones --}}
                        <div class="flex items-center justify-between pt-1 border-t border-gray-200 dark:border-gray-600">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="fieldStatus" type="checkbox" value="1"
                                    class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Campo activo</span>
                            </label>
                            <div class="flex gap-2">
                                @if($editingFieldId)
                                <button type="button" wire:click="cancelFieldEdit"
                                    class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Cancelar
                                </button>
                                @endif
                                <button type="button" wire:click="saveField" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                                    <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="saveField" />
                                    <span wire:loading.remove wire:target="saveField">
                                        {{ $editingFieldId ? 'Actualizar campo' : 'Agregar campo' }}
                                    </span>
                                    <span wire:loading wire:target="saveField">Guardando...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de campos --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Campos configurados
                            <span class="ml-1 text-xs bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                {{ count($processFields) }}
                            </span>
                        </h4>

                        @if(count($processFields) === 0)
                            <div class="text-center py-8 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 dark:text-gray-500">
                                <x-heroicon-o-list-bullet class="w-8 h-8 mx-auto mb-2 opacity-40" />
                                <p class="text-sm">No hay campos configurados para este proceso.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre interno</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Etiqueta</th>
                                            <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Opciones</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Condición</th>
                                            <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                            <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($processFields as $field)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $editingFieldId == $field['id'] ? 'ring-2 ring-inset ring-amber-400' : '' }}">
                                            <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">{{ $field['id'] }}</td>
                                            <td class="px-3 py-2.5 font-mono text-xs text-gray-800 dark:text-gray-200">{{ $field['name'] }}</td>
                                            <td class="px-3 py-2.5 text-gray-900 dark:text-white">{{ $field['label'] }}</td>
                                            <td class="px-3 py-2.5 text-center">
                                                @php
                                                    $typeColors = [
                                                        'text'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                                        'text_area' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400',
                                                        'select'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'number'    => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400',
                                                        'date'      => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-400',
                                                        'checkbox'  => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$field['type']] ?? 'bg-gray-100 text-gray-600' }}">
                                                    {{ $field['type'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400 max-w-[180px] truncate">
                                                {{ $field['options'] ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400">
                                                @if($field['parent_field'])
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-mono whitespace-nowrap">
                                                        <x-heroicon-o-eye class="w-3 h-3" />
                                                        {{ $field['parent_field'] }} = {{ $field['parent_value'] }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <button wire:click="toggleFieldStatus({{ $field['id'] }})"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-colors
                                                    {{ $field['status'] ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 hover:bg-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 hover:bg-red-200' }}">
                                                    {{ $field['status'] ? 'Activo' : 'Inactivo' }}
                                                </button>
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button wire:click="editField({{ $field['id'] }})"
                                                        class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 transition-colors"
                                                        title="Editar">
                                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                                    </button>
                                                    <button wire:click="deleteField({{ $field['id'] }})"
                                                        wire:confirm="¿Eliminar este campo?"
                                                        class="text-red-500 hover:text-red-700 dark:text-red-400 transition-colors"
                                                        title="Eliminar">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ── Vista previa del formulario completo ────────────── --}}
                    @if(count($processFields) > 0)
                    @php $activeFields = array_filter($processFields, fn($f) => $f['status']); @endphp
                    <div x-data="{ open: false }" class="rounded-xl border border-amber-200 dark:border-amber-800 overflow-hidden">

                        {{-- Cabecera colapsable --}}
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between px-5 py-3.5 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div class="p-1.5 bg-amber-200 dark:bg-amber-800 rounded-lg">
                                    <x-heroicon-o-eye class="w-4 h-4 text-amber-700 dark:text-amber-300" />
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                        Vista previa del formulario completo
                                    </p>
                                    <p class="text-xs text-amber-600 dark:text-amber-500">
                                        Así verá el operario el formulario "{{ $selectedProcessName }}"
                                        — {{ count($activeFields) }} campo(s) activo(s)
                                    </p>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-down class="w-4 h-4 text-amber-600 dark:text-amber-400 transition-transform flex-shrink-0"
                                x-bind:class="open ? 'rotate-180' : ''" />
                        </button>

                        {{-- Contenido del formulario simulado --}}
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="bg-white dark:bg-gray-800 border-t border-amber-200 dark:border-amber-800">

                            {{-- Barra de aviso --}}
                            <div class="px-5 py-2.5 bg-amber-50/50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-800/50 flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                <x-heroicon-o-information-circle class="w-3.5 h-3.5 flex-shrink-0" />
                                Esta es solo una simulación visual. Los campos están deshabilitados.
                            </div>

                            {{-- Formulario de producción simulado --}}
                            <div class="p-6">
                                {{-- Encabezado del formulario de producción --}}
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-lg">
                                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            Proceso: {{ $selectedProcessName }}
                                        </h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Complete la información del proceso para esta orden
                                        </p>
                                    </div>
                                </div>

                                @if(count($activeFields) === 0)
                                    <div class="text-center py-6 text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-eye-slash class="w-8 h-8 mx-auto mb-2 opacity-40" />
                                        <p class="text-sm">No hay campos activos para mostrar.</p>
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        @foreach($activeFields as $field)
                                        @php
                                            $colClass = $field['class'] ?? '';
                                            // Detectar si la clase indica ancho completo
                                            $isFullWidth = str_contains($colClass, 'col-span-2') || $field['type'] === 'text_area';
                                        @endphp
                                        <div class="{{ $isFullWidth ? 'sm:col-span-2' : '' }}">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                                {{ $field['label'] }}
                                            </label>

                                            @if($field['type'] === 'text')
                                                <input type="text" disabled placeholder="Escriba aquí..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed {{ $colClass }}">

                                            @elseif($field['type'] === 'text_area')
                                                <textarea disabled rows="3" placeholder="Escriba aquí..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 resize-none cursor-not-allowed {{ $colClass }}"></textarea>

                                            @elseif($field['type'] === 'select')
                                                <select disabled
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed {{ $colClass }}">
                                                    @if($field['options'])
                                                        <option value="">— Seleccione —</option>
                                                        @foreach(explode(',', $field['options']) as $opt)
                                                            <option>{{ trim($opt) }}</option>
                                                        @endforeach
                                                    @else
                                                        <option>Seleccione una opción...</option>
                                                    @endif
                                                </select>

                                            @elseif($field['type'] === 'number')
                                                <input type="number" disabled placeholder="0"
                                                    class="w-full sm:w-40 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed {{ $colClass }}">

                                            @elseif($field['type'] === 'date')
                                                <input type="date" disabled
                                                    class="w-full sm:w-52 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 cursor-not-allowed {{ $colClass }}">

                                            @elseif($field['type'] === 'checkbox')
                                                <div class="flex items-center gap-2 mt-1">
                                                    <input type="checkbox" disabled
                                                        class="w-4 h-4 border-gray-300 rounded text-amber-600 cursor-not-allowed">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400 select-none">
                                                        {{ $field['label'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- Botón guardar simulado --}}
                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                        <button type="button" disabled
                                            class="inline-flex items-center gap-2 px-5 py-2 bg-amber-600 opacity-60 text-white text-sm font-medium rounded-lg cursor-not-allowed">
                                            <x-heroicon-o-check class="w-4 h-4" />
                                            Guardar registro de proceso
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
    </div>
    @endif
</div>
