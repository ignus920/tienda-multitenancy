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

    <!-- =================== TAB: CALENDARIO =================== -->
    @if($activeTab === 'calendario')
    <div class="mt-4 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button wire:click="goToPreviousWeek" class="px-2 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200">←</button>
                <button wire:click="goToCurrentWeek" class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-200">Hoy</button>
                <button wire:click="goToNextWeek" class="px-2 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200">→</button>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 ml-2">
                    {{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}
                </span>
            </div>
            <select wire:model.live="calendarDepartmentId" class="block border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs">
                <option value="">Todos los departamentos</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        @forelse($calendarSchedules as $userKey => $schedules)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ explode('|', $userKey)[0] }}</h4>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($schedules as $schedule)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 px-4 py-2.5">
                    <div class="flex items-center gap-3">
                        <div class="text-xs font-mono text-gray-500 dark:text-gray-400 w-36 shrink-0">
                            {{ $schedule->scheduled_start->format('D d/m H:i') }} - {{ $schedule->scheduled_end->format('H:i') }}
                        </div>
                        @include('livewire.tenant.task-planner.partials.priority-badge', ['task' => $schedule->task])
                        <button wire:click="openDetailModal({{ $schedule->task_id }})" class="text-sm font-medium text-gray-800 dark:text-gray-100 hover:text-indigo-600 text-left">
                            {{ $schedule->task->title }}
                        </button>
                        @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $schedule->task])
                    </div>
                    <div>
                        <button wire:click="openScheduleModal({{ $schedule->task_id }})" class="text-xs font-semibold text-indigo-600 hover:underline">Reprogramar</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-400 text-sm">
            No hay tareas programadas esta semana.
        </div>
        @endforelse
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
</div>
