<div class="py-4 w-full px-4 sm:px-6">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Planeación de Tareas Operativas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Laboratorio, Instalaciones y Adecuaciones: quién hace qué, cuándo y con qué prioridad.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openUnavailabilityModal"
                class="inline-flex items-center px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                Registrar indisponibilidad
            </button>
            <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg shadow transition-colors shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva tarea
            </button>
        </div>
    </div>

    <!-- Dashboard rápido -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $dashboard['programadas'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Programadas</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $dashboard['en_proceso'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">En proceso</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $dashboard['pausadas'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Pausadas</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $dashboard['bloqueadas'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Bloqueadas</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $dashboard['terminadas_hoy'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Terminadas hoy</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $dashboard['atrasadas'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Atrasadas</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 text-center">
            <p class="text-2xl font-bold text-gray-500">{{ $dashboard['sin_programar'] }}</p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Sin programar</p>
        </div>
    </div>

    <!-- Pestañas -->
    <div class="flex flex-wrap gap-1 mt-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg text-xs font-semibold w-fit border border-gray-200 dark:border-gray-700">
        @foreach(['bandeja' => 'Bandeja', 'calendario' => 'Calendario', 'atrasadas' => 'Atrasadas', 'horarios' => 'Horarios laborales'] as $tab => $label)
        <button wire:click="$set('activeTab', '{{ $tab }}')"
            class="px-3 py-1.5 rounded-md transition-colors {{ $activeTab === $tab ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
            {{ $label }}
            @if($tab === 'atrasadas' && $overdueTasks->count() > 0)
                <span class="ml-1 inline-flex items-center justify-center bg-red-500 text-white text-[10px] rounded-full h-4 w-4">{{ $overdueTasks->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    <!-- =================== TAB: BANDEJA =================== -->
    @if($activeTab === 'bandeja')
    <div class="mt-4 space-y-6">

        @if($unscheduledTasks->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20">
                <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">Tareas pendientes de programar ({{ $unscheduledTasks->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($unscheduledTasks as $task)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 px-4 py-3">
                    <div class="flex items-center gap-3">
                        @include('livewire.tenant.task-planner.partials.priority-badge', ['task' => $task])
                        <div>
                            <button wire:click="openDetailModal({{ $task->id }})" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-indigo-600">{{ $task->title }}</button>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $task->department->name ?? '—' }} ·
                                {{ intdiv($task->estimated_minutes, 60) }}h {{ $task->estimated_minutes % 60 }}min ·
                                Límite: {{ $task->deadline_at?->format('d/m/Y H:i') ?? 'Sin definir' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="openScheduleModal({{ $task->id }})" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Programar</button>
                        <button wire:click="editTask({{ $task->id }})" class="px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg">Editar</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-3 items-center">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar tarea..."
                class="block w-full md:max-w-xs border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <select wire:model.live="filterDepartment" class="block w-full md:w-auto border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs">
                <option value="">Todos los departamentos</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterPriority" class="block w-full md:w-auto border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs">
                <option value="">Toda prioridad</option>
                @foreach(\App\Models\Tenant\TaskPlanner\Task::PRIORITIES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="block w-full md:w-auto border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs">
                <option value="">Todo estado (activas)</option>
                @foreach(\App\Models\Tenant\TaskPlanner\Task::STATUSES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Listado -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Tarea</th>
                        <th class="px-4 py-2 text-left">Departamento</th>
                        <th class="px-4 py-2 text-left">Responsables</th>
                        <th class="px-4 py-2 text-left">Programación</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                        <th class="px-4 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-2.5">
                            <button wire:click="openDetailModal({{ $task->id }})" class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 text-left">{{ $task->title }}</button>
                            @include('livewire.tenant.task-planner.partials.priority-badge', ['task' => $task])
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300">{{ $task->department->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300 text-xs">
                            {{ $task->assignments->map(fn($a) => optional(\App\Models\Auth\User::find($a->user_id))->name)->filter()->join(', ') ?: '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300 text-xs">
                            @if($task->currentSchedule)
                                {{ $task->currentSchedule->scheduled_start->format('d/m H:i') }} - {{ $task->currentSchedule->scheduled_end->format('H:i') }}
                            @else
                                <span class="text-gray-400">Sin programar</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $task])
                        </td>
                        <td class="px-4 py-2.5 text-right space-x-1 whitespace-nowrap">
                            <button wire:click="openScheduleModal({{ $task->id }})" class="text-xs font-semibold text-indigo-600 hover:underline">Programar</button>
                            <button wire:click="editTask({{ $task->id }})" class="text-xs font-semibold text-gray-500 hover:underline">Editar</button>
                            @if(!in_array($task->status, ['terminada', 'cancelada']))
                            <button wire:click="openCancelModal({{ $task->id }})" wire:confirm="¿Cancelar esta tarea?" class="text-xs font-semibold text-red-500 hover:underline">Cancelar</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No hay tareas que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $tasks->links() }}</div>
        </div>
    </div>
    @endif

    <!-- =================== TAB: CALENDARIO (FullCalendar + drag & drop) =================== -->
    @if($activeTab === 'calendario')
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">

        <!-- Bandeja de tareas arrastrables -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
            <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Arrastra al calendario</h4>
            <p class="text-[11px] text-gray-400 mb-3">Tareas sin programar. Suéltalas en el día/hora deseada.</p>

            <select wire:model.live="calendarDepartmentId" class="block w-full mb-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-2 py-1.5 text-xs">
                <option value="">Todos los departamentos</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <div id="unscheduled-tray" class="space-y-2 max-h-[600px] overflow-y-auto">
                @forelse($unscheduledTasks as $task)
                @php
                    $priorityColors = ['p1_urgente' => '#ef4444', 'p2_alta' => '#f97316', 'p3_normal' => '#3b82f6', 'p4_baja' => '#9ca3af'];
                    $hours = intdiv($task->total_occupied_minutes, 60);
                    $mins = $task->total_occupied_minutes % 60;
                @endphp
                <div class="tray-task cursor-move rounded-lg px-3 py-2 text-white text-xs font-semibold shadow-sm"
                     style="background-color: {{ $priorityColors[$task->priority] ?? '#6366f1' }}"
                     data-task-id="{{ $task->id }}"
                     data-title="{{ $task->title }}"
                     data-duration="{{ sprintf('%02d:%02d', $hours, $mins) }}"
                     data-color="{{ $priorityColors[$task->priority] ?? '#6366f1' }}">
                    {{ $task->title }}
                    <div class="text-[10px] font-normal opacity-90">{{ $task->department->name ?? '—' }} · {{ $hours }}h {{ $mins }}min</div>
                </div>
                @empty
                <p class="text-xs text-gray-400">No hay tareas sin programar.</p>
                @endforelse
            </div>
        </div>

        <!-- Calendario -->
        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3"
             x-data="taskPlannerCalendar($wire)" x-init="init($el)" wire:ignore>
            <div id="task-planner-calendar-el"></div>
        </div>
    </div>
    @endif

    <!-- =================== TAB: ATRASADAS =================== -->
    @if($activeTab === 'atrasadas')
    <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/40 text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2 text-left">Tarea</th>
                    <th class="px-4 py-2 text-left">Responsables</th>
                    <th class="px-4 py-2 text-left">Fecha límite</th>
                    <th class="px-4 py-2 text-left">Atraso</th>
                    <th class="px-4 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($overdueTasks as $task)
                <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10">
                    <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                        <button wire:click="openDetailModal({{ $task->id }})" class="hover:text-indigo-600">{{ $task->title }}</button>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">
                        {{ $task->assignments->map(fn($a) => optional(\App\Models\Auth\User::find($a->user_id))->name)->filter()->join(', ') ?: '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $task->deadline_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2.5 text-xs font-semibold text-red-600">{{ $task->deadline_at?->diffForHumans(null, true) }}</td>
                    <td class="px-4 py-2.5 text-right space-x-1">
                        <button wire:click="openScheduleModal({{ $task->id }})" class="text-xs font-semibold text-indigo-600 hover:underline">Reprogramar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No hay tareas atrasadas. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    <!-- =================== TAB: HORARIOS LABORALES =================== -->
    @if($activeTab === 'horarios')
    <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 space-y-4">
        <select wire:model.live="scheduleFormUserId" class="block w-full md:w-72 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
            <option value="">Selecciona un trabajador...</option>
            @foreach($assignableUsers as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        @if($scheduleFormUserId)
        <div class="space-y-2">
            @foreach(['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $dayIndex => $dayName)
            <div class="flex flex-wrap items-center gap-3 border border-gray-100 dark:border-gray-700 rounded-lg p-2.5">
                <label class="flex items-center gap-2 w-28 shrink-0 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model="employeeScheduleForm.{{ $dayIndex }}.active" class="rounded border-gray-300 text-indigo-600">
                    {{ $dayName }}
                </label>
                @if(!empty($employeeScheduleForm[$dayIndex]['active']))
                <div class="flex items-center gap-1 text-xs">
                    <span class="text-gray-400">Entrada</span>
                    <input type="time" wire:model="employeeScheduleForm.{{ $dayIndex }}.start_time" class="border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                    <span class="text-gray-400">Salida</span>
                    <input type="time" wire:model="employeeScheduleForm.{{ $dayIndex }}.end_time" class="border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                    <span class="text-gray-400 ml-2">Almuerzo</span>
                    <input type="time" wire:model="employeeScheduleForm.{{ $dayIndex }}.break_start" class="border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                    <span class="text-gray-400">a</span>
                    <input type="time" wire:model="employeeScheduleForm.{{ $dayIndex }}.break_end" class="border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                </div>
                @endif
            </div>
            @endforeach
            <button wire:click="saveEmployeeSchedule" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar horario</button>
        </div>
        @endif

        <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-2">Indisponibilidades vigentes</h4>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($unavailabilities as $u)
                <div class="flex items-center justify-between py-2 text-sm">
                    <span>
                        <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $u->user->name ?? '—' }}</span>
                        <span class="text-gray-500 dark:text-gray-400"> · {{ \App\Models\Tenant\TaskPlanner\EmployeeUnavailability::TYPES[$u->type] ?? $u->type }} ·
                        {{ $u->start_datetime->format('d/m H:i') }} - {{ $u->end_datetime->format('d/m H:i') }}</span>
                    </span>
                    <button wire:click="deleteUnavailability({{ $u->id }})" wire:confirm="¿Eliminar esta indisponibilidad?" class="text-xs text-red-500 hover:underline">Eliminar</button>
                </div>
                @empty
                <p class="text-sm text-gray-400 py-2">No hay indisponibilidades registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== MODALES ==================== --}}
    @include('livewire.tenant.task-planner.partials.task-modal')
    @include('livewire.tenant.task-planner.partials.schedule-modal')
    @include('livewire.tenant.task-planner.partials.detail-modal')
    @include('livewire.tenant.task-planner.partials.cancel-modal')
    @include('livewire.tenant.task-planner.partials.block-modal')
    @include('livewire.tenant.task-planner.partials.unavailability-modal')

    <script>
        function taskPlannerCalendar($wire) {
            return {
                calendar: null,
                init(el) {
                    this.loadAssets().then(() => {
                        this.renderCalendar(el);
                        this.initDraggableTray();
                    });

                    $wire.on('calendar-refresh', () => {
                        if (this.calendar) this.calendar.refetchEvents();
                    });
                },
                loadAssets() {
                    if (window.FullCalendar) return Promise.resolve();

                    return this.loadScript('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
                        .then(() => this.loadScript('https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js'));
                },
                loadScript(src) {
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
                },
                renderCalendar(el) {
                    const calendarEl = el.querySelector('#task-planner-calendar-el');

                    this.calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'es',
                        height: 'auto',
                        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
                        initialView: 'timeGridWeek',
                        slotMinTime: '06:00:00',
                        slotMaxTime: '20:00:00',
                        nowIndicator: true,
                        editable: true,
                        droppable: true,
                        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                        events: (fetchInfo, successCallback, failureCallback) => {
                            $wire.getCalendarEvents(fetchInfo.startStr, fetchInfo.endStr)
                                .then(successCallback)
                                .catch(failureCallback);
                        },
                        eventDrop: (info) => {
                            $wire.prefillScheduleFromDrop(info.event.extendedProps.taskId, info.event.startStr, info.event.endStr);
                            info.revert();
                        },
                        eventResize: (info) => {
                            $wire.prefillScheduleFromDrop(info.event.extendedProps.taskId, info.event.startStr, info.event.endStr);
                            info.revert();
                        },
                        eventReceive: (info) => {
                            const taskId = info.event.extendedProps.taskId;
                            const startStr = info.event.startStr;
                            info.event.remove();
                            $wire.prefillScheduleFromDrop(taskId, startStr, null);
                        },
                        eventClick: (info) => {
                            $wire.openDetailModal(info.event.extendedProps.taskId);
                        },
                    });

                    this.calendar.render();
                },
                initDraggableTray() {
                    const trayEl = document.getElementById('unscheduled-tray');
                    if (!trayEl || trayEl.__tpDraggableInit) return;
                    trayEl.__tpDraggableInit = true;

                    new FullCalendar.Draggable(trayEl, {
                        itemSelector: '.tray-task',
                        eventData: (eventEl) => ({
                            title: eventEl.dataset.title,
                            duration: eventEl.dataset.duration,
                            backgroundColor: eventEl.dataset.color,
                            borderColor: eventEl.dataset.color,
                            extendedProps: { taskId: eventEl.dataset.taskId },
                        }),
                    });
                },
            };
        }
    </script>
</div>
