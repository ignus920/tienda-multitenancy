<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto" x-data="{ lightboxImg: null }">

    {{-- ============ ENCABEZADO ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mis Tareas de Hoy</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $today->translatedFormat('l j \d\e F') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($daySchedule)
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Horario</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ substr($daySchedule->start_time, 0, 5) }}–{{ substr($daySchedule->end_time, 0, 5) }}</p>
            </div>
            @endif
            @if($availableMinutes > 0)
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Disponible</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ intdiv($availableMinutes, 60) }}h {{ $availableMinutes % 60 }}m</p>
            </div>
            @endif
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5">
                <p class="text-[10px] uppercase tracking-wide text-gray-400">Programado</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ intdiv($scheduledMinutes, 60) }}h {{ $scheduledMinutes % 60 }}m</p>
            </div>
        </div>
    </div>

    @if($currentSchedule)
    @php
        $task = $currentSchedule->task;
        $accentBg = match ($task->priority) {
            'p1_urgente' => 'bg-red-500',
            'p2_alta'    => 'bg-orange-500',
            'p4_baja'    => 'bg-gray-400',
            default      => 'bg-indigo-500',
        };
        $accentText = match ($task->priority) {
            'p1_urgente' => 'text-red-600 dark:text-red-400',
            'p2_alta'    => 'text-orange-600 dark:text-orange-400',
            'p4_baja'    => 'text-gray-500 dark:text-gray-400',
            default      => 'text-indigo-600 dark:text-indigo-400',
        };
        $chkDone  = $task->checklists->where('is_completed', true)->count();
        $chkTotal = $task->checklists->count();
    @endphp

    {{-- ============ CUADRÍCULA PRINCIPAL ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- ---- COLUMNA PRINCIPAL: AHORA ---- --}}
        <div class="lg:col-span-7 space-y-5">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="h-1.5 {{ $accentBg }}"></div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-widest {{ $accentText }}">Ahora debe realizar</span>
                        <span class="text-[11px] font-bold {{ $accentText }}">· {{ $task->priority_label }}</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ $task->title }}</h2>
                    @if($task->description)
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1.5">{{ $task->description }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2.5 py-1 font-semibold">⏱ {{ intdiv($task->estimated_minutes, 60) }}h {{ $task->estimated_minutes % 60 }}min</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2.5 py-1 font-semibold">🕒 Hasta las {{ $currentSchedule->scheduled_end->format('H:i') }}</span>
                        @if($task->location && $task->location_type !== 'empresa')
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2.5 py-1 font-semibold">📍 {{ $task->location }}</span>
                        @endif
                    </div>

                    @if($task->status === 'vencida')
                    <div class="mt-4 flex items-start gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
                        <span class="shrink-0">⚠</span>
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Esta tarea pasó su fecha límite. Iníciala o avisa a Gerencia para reprogramarla.</p>
                    </div>
                    @endif

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="mt-5 grid grid-cols-2 gap-2.5">
                        @if(in_array($task->status, ['pendiente', 'vencida']))
                            <button wire:click="startTask({{ $task->id }})" class="col-span-2 inline-flex items-center justify-center gap-2 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition-colors">
                                ▶ INICIAR TAREA
                            </button>
                        @elseif($task->status === 'en_proceso')
                            <button wire:click="openFinishModal({{ $task->id }})" class="inline-flex items-center justify-center gap-2 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition-colors">✓ TERMINAR</button>
                            <button wire:click="openPauseModal({{ $task->id }})" class="inline-flex items-center justify-center gap-2 py-3.5 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-100 font-bold text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">⏸ PAUSAR</button>
                            <button wire:click="openMoreTimeModal({{ $task->id }})" class="py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Necesito más tiempo</button>
                            <button wire:click="openCommentModal({{ $task->id }})" class="py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Reportar problema</button>
                        @elseif($task->status === 'pausada')
                            <button wire:click="resumeTask({{ $task->id }})" class="col-span-2 inline-flex items-center justify-center gap-2 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition-colors">▶ REANUDAR</button>
                        @endif
                        <button wire:click="openAttachModal({{ $task->id }})" class="col-span-2 py-2.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-xs font-semibold hover:border-indigo-400 hover:text-indigo-500 transition-colors">📎 Adjuntar foto / archivo</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ---- COLUMNA LATERAL ---- --}}
        <div class="lg:col-span-5 space-y-5">

            {{-- CHECKLIST --}}
            @if($chkTotal > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Checklist</h3>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $chkDone }}/{{ $chkTotal }}</span>
                </div>
                <div class="h-1 bg-gray-100 dark:bg-gray-700">
                    <div class="h-full bg-emerald-500 transition-all duration-300" style="width: {{ $chkTotal ? round($chkDone / $chkTotal * 100) : 0 }}%"></div>
                </div>
                <ul class="p-2.5 space-y-0.5">
                    @foreach($task->checklists as $chk)
                    <li>
                        <label class="flex items-start gap-2.5 rounded-lg px-2 py-1.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <input wire:click="toggleChecklistItem({{ $chk->id }})" type="checkbox" @checked($chk->is_completed)
                                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                @if(!in_array($task->status, ['en_proceso', 'pausada'])) disabled @endif>
                            <span class="text-sm leading-snug select-none {{ $chk->is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-200' }}">
                                {{ $chk->description }}
                                @if($chk->is_required)<span class="text-amber-500 font-bold" title="Obligatorio para terminar">*</span>@endif
                            </span>
                        </label>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- INSUMOS --}}
            @if($task->materials->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Llevar insumos</h3>
                </div>
                <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($task->materials as $mat)
                    <li class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                        <span class="text-gray-700 dark:text-gray-200">{{ $mat->item_id ? ($mat->item->name ?? 'Ítem') : $mat->name }}</span>
                        <span class="shrink-0 font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 rounded-md px-2 py-0.5 text-xs">{{ (float) $mat->estimated_quantity }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ARCHIVOS (de Gerencia + los que suba el trabajador) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Archivos</h3>
                    <button wire:click="openAttachModal({{ $task->id }})" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">+ Subir</button>
                </div>
                @if($task->attachments->isNotEmpty())
                <div class="p-3 grid grid-cols-3 gap-2">
                    @foreach($task->attachments as $att)
                        @php
                            $attUrl = \Illuminate\Support\Str::startsWith($att->file_path, ['http://', 'https://'])
                                ? $att->file_path
                                : route('tenant.task-planner.attachment', $att->id);
                            $attExt = strtolower($att->file_type ?: pathinfo($att->file_name, PATHINFO_EXTENSION));
                            $attIsImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif']);
                        @endphp
                        @if($attIsImage)
                        <button type="button" @click="lightboxImg = @js($attUrl)" title="{{ $att->file_name }}"
                            class="group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                            <img src="{{ $attUrl }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                        </button>
                        @else
                        <a href="{{ $attUrl }}" target="_blank" rel="noopener" title="{{ $att->file_name }}"
                            class="flex flex-col items-center justify-center gap-1 aspect-square rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-1 text-center hover:border-indigo-400 transition-colors">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400">{{ $attExt ? strtoupper($attExt) : 'ARCHIVO' }}</span>
                        </a>
                        @endif
                    @endforeach
                </div>
                @else
                <div class="px-4 py-6 text-center text-xs text-gray-400">Sin archivos todavía.</div>
                @endif
            </div>
        </div>
    </div>

    @elseif($fillerTasks->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 max-w-xl">
        <p class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-1">Nada prioritario ahora mismo ✨</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Puedes aprovechar el tiempo con:</p>
        <ul class="space-y-2">
            @foreach($fillerTasks as $filler)
            <li class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3.5 py-2.5 text-sm">
                <span class="text-gray-700 dark:text-gray-200">{{ $filler->title }}</span>
                <button wire:click="startTask({{ $filler->id }})" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg transition-colors">Iniciar</button>
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-10 border border-gray-100 dark:border-gray-700 text-center max-w-xl">
        <p class="text-4xl mb-2">🎉</p>
        <p class="text-base font-semibold text-gray-800 dark:text-gray-100">No tienes tareas programadas por ahora.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cuando Gerencia te asigne algo, aparecerá aquí al instante.</p>
    </div>
    @endif

    {{-- ============ AGENDA (siempre visible) ============ --}}
    @if($upcomingSchedules->isNotEmpty() || $upcomingDays->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

        @if($upcomingSchedules->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Siguiente / Después (hoy)</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($upcomingSchedules as $schedule)
                <div class="flex items-center justify-between px-4 py-3 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $schedule->task->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $schedule->scheduled_start->format('H:i') }} - {{ $schedule->scheduled_end->format('H:i') }} · {{ $schedule->task->priority_label }}</p>
                    </div>
                    @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $schedule->task])
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($upcomingDays->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Próximos días</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($upcomingDays as $date => $daySchedules)
                <div class="px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1.5 capitalize">{{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F') }}</p>
                    <ul class="space-y-1">
                        @foreach($daySchedules as $schedule)
                        <li class="flex items-center justify-between gap-2 text-sm">
                            <span class="text-gray-700 dark:text-gray-200 truncate">{{ $schedule->task->title }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">{{ $schedule->scheduled_start->format('H:i') }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif
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

    {{-- Visor de imagen (lightbox) — se abre aquí mismo, sin ventana nueva --}}
    <div x-show="lightboxImg" x-cloak style="display: none;"
         x-transition.opacity
         class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4"
         @click="lightboxImg = null"
         @keydown.escape.window="lightboxImg = null">
        <button type="button" @click="lightboxImg = null"
            class="absolute top-4 right-4 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2 z-[61]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <img :src="lightboxImg" @click.stop alt="" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
    </div>
</div>
