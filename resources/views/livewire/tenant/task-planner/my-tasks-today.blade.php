<div class="py-4 w-full px-4 sm:px-6 max-w-2xl mx-auto">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 mb-4">
        <h1 class="text-lg font-bold text-gray-900 dark:text-white">Mis Tareas de Hoy</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $today->translatedFormat('l j \d\e F') }}</p>
        <div class="flex gap-4 mt-2 text-xs">
            @if($daySchedule)
            <span class="text-gray-500 dark:text-gray-400">Horario: <strong class="text-gray-700 dark:text-gray-200">{{ $daySchedule->start_time }} - {{ $daySchedule->end_time }}</strong></span>
            @endif
            <span class="text-gray-500 dark:text-gray-400">Programado: <strong class="text-gray-700 dark:text-gray-200">{{ intdiv($scheduledMinutes, 60) }}h {{ $scheduledMinutes % 60 }}min</strong></span>
        </div>
    </div>

    <!-- AHORA -->
    @if($currentSchedule)
    @php $task = $currentSchedule->task; @endphp
    <div class="bg-indigo-600 rounded-2xl p-5 text-white mb-4 shadow-lg">
        <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200 mb-1">Ahora debe realizar</p>
        <h2 class="text-xl font-bold mb-1">{{ $task->title }}</h2>
        @if($task->description)
        <p class="text-sm text-indigo-100 mb-2">{{ $task->description }}</p>
        @endif
        <div class="flex items-center gap-3 text-xs text-indigo-100 mb-4">
            <span>⏱ {{ intdiv($task->estimated_minutes, 60) }}h {{ $task->estimated_minutes % 60 }}min</span>
            <span>Hasta las {{ $currentSchedule->scheduled_end->format('H:i') }}</span>
            <span class="uppercase font-bold">{{ $task->priority_label }}</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            @if($task->status === 'programada' || $task->status === 'disponible' || $task->status === 'pendiente')
            <button wire:click="startTask({{ $task->id }})" class="col-span-2 py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">INICIAR TAREA</button>
            @elseif($task->status === 'en_proceso')
            <button wire:click="openFinishModal({{ $task->id }})" class="py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">TERMINAR</button>
            <button wire:click="openPauseModal({{ $task->id }})" class="py-3 rounded-xl bg-indigo-500 text-white font-bold text-sm border border-indigo-300">PAUSAR</button>
            <button wire:click="openMoreTimeModal({{ $task->id }})" class="py-2.5 rounded-xl bg-indigo-500/60 text-white text-xs font-semibold">Necesito más tiempo</button>
            <button wire:click="openCommentModal({{ $task->id }})" class="py-2.5 rounded-xl bg-indigo-500/60 text-white text-xs font-semibold">Reportar problema</button>
            @elseif($task->status === 'pausada')
            <button wire:click="resumeTask({{ $task->id }})" class="col-span-2 py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">REANUDAR</button>
            @endif
        </div>
    </div>
    @elseif($fillerTasks->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 mb-4">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">No tiene tareas prioritarias pendientes ahora mismo.</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Puede aprovechar el tiempo con:</p>
        <ul class="space-y-2">
            @foreach($fillerTasks as $filler)
            <li class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2 text-sm">
                <span>{{ $filler->title }}</span>
                <button wire:click="startTask({{ $filler->id }})" class="text-xs font-bold text-indigo-600">Iniciar</button>
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 mb-4 text-center">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">No tiene tareas programadas por ahora. 🎉</p>
    </div>
    @endif

    <!-- SIGUIENTE / DESPUÉS -->
    @if($upcomingSchedules->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-4">
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Siguiente / Después</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($upcomingSchedules as $schedule)
            <div class="flex items-center justify-between px-4 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $schedule->task->title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $schedule->scheduled_start->format('H:i') }} - {{ $schedule->scheduled_end->format('H:i') }} ·
                        {{ $schedule->task->priority_label }}
                    </p>
                </div>
                @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $schedule->task])
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Modal: Pausar / Reportar problema --}}
    @if($showPauseModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Por qué pausas la tarea?</h3>
            </div>
            <div class="p-6 space-y-3">
                <select wire:model="pauseReason" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecciona un motivo...</option>
                    @foreach($pauseReasons as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('pauseReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                <textarea wire:model="pauseObservation" rows="2" placeholder="Observación (opcional)"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showPauseModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="confirmPause" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Pausar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Terminar --}}
    @if($showFinishModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Terminar tarea</h3>
            </div>
            <div class="p-6">
                <textarea wire:model="finishNote" rows="3" placeholder="Nota final (opcional)"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showFinishModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="confirmFinish" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">Confirmar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Necesito más tiempo --}}
    @if($showMoreTimeModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Necesito más tiempo</h3>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex gap-2">
                    @foreach([15, 30, 60] as $min)
                    <button type="button" wire:click="$set('moreTimeMinutes', {{ $min }})"
                        class="flex-1 py-2 rounded-lg text-sm font-semibold {{ $moreTimeMinutes == $min ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        {{ $min }} min
                    </button>
                    @endforeach
                </div>
                <input wire:model="moreTimeMinutes" type="number" min="1" placeholder="Otro (minutos)"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                <textarea wire:model="moreTimeReason" rows="2" placeholder="¿Qué pasó?"
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showMoreTimeModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="confirmMoreTime" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Enviar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Reportar problema / comentario --}}
    @if($showCommentModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reportar problema</h3>
            </div>
            <div class="p-6">
                <textarea wire:model="newComment" rows="3" placeholder="Describe el problema..."
                    class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
                @error('newComment') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showCommentModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cerrar</button>
                <button wire:click="addComment" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Enviar</button>
            </div>
        </div>
    </div>
    @endif
</div>
