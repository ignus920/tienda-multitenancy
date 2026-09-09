@if($showScheduleModal)
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Programar tarea</h3>
            <button wire:click="$set('showScheduleModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Fecha <x-help-tip text="Día en que el trabajador realizará la tarea." /></label>
                    <input wire:model="scheduleDate" type="date" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('scheduleDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hora inicio <x-help-tip text="Hora a la que empieza el bloque reservado en la agenda del trabajador (debe ser el mismo día)." /></label>
                        <input wire:model="scheduleStartTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hora fin <x-help-tip text="Hora a la que termina el bloque. Suele calcularse sola según la duración estimada de la tarea." /></label>
                        <input wire:model="scheduleEndTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        @error('scheduleEndTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button wire:click="checkScheduleConflicts" type="button"
                    class="px-3 py-2 text-xs font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100">
                    Verificar disponibilidad
                </button>
                <button wire:click="findSlots" type="button"
                    class="px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg hover:bg-emerald-100">
                    Buscar espacio libre
                </button>
            </div>
            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-tight -mt-1">
                <span class="font-semibold text-gray-500 dark:text-gray-400">Verificar disponibilidad:</span> ¿el trabajador está libre a la hora que pusiste? Avisa si hay choque, permiso o está fuera de horario (no bloquea).
                <span class="font-semibold text-gray-500 dark:text-gray-400">Buscar espacio libre:</span> el sistema te propone los primeros huecos disponibles antes de la fecha límite.
            </p>

            @if(!empty($suggestedSlots))
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 space-y-1">
                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Primeros huecos disponibles</p>
                @foreach($suggestedSlots as $i => $slot)
                <button wire:click="applySlot({{ $i }})" type="button"
                    class="w-full text-left text-xs px-2 py-1.5 rounded-md bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-700 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-gray-700 dark:text-gray-200">
                    {{ $slot['label'] }}
                </button>
                @endforeach
            </div>
            @endif

            @if(!empty($scheduleConflicts))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 space-y-2">
                <p class="text-xs font-bold text-red-700 dark:text-red-300">⚠ Conflicto de horario</p>
                @foreach($scheduleConflicts as $userId => $conflicts)
                <div class="text-xs text-red-600 dark:text-red-300">
                    <span class="font-semibold">{{ optional(\App\Models\Auth\User::find($userId))->name }}:</span>
                    <ul class="list-disc list-inside">
                        @foreach($conflicts as $c)
                        <li>{{ $c['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
                <p class="text-[11px] text-red-500">Puedes ajustar el horario o confirmar de todas formas si Gerencia decide priorizar esta tarea.</p>
            </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Motivo de reprogramación (si aplica) <x-help-tip text="Solo si estás moviendo una tarea que YA estaba programada. Queda registrado en el historial para saber por qué no se cumplió el plan original." /></label>
                <select wire:model="rescheduleReason" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">No aplica</option>
                    @foreach(\App\Models\Tenant\TaskPlanner\TaskSchedule::RESCHEDULE_REASONS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="$set('showScheduleModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
            <button wire:click="confirmSchedule" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                {{ !empty($scheduleConflicts) ? 'Confirmar de todas formas' : 'Confirmar programación' }}
            </button>
        </div>
    </div>
</div>
@endif
