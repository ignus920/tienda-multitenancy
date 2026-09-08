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
                <div x-data="taskPlannerChoices()" x-init="init($el)">
                    <select x-ref="select" wire:model="assignedUserIds" multiple
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
                        @foreach($assignableUsers as $user)
                        <option value="{{ $user->id }}" @selected(in_array($user->id, $assignedUserIds))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
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



            <!-- RECURRENCIA -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Repetir</label>
                    <select wire:model.live="recurrenceType" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        <option value="none">No se repite</option>
                        <option value="daily">Cada día laboral</option>
                        <option value="weekly">Cada semana</option>
                        <option value="monthly">Cada mes</option>
                    </select>
                </div>
                @if($recurrenceType === 'weekly')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Día de la semana</label>
                    <select wire:model="recurrenceWeekday" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        @foreach(['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $i => $d)
                        <option value="{{ $i }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($recurrenceType === 'monthly')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Día del mes (1–28)</label>
                    <input wire:model="recurrenceMonthday" type="number" min="1" max="28" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
                @endif
            </div>
            @if($recurrenceType !== 'none')
            <p class="text-[11px] text-gray-400 -mt-2">Esta tarea sirve de plantilla: el sistema creará una copia nueva (sin programar) en cada repetición. Se genera automáticamente cada madrugada.</p>
            @endif

            <hr class="border-gray-100 dark:border-gray-700 my-4">

            <!-- CHECKLIST -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="flex items-center text-xs font-semibold text-gray-600 dark:text-gray-300">
                        Checklist (Paso a paso)
                        <div x-data="{ show: false }" class="relative ml-1 flex items-center">
                            <button type="button" @mouseenter="show = true" @mouseleave="show = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                            <div x-show="show" x-cloak class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 w-48 p-2 bg-gray-800 text-white text-[10px] rounded shadow-lg z-50">
                                Lista de pasos que el trabajador deberá ir marcando mientras ejecuta la tarea.
                            </div>
                        </div>
                    </label>
                    <button wire:click="addChecklistItem" type="button" class="px-2 py-1 bg-white border border-gray-300 text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 rounded hover:bg-gray-50 text-xs flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                        Añadir paso
                    </button>
                </div>
                <div class="space-y-2">
                    @foreach($tempChecklists as $idx => $chk)
                    <div class="flex items-center gap-2">
                        <input wire:model="tempChecklists.{{ $idx }}.description" type="text" placeholder="Paso a realizar..." class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <label class="flex items-center gap-1 shrink-0 text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap" title="Si es obligatorio, la tarea no se puede terminar sin marcarlo">
                            <input type="checkbox" wire:model="tempChecklists.{{ $idx }}.is_required" class="rounded border-gray-300 text-indigo-600">
                            Obligatorio
                        </label>
                        <button wire:click="$dispatch('swal:confirm', { action: 'removeChecklistItem', params: {{ $idx }}, title: '¿Quitar paso?', text: 'Se eliminará de la lista.' })" type="button" class="p-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- MATERIALES -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="flex items-center text-xs font-semibold text-gray-600 dark:text-gray-300">
                        Materiales y Herramientas requeridas
                        <div x-data="{ show: false }" class="relative ml-1 flex items-center">
                            <button type="button" @mouseenter="show = true" @mouseleave="show = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                            <div x-show="show" x-cloak class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 w-48 p-2 bg-gray-800 text-white text-[10px] rounded shadow-lg z-50">
                                Busca insumos del inventario o anota texto libre para herramientas.
                            </div>
                        </div>
                    </label>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 mb-3">
                    <div class="flex gap-2 mb-2 border-b border-gray-200 dark:border-gray-600 pb-2">
                        <button wire:click="$set('materialSearchMode', 'inventory')" type="button" class="text-xs px-2 py-1 rounded {{ $materialSearchMode === 'inventory' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-500 hover:bg-gray-200' }}">Desde Inventario</button>
                        <button wire:click="$set('materialSearchMode', 'free')" type="button" class="text-xs px-2 py-1 rounded {{ $materialSearchMode === 'free' ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-500 hover:bg-gray-200' }}">Genérico / Herramienta</button>
                    </div>

                    @if($materialSearchMode === 'inventory')
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="searchMaterial" type="text" placeholder="Buscar por código o nombre (mín 2 letras)..." class="block w-full border border-gray-200 bg-white rounded-lg px-3 py-1.5 text-sm">
                        @if(!empty($searchMaterialResults))
                        <ul class="absolute z-50 w-full bg-white border border-gray-200 shadow-lg rounded-lg mt-1 max-h-40 overflow-y-auto">
                            @foreach($searchMaterialResults as $res)
                            <li wire:click="addInventoryMaterial({{ $res['id'] }}, '{{ $res['name'] }}')" class="px-3 py-2 text-xs cursor-pointer hover:bg-indigo-50 border-b border-gray-50">
                                <strong>{{ $res['internal_code'] }}</strong> - {{ $res['name'] }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @else
                    <div class="flex items-center gap-2">
                        <input wire:model="freeMaterialName" type="text" placeholder="Ej: Taladro inalámbrico..." class="block w-full border border-gray-200 bg-white rounded-lg px-3 py-1.5 text-sm">
                        <input wire:model="freeMaterialQty" type="number" min="0.01" step="0.01" class="block w-20 border border-gray-200 bg-white rounded-lg px-2 py-1.5 text-sm" placeholder="Cant.">
                        <button wire:click="addFreeMaterial" type="button" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold">Añadir</button>
                    </div>
                    @endif
                </div>

                <div class="space-y-1">
                    @foreach($tempMaterials as $idx => $mat)
                    <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            @if($mat['item_id'])
                                <span class="bg-blue-100 text-blue-800 text-[10px] px-1.5 py-0.5 rounded mr-1">Inventario</span>
                                <span>{{ $mat['inventory_name'] }}</span>
                            @else
                                <span class="bg-gray-200 text-gray-800 text-[10px] px-1.5 py-0.5 rounded mr-1">Genérico</span>
                                <span>{{ $mat['name'] }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <input wire:model="tempMaterials.{{ $idx }}.estimated_quantity" type="number" step="0.01" class="block w-16 border border-gray-200 rounded px-1 py-0.5 text-xs text-center" title="Cantidad estimada">
                            <button wire:click="$dispatch('swal:confirm', { action: 'removeMaterial', params: {{ $idx }}, title: '¿Quitar material?', text: 'Se eliminará de la lista.' })" type="button" class="text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- ADJUNTOS -->
            <div class="mt-6">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Archivos Adjuntos</label>
                <input wire:model="tempAttachments" type="file" multiple class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mb-2">
                
                @if(count($existingAttachments) > 0)
                <ul class="text-xs space-y-1 mb-2">
                    @foreach($existingAttachments as $att)
                    <li class="flex items-center justify-between bg-gray-50 p-1.5 rounded border border-gray-200">
                        <a href="{{ tenant_asset(ltrim($att['file_path'], '/')) }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">{{ $att['file_name'] }}</a>
                        <button wire:click="$dispatch('swal:confirm', { action: 'deleteExistingAttachment', params: {{ $att['id'] }}, title: '¿Borrar archivo?', text: 'Se eliminará permanentemente.' })" type="button" class="text-red-500 hover:text-red-700">Borrar</button>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="$set('showTaskModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
            <button wire:click="saveTask" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar tarea</button>
        </div>
    </div>
</div>
@endif
