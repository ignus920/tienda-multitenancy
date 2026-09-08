<div class="py-4 w-full px-4 sm:px-6 max-w-2xl mx-auto">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 mb-4">
        <h1 class="text-lg font-bold text-gray-900 dark:text-white">Mis Tareas de Hoy</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $today->translatedFormat('l j \d\e F') }}</p>
        <div class="flex flex-wrap gap-4 mt-2 text-xs">
            @if($daySchedule)
            <span class="text-gray-500 dark:text-gray-400">Horario: <strong class="text-gray-700 dark:text-gray-200">{{ $daySchedule->start_time }} - {{ $daySchedule->end_time }}</strong></span>
            @endif
            @if($availableMinutes > 0)
            <span class="text-gray-500 dark:text-gray-400">Disponible: <strong class="text-gray-700 dark:text-gray-200">{{ intdiv($availableMinutes, 60) }}h {{ $availableMinutes % 60 }}min</strong></span>
            @endif
            <span class="text-gray-500 dark:text-gray-400">Programado: <strong class="text-gray-700 dark:text-gray-200">{{ intdiv($scheduledMinutes, 60) }}h {{ $scheduledMinutes % 60 }}min</strong></span>
        </div>
    </div>

    <!-- NOVEDADES -->
    @if($notifications->isNotEmpty())
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300">🔔 Novedades ({{ $notifications->count() }})</h3>
            <button wire:click="markAllNotifsRead" class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 hover:underline">Marcar todo leído</button>
        </div>
        <ul class="space-y-1.5">
            @foreach($notifications as $n)
            <li class="flex items-start justify-between gap-2 text-xs">
                <span class="text-gray-700 dark:text-gray-200">
                    {{ $n->message }}
                    <span class="text-gray-400"> · {{ $n->created_at->diffForHumans() }}</span>
                </span>
                <button wire:click="markNotifRead({{ $n->id }})" class="text-gray-400 hover:text-gray-600 shrink-0" title="Marcar leído">✕</button>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- AHORA -->
    @if($currentSchedule)
    @php $task = $currentSchedule->task; @endphp
    <div class="bg-indigo-600 rounded-2xl p-5 text-white mb-4 shadow-lg">
        <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200 mb-1">Ahora debe realizar</p>
        <h2 class="text-xl font-bold mb-1">{{ $task->title }}</h2>
        @if($task->description)
        <p class="text-sm text-indigo-100 mb-2">{{ $task->description }}</p>
        @endif
        <div class="flex items-center gap-3 text-xs text-indigo-100 mb-3">
            <span>⏱ {{ intdiv($task->estimated_minutes, 60) }}h {{ $task->estimated_minutes % 60 }}min</span>
            <span>Hasta las {{ $currentSchedule->scheduled_end->format('H:i') }}</span>
            <span class="uppercase font-bold">{{ $task->priority_label }}</span>
        </div>

        @if($task->materials->isNotEmpty())
        <div class="bg-indigo-700/50 rounded-xl p-3 mb-3 border border-indigo-500/30">
            <h4 class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 mb-2">📍 Llevar Insumos</h4>
            <ul class="text-xs space-y-1">
                @foreach($task->materials as $mat)
                <li class="flex justify-between items-center text-indigo-50">
                    <span>{{ $mat->item_id ? ($mat->item->name ?? 'Ítem') : $mat->name }}</span>
                    <span class="font-bold text-white bg-indigo-500/50 px-2 py-0.5 rounded">{{ (float) $mat->estimated_quantity }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($task->checklists->isNotEmpty())
        <div class="bg-white/10 rounded-xl p-3 mb-4">
            <h4 class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 mb-2">📋 Checklist (Paso a paso)</h4>
            <ul class="text-sm space-y-2">
                @foreach($task->checklists as $chk)
                <li>
                    <label class="flex items-start gap-2 cursor-pointer group">
                        <input wire:click="toggleChecklistItem({{ $chk->id }})" type="checkbox" @checked($chk->is_completed) 
                            class="mt-0.5 w-4 h-4 text-green-500 bg-white/20 border-white/30 rounded focus:ring-green-500 focus:ring-2 cursor-pointer transition-colors"
                            @if(!in_array($task->status, ['en_proceso', 'pausada'])) disabled @endif>
                        <span class="text-indigo-50 leading-tight select-none {{ $chk->is_completed ? 'line-through opacity-60' : 'group-hover:text-white transition-colors' }}">
                            {{ $chk->description }}
                            @if($chk->is_required)<span class="text-amber-300 font-bold" title="Obligatorio para poder terminar">*</span>@endif
                        </span>
                    </label>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-2">
            @if(in_array($task->status, ['pendiente', 'vencida']))
            @if($task->status === 'vencida')
            <p class="col-span-2 text-[11px] font-semibold text-amber-200">⚠ Esta tarea pasó su fecha límite. Iníciala o avisa a Gerencia para reprogramarla.</p>
            @endif
            <button wire:click="startTask({{ $task->id }})" class="col-span-2 py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">INICIAR TAREA</button>
            @elseif($task->status === 'en_proceso')
            <button wire:click="openFinishModal({{ $task->id }})" class="py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">TERMINAR</button>
            <button wire:click="openPauseModal({{ $task->id }})" class="py-3 rounded-xl bg-indigo-500 text-white font-bold text-sm border border-indigo-300">PAUSAR</button>
            <button wire:click="openMoreTimeModal({{ $task->id }})" class="py-2.5 rounded-xl bg-indigo-500/60 text-white text-xs font-semibold">Necesito más tiempo</button>
            <button wire:click="openCommentModal({{ $task->id }})" class="py-2.5 rounded-xl bg-indigo-500/60 text-white text-xs font-semibold">Reportar problema</button>
            @elseif($task->status === 'pausada')
            <button wire:click="resumeTask({{ $task->id }})" class="col-span-2 py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm shadow">REANUDAR</button>
            @endif
            <button wire:click="openAttachModal({{ $task->id }})" class="col-span-2 py-2 rounded-xl bg-indigo-500/40 text-white text-xs font-semibold">📎 Adjuntar foto / archivo</button>
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

    {{-- PRÓXIMOS DÍAS --}}
    @if($upcomingDays->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-4">
        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Próximos días</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($upcomingDays as $date => $daySchedules)
            <div class="px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1.5 capitalize">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F') }}
                </p>
                <ul class="space-y-1">
                    @foreach($daySchedules as $schedule)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-200">{{ $schedule->task->title }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0 ml-2">
                            {{ $schedule->scheduled_start->format('H:i') }} - {{ $schedule->scheduled_end->format('H:i') }}
                        </span>
                    </li>
                    @endforeach
                </ul>
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

    {{-- Modal: Adjuntar foto / archivo --}}
    @if($showAttachModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Adjuntar foto / archivo</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Fotos del antes / después, evidencias, documentos.</p>
            </div>
            <div class="p-6 space-y-2">
                <input wire:model="attachFiles" type="file" multiple accept="image/*,application/pdf"
                    class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                @error('attachFiles') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                @error('attachFiles.*') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                <div wire:loading wire:target="attachFiles" class="text-xs text-gray-400">Subiendo…</div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showAttachModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="saveAttachments" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
