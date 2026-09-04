@if($showTaskModal)
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editingTaskId ? 'Editar tarea' : 'Nueva tarea' }}</h3>
            <button wire:click="$set('showTaskModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Título de la tarea</label>
                <input wire:model="title" type="text" placeholder="Ej: Instalar luminarias piso 1"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Descripción</label>
                <textarea wire:model="description" rows="3" placeholder="Detalle de la actividad..."
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Departamento</label>
                    <select wire:model="departmentId" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecciona...</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('departmentId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Prioridad</label>
                    <select wire:model="priority" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        @foreach(\App\Models\Tenant\TaskPlanner\Task::PRIORITIES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Duración estimada</label>
                    <div class="flex items-center gap-2">
                        <input wire:model="estimatedHours" type="number" min="0" placeholder="Horas"
                            class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <span class="text-xs text-gray-400">h</span>
                        <input wire:model="estimatedMinutes" type="number" min="0" max="59" placeholder="Min"
                            class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <span class="text-xs text-gray-400">min</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Fecha sugerida</label>
                    <input wire:model="suggestedDate" type="date" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Fecha límite</label>
                    <input wire:model="deadlineDate" type="date" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('deadlineDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hora límite</label>
                    <input wire:model="deadlineTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Responsables</label>
                <select wire:model="assignedUserIds" multiple size="5"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @foreach($assignableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Mantén Ctrl (o Cmd) presionado para seleccionar varios.</p>
                @error('assignedUserIds') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Ubicación</label>
                    <select wire:model="locationType" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <option value="empresa">Instalaciones de la empresa</option>
                        <option value="cliente">Cliente</option>
                        <option value="otra">Otra ubicación</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Dirección / referencia</label>
                    <input wire:model="location" type="text" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            @if($locationType !== 'empresa')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Desplazamiento ida (min)</label>
                    <input wire:model="travelBefore" type="number" min="0" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Desplazamiento regreso (min)</label>
                    <input wire:model="travelAfter" type="number" min="0" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Origen (opcional)</label>
                    <select wire:model="originType" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <option value="">Sin origen específico</option>
                        @foreach(\App\Models\Tenant\TaskPlanner\Task::ORIGIN_TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($originType === 'proyecto')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Proyecto relacionado</label>
                    <select wire:model="originProjectId" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecciona...</option>
                        @foreach($projectsForOrigin as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @if(!$editingTaskId)
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Depende de (opcional)</label>
                <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2">
                    @forelse($allOpenTasksForDependency as $depTask)
                    <label class="inline-flex items-center gap-1.5 text-xs bg-gray-50 dark:bg-gray-700 rounded-full px-2 py-1 cursor-pointer">
                        <input type="checkbox" wire:model="dependsOnTaskIds" value="{{ $depTask->id }}" class="rounded border-gray-300 text-indigo-600">
                        {{ $depTask->title }}
                    </label>
                    @empty
                    <span class="text-xs text-gray-400">No hay otras tareas abiertas.</span>
                    @endforelse
                </div>
            </div>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="$set('showTaskModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
            <button wire:click="saveTask" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar tarea</button>
        </div>
    </div>
</div>
@endif
