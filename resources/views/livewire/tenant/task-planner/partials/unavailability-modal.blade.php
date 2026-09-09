@if($showUnavailabilityModal)
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar indisponibilidad</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Un rato en que un trabajador NO puede trabajar (permiso, cita, incapacidad…). El sistema lo tiene en cuenta al programar y avisa si ya había tareas ahí.</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Trabajador <x-help-tip text="Persona que no estará disponible en ese rango." /></label>
                <select wire:model="unavailUserId" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecciona...</option>
                    @foreach($assignableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('unavailUserId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Desde <x-help-tip text="Fecha y hora exactas en que empieza la ausencia." /></label>
                    <input wire:model="unavailStart" type="datetime-local" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hasta <x-help-tip text="Fecha y hora en que el trabajador vuelve a estar disponible." /></label>
                    <input wire:model="unavailEnd" type="datetime-local" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('unavailEnd') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Motivo <x-help-tip text="Tipo de ausencia: permiso personal, cita médica, incapacidad, vacaciones, reunión, trabajo externo u otro." /></label>
                <select wire:model="unavailType" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @foreach(\App\Models\Tenant\TaskPlanner\EmployeeUnavailability::TYPES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Observación (opcional) <x-help-tip text="Nota adicional: a qué hora regresa, si dejó algo pendiente, etc." /></label>
                <input wire:model="unavailReason" type="text" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="$set('showUnavailabilityModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
            <button wire:click="saveUnavailability" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar</button>
        </div>
    </div>
</div>
@endif
