<div class="py-5 px-4 sm:px-6 lg:px-8 w-full mx-auto" x-data="{ lightboxImg: null }">
<style>
    /* Premium FullCalendar Styling */
    :root {
        --fc-border-color: #e5e7eb;
        --fc-daygrid-event-dot-width: 8px;
        --fc-page-bg-color: transparent;
        --fc-neutral-bg-color: #f3f4f6;
        
        /* Buttons */
        --fc-button-text-color: #fff;
        --fc-button-bg-color: #4f46e5;
        --fc-button-border-color: #4f46e5;
        --fc-button-hover-bg-color: #4338ca;
        --fc-button-hover-border-color: #4338ca;
        --fc-button-active-bg-color: #3730a3;
        --fc-button-active-border-color: #3730a3;
        
        /* Today Highlight */
        --fc-today-bg-color: #eef2ff;
    }

    .dark {
        --fc-border-color: #374151;
        --fc-neutral-bg-color: #1f2937;
        --fc-today-bg-color: #312e81;
    }

    .fc-event {
        border-radius: 6px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        border: none !important;
        padding: 2px 4px;
        font-weight: 500;
        transition: transform 0.15s ease-in-out;
        cursor: pointer !important;
    }
    
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
    }

    .fc-toolbar-title {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        color: #111827;
    }
    .dark .fc-toolbar-title {
        color: #f9fafb;
    }

    .fc-button {
        text-transform: capitalize;
        font-weight: 600 !important;
        padding: 0.4rem 1rem !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .fc-button-group .fc-button {
        border-radius: 0 !important;
        margin-left: -1px;
    }
    .fc-button-group .fc-button:first-child {
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
        margin-left: 0;
    }
    .fc-button-group .fc-button:last-child {
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
    }
    .fc-toolbar-chunk > .fc-button:not(.fc-button-group .fc-button) {
        border-radius: 6px !important;
    }
    .fc-button-primary:not(:disabled):active, .fc-button-primary:not(:disabled).fc-button-active {
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06) !important;
    }
    
    .fc-col-header-cell {
        padding: 8px 0;
        background-color: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .dark .fc-col-header-cell {
        background-color: #1f2937;
        color: #9ca3af;
    }
</style>

    {{-- ============ ENCABEZADO ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mis Tareas de Hoy</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $today->translatedFormat('l j \d\e F') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($activeView === 'today')
                @if($daySchedule)
                <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5 hidden sm:block">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400">Horario</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ substr($daySchedule->start_time, 0, 5) }}–{{ substr($daySchedule->end_time, 0, 5) }}</p>
                </div>
                @endif
                @if($availableMinutes > 0)
                <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5 hidden sm:block">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400">Disponible</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ intdiv($availableMinutes, 60) }}h {{ $availableMinutes % 60 }}m</p>
                </div>
                @endif
                <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3 py-1.5 hidden sm:block">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400">Programado</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ intdiv($scheduledMinutes, 60) }}h {{ $scheduledMinutes % 60 }}m</p>
                </div>
            @endif

            <button wire:click="openCreateTaskModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Tarea
            </button>

            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden ml-1">
                <button wire:click="$set('activeView', 'today')"
                    class="px-3 py-1.5 text-xs font-semibold {{ $activeView === 'today' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300' }}">Hoy</button>
                <button wire:click="$set('activeView', 'calendar')"
                    class="px-3 py-1.5 text-xs font-semibold {{ $activeView === 'calendar' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300' }}">Mi Calendario</button>
            </div>
            @if($enableAutoAssign)
            <button wire:click="openAutoAssignModal" class="rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 px-3 py-1.5 flex items-center justify-center gap-1.5 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors group">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <div class="text-left">
                    <p class="text-[10px] uppercase tracking-wide text-indigo-600/70 dark:text-indigo-400/70">Tareas</p>
                    <p class="text-sm font-bold text-indigo-700 dark:text-indigo-300">Autoasignar</p>
                </div>
            </button>
            @endif
        </div>
    </div>

    @if($activeView === 'calendar')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-3"
         x-data="myTaskCalendar($wire)" x-init="init($el)" wire:ignore>
        <div id="my-task-calendar-el"></div>
    </div>
    @endif

    @if($activeView === 'today' && $currentSchedule)
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
                    <button type="button" wire:click="openDetailModal({{ $task->id }})" class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Ver detalle completo →</button>

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

    @elseif($activeView === 'today' && $fillerTasks->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 w-full shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 mb-2">
                    <span class="text-2xl">✨</span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Nada prioritario ahora mismo</h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Tienes tu agenda libre, pero puedes aprovechar el tiempo adelantando alguna de estas tareas de tu área:</p>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($fillerTasks as $filler)
                    <li class="flex items-center justify-between bg-gray-50 hover:bg-white dark:bg-gray-700/30 dark:hover:bg-gray-700/60 border border-transparent hover:border-indigo-100 dark:hover:border-indigo-800 transition-all rounded-xl p-3 shadow-sm group">
                        <div class="min-w-0 pr-3">
                            <span class="block text-sm font-semibold text-gray-700 dark:text-gray-200 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $filler->title }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button wire:click="openSelfScheduleModal({{ $filler->id }})" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline px-1 py-1">Agendar</button>
                            <button wire:click="startTask({{ $filler->id }})" class="shrink-0 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-900/30 dark:hover:bg-indigo-600 px-3.5 py-2 rounded-lg transition-colors">
                                Iniciar
                            </button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @elseif($activeView === 'today')
    <div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-3xl p-12 border border-gray-100 dark:border-gray-700 w-full text-center shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center shadow-inner">
                <span class="text-4xl">🎉</span>
            </div>
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">¡Todo al día!</h2>
        <p class="text-base text-gray-500 dark:text-gray-400 max-w-lg mx-auto">No tienes tareas programadas por ahora ni pendientes prioritarios.</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Cuando Gerencia te asigne algo o te autoasignes una tarea, aparecerá aquí al instante.</p>
    </div>
    @endif

    {{-- ============ AGENDA ============ --}}
    @if($activeView === 'today' && ($upcomingSchedules->isNotEmpty() || $upcomingDays->isNotEmpty()))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

        @if($upcomingSchedules->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Siguiente / Después (hoy)</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($upcomingSchedules as $schedule)
                <button type="button" wire:click="openDetailModal({{ $schedule->task_id }})" class="w-full flex items-center justify-between px-4 py-3 gap-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $schedule->task->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $schedule->scheduled_start->format('H:i') }} - {{ $schedule->scheduled_end->format('H:i') }} · {{ $schedule->task->priority_label }}</p>
                    </div>
                    @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $schedule->task])
                </button>
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
                        <li>
                            <button type="button" wire:click="openDetailModal({{ $schedule->task_id }})" class="w-full flex items-center justify-between gap-2 text-sm text-left hover:text-indigo-600 dark:hover:text-indigo-400">
                                <span class="text-gray-700 dark:text-gray-200 truncate">{{ $schedule->task->title }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">{{ $schedule->scheduled_start->format('H:i') }}</span>
                            </button>
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

    {{-- Modal: Crear Tarea --}}
    @if($showCreateTaskModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nueva Tarea (Autoasignada)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Agrega una tarea a tu bandeja para poder agendarla o iniciarla hoy.</p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Título</label>
                    <input wire:model="createTitle" type="text" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('createTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Descripción (Opcional)</label>
                    <textarea wire:model="createDescription" rows="2" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Departamento</label>
                        <select wire:model="createDepartmentId" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                            <option value="">Selecciona...</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                            @endforeach
                        </select>
                        @error('createDepartmentId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Prioridad</label>
                        <select wire:model="createPriority" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                            <option value="p1_urgente">Urgente</option>
                            <option value="p2_alta">Alta</option>
                            <option value="p3_normal">Normal</option>
                            <option value="p4_baja">Baja</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tiempo Estimado</label>
                    <input wire:model="createEstimatedTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('createEstimatedTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showCreateTaskModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="saveNewTask" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm">Crear Tarea</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Auto-agendar Tarea --}}
    @if($showSelfScheduleModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Agendar Tarea</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                    <input wire:model="selfScheduleDate" type="date" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                    @error('selfScheduleDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Hora Inicio</label>
                        <input wire:model.live="selfScheduleStartTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        @error('selfScheduleStartTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Hora Fin</label>
                        <input wire:model="selfScheduleEndTime" type="time" class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
                        @error('selfScheduleEndTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showSelfScheduleModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="confirmSelfSchedule" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm">Guardar</button>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Detalle completo de la tarea (solo lectura de datos de creación: no hay
         edición de título/descripción/fechas ni reprogramación, solo lo operativo
         que el trabajador ya puede hacer: comentar y bloquear/desbloquear). --}}
    @include('livewire.tenant.task-planner.partials.detail-modal')
    @include('livewire.tenant.task-planner.partials.block-modal')


    {{-- Modal: Autoasignar --}}
    @if($showAutoAssignModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[85vh]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Autoasignarme una tarea</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Elige una tarea y el sistema buscará el primer espacio libre en tu horario para agendarla.</p>
            </div>
            <div class="p-6 overflow-y-auto flex-1 space-y-3">
                @forelse($availableTasksToAssign as $t)
                <div class="flex items-center justify-between gap-4 p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 bg-gray-50 dark:bg-gray-700/30 transition-colors">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $t->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $t->description ?: 'Sin descripción' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $t->priority === 'p1_urgente' ? 'bg-red-100 text-red-700' : ($t->priority === 'p2_alta' ? 'bg-orange-100 text-orange-700' : 'bg-gray-200 text-gray-700') }}">{{ $t->priority_label }}</span>
                            <span class="text-xs text-gray-500 font-semibold">⏱ {{ $t->estimated_minutes ?: 60 }} min</span>
                        </div>
                    </div>
                    <button wire:click="confirmAutoAssign({{ $t->id }})" class="shrink-0 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition-colors border border-indigo-100">
                        Tomar tarea
                    </button>
                </div>
                @empty
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500">No hay tareas pendientes para autoasignarte en tu área en este momento.</p>
                </div>
                @endforelse
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button wire:click="$set('showAutoAssignModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cerrar</button>
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

@once
<script>
    function tpLoadScript(src) {
        window.__tpScriptPromises = window.__tpScriptPromises || {};
        if (window.__tpScriptPromises[src]) return window.__tpScriptPromises[src];

        window.__tpScriptPromises[src] = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });

        return window.__tpScriptPromises[src];
    }

    function myTaskCalendar($wire) {
        return {
            calendar: null,
            init(el) {
                this.loadAssets().then(() => this.renderCalendar(el));

                $wire.on('calendar-refresh', () => {
                    if (this.calendar) this.calendar.refetchEvents();
                });
            },
            loadAssets() {
                if (window.FullCalendar) return Promise.resolve();

                return tpLoadScript('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
                    .then(() => tpLoadScript('https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js'));
            },
            renderCalendar(el) {
                const calendarEl = el.querySelector('#my-task-calendar-el');

                this.calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'es',
                    height: 'auto',
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
                    initialView: 'timeGridWeek',
                    eventDisplay: 'block',
                    slotMinTime: '06:00:00',
                    slotMaxTime: '20:00:00',
                    nowIndicator: true,
                    editable: true,
                    droppable: true,
                    eventDrop: function(info) {
                        $wire.updateScheduleFromCalendar(info.event.id, info.event.startStr, info.event.endStr || null);
                    },
                    eventResize: function(info) {
                        $wire.updateScheduleFromCalendar(info.event.id, info.event.startStr, info.event.endStr || null);
                    },
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    events: (fetchInfo, successCallback, failureCallback) => {
                        $wire.getMyCalendarEvents(fetchInfo.startStr, fetchInfo.endStr)
                            .then(successCallback)
                            .catch(failureCallback);
                    },
                    eventClick: (info) => {
                        if (info.event.extendedProps.taskId) {
                            $wire.openDetailModal(info.event.extendedProps.taskId);
                        }
                    },
                });

                this.calendar.render();
            },
        };
    }
</script>
@endonce
