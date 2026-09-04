<?php

namespace App\Livewire\Tenant\TaskPlanner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskDepartment;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskComment;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeUnavailability;
use App\Models\Tenant\Projects\Project;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\TaskPlanner\TaskService;
use App\Services\TaskPlanner\SchedulingService;
use App\Services\TaskPlanner\TimeTrackingService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManageTasks extends Component
{
    use WithPagination;

    public $activeTab = 'bandeja';

    // Filtros del listado
    public $search = '';
    public $filterDepartment = '';
    public $filterPriority = '';
    public $filterStatus = '';

    // Modal Crear/Editar Tarea
    public $showTaskModal = false;
    public $editingTaskId = null;
    public $title = '';
    public $description = '';
    public $departmentId = '';
    public $priority = 'p3_normal';
    public $estimatedHours = 0;
    public $estimatedMinutes = 30;
    public $deadlineDate = '';
    public $deadlineTime = '17:00';
    public $suggestedDate = '';
    public $locationType = 'empresa';
    public $location = '';
    public $travelBefore = 0;
    public $travelAfter = 0;
    public $originType = '';
    public $originProjectId = '';
    public $assignedUserIds = [];
    public $dependsOnTaskIds = [];

    // Modal Programar / Reprogramar
    public $showScheduleModal = false;
    public $schedulingTaskId = null;
    public $scheduleDate = '';
    public $scheduleStartTime = '';
    public $scheduleEndTime = '';
    public $rescheduleReason = '';
    public $scheduleConflicts = [];

    // Modal Detalle de tarea
    public $showDetailModal = false;
    public $detailTaskId = null;
    public $newComment = '';

    // Modal Cancelar
    public $showCancelModal = false;
    public $cancelingTaskId = null;
    public $cancelReason = '';

    // Modal Bloquear
    public $showBlockModal = false;
    public $blockingTaskId = null;
    public $blockReason = '';

    // Tab Calendario
    public $calendarDepartmentId = '';

    // Tab Horarios laborales
    public $scheduleFormUserId = '';
    public $employeeScheduleForm = [];

    // Modal Indisponibilidad
    public $showUnavailabilityModal = false;
    public $unavailUserId = '';
    public $unavailStart = '';
    public $unavailEnd = '';
    public $unavailType = 'permiso_personal';
    public $unavailReason = '';

    protected $queryString = [
        'activeTab' => ['except' => 'bandeja'],
    ];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->deadlineDate = now()->addDay()->format('Y-m-d');
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        app(TenantManager::class)->setConnection($tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    private function assignableUsersQuery()
    {
        return User::whereHas('tenants', function ($q) {
                $q->where('tenants.id', session('tenant_id'));
            })
            ->whereNotIn('profile_id', [17, 18])
            ->orderBy('name');
    }

    // ---------------------------------------------------------------
    // Crear / Editar tarea
    // ---------------------------------------------------------------

    public function openCreateModal()
    {
        $this->reset([
            'editingTaskId', 'title', 'description', 'departmentId', 'priority', 'estimatedHours',
            'estimatedMinutes', 'suggestedDate', 'locationType', 'location', 'travelBefore', 'travelAfter',
            'originType', 'originProjectId', 'assignedUserIds', 'dependsOnTaskIds'
        ]);
        $this->priority = 'p3_normal';
        $this->estimatedMinutes = 30;
        $this->locationType = 'empresa';
        $this->deadlineDate = now()->addDay()->format('Y-m-d');
        $this->deadlineTime = '17:00';
        $this->showTaskModal = true;
    }

    public function saveTask(TaskService $taskService)
    {
        $this->ensureTenantConnection();

        $this->validate([
            'title' => 'required|string|max:255',
            'departmentId' => 'required|exists:tenant.tsk_departments,id',
            'priority' => 'required|in:p1_urgente,p2_alta,p3_normal,p4_baja',
            'estimatedHours' => 'nullable|integer|min:0',
            'estimatedMinutes' => 'nullable|integer|min:0|max:59',
            'deadlineDate' => 'required|date',
            'assignedUserIds' => 'required|array|min:1',
        ], [
            'title.required' => 'El título de la tarea es obligatorio.',
            'departmentId.required' => 'Selecciona el departamento.',
            'deadlineDate.required' => 'La fecha límite es obligatoria.',
            'assignedUserIds.required' => 'Selecciona al menos un responsable.',
        ]);

        $totalMinutes = ((int) $this->estimatedHours * 60) + (int) $this->estimatedMinutes;

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'department_id' => $this->departmentId,
            'priority' => $this->priority,
            'estimated_minutes' => max($totalMinutes, 5),
            'deadline_at' => Carbon::parse($this->deadlineDate . ' ' . ($this->deadlineTime ?: '17:00')),
            'suggested_date' => $this->suggestedDate ?: null,
            'location_type' => $this->locationType,
            'location' => $this->location,
            'travel_minutes_before' => (int) $this->travelBefore,
            'travel_minutes_after' => (int) $this->travelAfter,
            'origin_type' => $this->originType ?: null,
            'origin_project_id' => $this->originProjectId ?: null,
        ];

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            $task->update($data);
            $taskService->updateAssignedUsers($task, $this->assignedUserIds, Auth::id());
            TaskHistory::log($task->id, Auth::id(), 'editada');
        } else {
            $taskService->createTask($data, $this->assignedUserIds, Auth::id(), $this->dependsOnTaskIds ?: []);
        }

        $this->showTaskModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea guardada con éxito.']);
    }

    public function editTask($taskId)
    {
        $this->ensureTenantConnection();
        $task = Task::with('assignments', 'dependencies')->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->departmentId = $task->department_id;
        $this->priority = $task->priority;
        $this->estimatedHours = intdiv($task->estimated_minutes, 60);
        $this->estimatedMinutes = $task->estimated_minutes % 60;
        $this->deadlineDate = $task->deadline_at?->format('Y-m-d');
        $this->deadlineTime = $task->deadline_at?->format('H:i') ?? '17:00';
        $this->suggestedDate = $task->suggested_date?->format('Y-m-d');
        $this->locationType = $task->location_type;
        $this->location = $task->location;
        $this->travelBefore = $task->travel_minutes_before;
        $this->travelAfter = $task->travel_minutes_after;
        $this->originType = $task->origin_type;
        $this->originProjectId = $task->origin_project_id;
        $this->assignedUserIds = $task->assignments->pluck('user_id')->toArray();
        $this->dependsOnTaskIds = $task->dependencies->pluck('depends_on_task_id')->toArray();

        $this->showTaskModal = true;
    }

    // ---------------------------------------------------------------
    // Programar / Reprogramar
    // ---------------------------------------------------------------

    public function openScheduleModal($taskId)
    {
        $this->ensureTenantConnection();
        $task = Task::with('currentSchedule')->findOrFail($taskId);

        $this->schedulingTaskId = $taskId;
        $this->scheduleConflicts = [];
        $this->rescheduleReason = '';

        if ($task->currentSchedule) {
            $this->scheduleDate = $task->currentSchedule->scheduled_start->format('Y-m-d');
            $this->scheduleStartTime = $task->currentSchedule->scheduled_start->format('H:i');
            $this->scheduleEndTime = $task->currentSchedule->scheduled_end->format('H:i');
        } else {
            $suggested = $task->suggested_date ? Carbon::parse($task->suggested_date) : now()->addDay();
            $this->scheduleDate = $suggested->format('Y-m-d');
            $this->scheduleStartTime = '08:00';
            $end = Carbon::parse('08:00')->addMinutes($task->total_occupied_minutes);
            $this->scheduleEndTime = $end->format('H:i');
        }

        $this->showScheduleModal = true;
    }

    public function checkScheduleConflicts(SchedulingService $schedulingService)
    {
        $this->ensureTenantConnection();

        $task = Task::with('assignments')->findOrFail($this->schedulingTaskId);
        $userIds = $task->assignments->pluck('user_id')->toArray();

        $start = Carbon::parse($this->scheduleDate . ' ' . $this->scheduleStartTime);
        $end = Carbon::parse($this->scheduleDate . ' ' . $this->scheduleEndTime);

        $this->scheduleConflicts = $schedulingService->checkAvailabilityForUsers($userIds, $start, $end, $task->id);
    }

    public function confirmSchedule(SchedulingService $schedulingService)
    {
        $this->ensureTenantConnection();

        $this->validate([
            'scheduleDate' => 'required|date',
            'scheduleStartTime' => 'required',
            'scheduleEndTime' => 'required',
        ]);

        $task = Task::with('assignments')->findOrFail($this->schedulingTaskId);
        $userIds = $task->assignments->pluck('user_id')->toArray();

        $start = Carbon::parse($this->scheduleDate . ' ' . $this->scheduleStartTime);
        $end = Carbon::parse($this->scheduleDate . ' ' . $this->scheduleEndTime);

        if ($end->lte($start)) {
            $this->addError('scheduleEndTime', 'La hora final debe ser después de la hora de inicio.');
            return;
        }

        $schedulingService->scheduleTask($task, $userIds, $start, $end, Auth::id(), $this->rescheduleReason ?: null);

        $this->showScheduleModal = false;
        $this->scheduleConflicts = [];
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea programada.']);
        $this->dispatch('calendar-refresh');
    }

    public function unschedule($taskId, SchedulingService $schedulingService)
    {
        $this->ensureTenantConnection();
        $schedulingService->unscheduleTask(Task::findOrFail($taskId), Auth::id());
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'La tarea volvió a la bandeja de sin programar.']);
        $this->dispatch('calendar-refresh');
    }

    // ---------------------------------------------------------------
    // Detalle / comentarios / cancelar / bloquear
    // ---------------------------------------------------------------

    public function openDetailModal($taskId)
    {
        $this->detailTaskId = $taskId;
        $this->newComment = '';
        $this->showDetailModal = true;
    }

    public function addDetailComment()
    {
        $this->ensureTenantConnection();
        $this->validate(['newComment' => 'required|string']);

        TaskComment::create([
            'task_id' => $this->detailTaskId,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
    }

    public function openCancelModal($taskId)
    {
        $this->cancelingTaskId = $taskId;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function confirmCancel(TaskService $taskService)
    {
        $this->ensureTenantConnection();
        $taskService->cancelTask(Task::findOrFail($this->cancelingTaskId), Auth::id(), $this->cancelReason);
        $this->showCancelModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea cancelada.']);
        $this->dispatch('calendar-refresh');
    }

    public function openBlockModal($taskId)
    {
        $this->blockingTaskId = $taskId;
        $this->blockReason = '';
        $this->showBlockModal = true;
    }

    public function confirmBlock(TimeTrackingService $service)
    {
        $this->ensureTenantConnection();
        $this->validate(['blockReason' => 'required|string']);
        $service->block(Task::findOrFail($this->blockingTaskId), Auth::id(), $this->blockReason);
        $this->showBlockModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea marcada como bloqueada.']);
    }

    public function unblockTask($taskId, TimeTrackingService $service)
    {
        $this->ensureTenantConnection();
        $service->unblock(Task::findOrFail($taskId), Auth::id());
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea desbloqueada.']);
    }

    // ---------------------------------------------------------------
    // Calendario (FullCalendar: eventos por AJAX + drag & drop)
    // ---------------------------------------------------------------

    /**
     * Filtra el listado de la Bandeja al hacer clic en una tarjeta del dashboard.
     */
    public function filterByDashboard($card)
    {
        if ($card === 'atrasadas') {
            $this->activeTab = 'atrasadas';
            return;
        }

        $map = [
            'programadas' => 'programada',
            'en_proceso' => 'en_proceso',
            'pausadas' => 'pausada',
            'bloqueadas' => 'bloqueada',
            'terminadas_hoy' => 'terminada',
            'sin_programar' => 'sin_programar',
        ];

        $this->activeTab = 'bandeja';
        $this->filterStatus = $map[$card] ?? '';
        $this->search = '';
        $this->filterDepartment = '';
        $this->filterPriority = '';
    }

    public function updatedCalendarDepartmentId()
    {
        $this->dispatch('calendar-refresh');
    }

    /**
     * Fuente de eventos para FullCalendar. Se llama vía $wire desde JS
     * cada vez que el calendario necesita (re)cargar el rango visible.
     */
    public function getCalendarEvents($start, $end)
    {
        $this->ensureTenantConnection();

        $colors = [
            'p1_urgente' => '#ef4444',
            'p2_alta' => '#f97316',
            'p3_normal' => '#3b82f6',
            'p4_baja' => '#9ca3af',
        ];

        $query = TaskSchedule::with(['task.department', 'user'])
            ->whereNotIn('schedule_status', ['cancelada'])
            ->where('scheduled_start', '<', $end)
            ->where('scheduled_end', '>', $start);

        if ($this->calendarDepartmentId) {
            $query->whereHas('task', fn($q) => $q->where('department_id', $this->calendarDepartmentId));
        }

        return $query->get()->map(function ($schedule) use ($colors) {
            $color = $colors[$schedule->task->priority] ?? '#6366f1';

            return [
                'id' => $schedule->id,
                'title' => ($schedule->user->name ?? 'Sin asignar') . ' · ' . $schedule->task->title,
                'start' => $schedule->scheduled_start->toIso8601String(),
                'end' => $schedule->scheduled_end->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'taskId' => $schedule->task_id,
                    'status' => $schedule->task->status,
                ],
            ];
        })->toArray();
    }

    /**
     * Se llama desde JS al soltar una tarea (arrastrada desde la bandeja
     * o movida/redimensionada dentro del calendario). En vez de guardar
     * de inmediato, abre el modal de programación ya prellenado para
     * que el conflicto (si existe) se revise antes de confirmar.
     */
    public function prefillScheduleFromDrop($taskId, $startIso, $endIso = null)
    {
        $this->ensureTenantConnection();

        $task = Task::findOrFail($taskId);
        $start = Carbon::parse($startIso);
        $end = $endIso ? Carbon::parse($endIso) : $start->copy()->addMinutes($task->total_occupied_minutes);

        $this->schedulingTaskId = $task->id;
        $this->scheduleDate = $start->format('Y-m-d');
        $this->scheduleStartTime = $start->format('H:i');
        $this->scheduleEndTime = $end->format('H:i');
        $this->rescheduleReason = '';
        $this->scheduleConflicts = [];
        $this->showScheduleModal = true;

        $this->checkScheduleConflicts(app(SchedulingService::class));
    }

    // ---------------------------------------------------------------
    // Horarios laborales
    // ---------------------------------------------------------------

    public function updatedOriginType($value)
    {
        if ($value !== 'proyecto') {
            $this->originProjectId = '';
        }
    }

    public function updatedScheduleFormUserId()
    {
        $this->ensureTenantConnection();
        $this->loadEmployeeScheduleForm();
    }

    private function loadEmployeeScheduleForm()
    {
        $this->employeeScheduleForm = [];
        for ($day = 0; $day <= 6; $day++) {
            $existing = $this->scheduleFormUserId
                ? EmployeeSchedule::where('user_id', $this->scheduleFormUserId)->where('day_of_week', $day)->first()
                : null;

            $this->employeeScheduleForm[$day] = [
                'active' => (bool) $existing,
                'start_time' => $existing->start_time ?? '08:00',
                'end_time' => $existing->end_time ?? '17:00',
                'break_start' => $existing->break_start ?? '12:00',
                'break_end' => $existing->break_end ?? '13:00',
            ];
        }
    }

    public function saveEmployeeSchedule()
    {
        $this->ensureTenantConnection();

        if (!$this->scheduleFormUserId) {
            $this->addError('scheduleFormUserId', 'Selecciona un trabajador.');
            return;
        }

        foreach ($this->employeeScheduleForm as $day => $config) {
            if (!empty($config['active'])) {
                EmployeeSchedule::updateOrCreate(
                    ['user_id' => $this->scheduleFormUserId, 'day_of_week' => $day],
                    [
                        'start_time' => $config['start_time'],
                        'end_time' => $config['end_time'],
                        'break_start' => $config['break_start'] ?: null,
                        'break_end' => $config['break_end'] ?: null,
                    ]
                );
            } else {
                EmployeeSchedule::where('user_id', $this->scheduleFormUserId)->where('day_of_week', $day)->delete();
            }
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Horario laboral guardado.']);
    }

    // ---------------------------------------------------------------
    // Indisponibilidades
    // ---------------------------------------------------------------

    public function openUnavailabilityModal()
    {
        $this->reset(['unavailUserId', 'unavailStart', 'unavailEnd', 'unavailReason']);
        $this->unavailType = 'permiso_personal';
        $this->showUnavailabilityModal = true;
    }

    public function saveUnavailability()
    {
        $this->ensureTenantConnection();

        $this->validate([
            'unavailUserId' => 'required',
            'unavailStart' => 'required|date',
            'unavailEnd' => 'required|date|after:unavailStart',
            'unavailType' => 'required',
        ], [
            'unavailUserId.required' => 'Selecciona el trabajador.',
            'unavailEnd.after' => 'La fecha final debe ser después de la inicial.',
        ]);

        EmployeeUnavailability::create([
            'user_id' => $this->unavailUserId,
            'start_datetime' => $this->unavailStart,
            'end_datetime' => $this->unavailEnd,
            'type' => $this->unavailType,
            'reason' => $this->unavailReason,
            'created_by' => Auth::id(),
        ]);

        $this->showUnavailabilityModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Indisponibilidad registrada.']);
    }

    public function deleteUnavailability($id)
    {
        $this->ensureTenantConnection();
        EmployeeUnavailability::where('id', $id)->delete();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Indisponibilidad eliminada.']);
    }

    // ---------------------------------------------------------------
    // Render
    // ---------------------------------------------------------------

    public function render()
    {
        $this->ensureTenantConnection();

        // Vencer automáticamente las que ya pasaron su fecha límite
        app(TaskService::class)->markOverdueTasks();

        $departments = TaskDepartment::where('status', 1)->orderBy('order')->get();
        $assignableUsers = $this->assignableUsersQuery()->get(['id', 'name']);

        $query = Task::with(['department', 'assignments', 'currentSchedule']);

        if ($this->filterDepartment) {
            $query->where('department_id', $this->filterDepartment);
        }
        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        } else {
            $query->where('status', '!=', 'cancelada');
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'p1_urgente','p2_alta','p3_normal','p4_baja')")
            ->orderBy('deadline_at')
            ->paginate(15);

        $unscheduledTasks = Task::with(['department', 'assignments'])
            ->where('status', 'sin_programar')
            ->orderByRaw("FIELD(priority, 'p1_urgente','p2_alta','p3_normal','p4_baja')")
            ->orderBy('deadline_at')
            ->get();

        $overdueTasks = Task::with(['department', 'assignments'])
            ->where(function ($q) {
                $q->where('status', 'vencida')
                  ->orWhere(function ($qq) {
                      $qq->whereIn('status', Task::OPEN_STATUSES)
                         ->whereNotNull('deadline_at')
                         ->where('deadline_at', '<', now());
                  });
            })
            ->orderBy('deadline_at')
            ->get();

        // Dashboard
        $dashboard = [
            'programadas' => Task::whereIn('status', ['programada', 'pendiente', 'disponible'])->count(),
            'en_proceso' => Task::where('status', 'en_proceso')->count(),
            'pausadas' => Task::where('status', 'pausada')->count(),
            'bloqueadas' => Task::where('status', 'bloqueada')->count(),
            'terminadas_hoy' => Task::where('status', 'terminada')->whereDate('updated_at', now()->toDateString())->count(),
            'atrasadas' => $overdueTasks->count(),
            'sin_programar' => Task::where('status', 'sin_programar')->count(),
        ];

        $unavailabilities = EmployeeUnavailability::with('user')
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->get();

        $projectsForOrigin = Project::orderBy('title')->limit(50)->get(['id', 'title']);

        return view('livewire.tenant.task-planner.manage-tasks', [
            'departments' => $departments,
            'assignableUsers' => $assignableUsers,
            'tasks' => $tasks,
            'unscheduledTasks' => $unscheduledTasks,
            'overdueTasks' => $overdueTasks,
            'dashboard' => $dashboard,
            'unavailabilities' => $unavailabilities,
            'projectsForOrigin' => $projectsForOrigin,
            'detailTask' => $this->detailTaskId ? Task::with(['department', 'assignments.user', 'comments.user', 'history.user', 'dependencies.dependsOnTask', 'schedules', 'pauses.user', 'timeLogs.user'])->find($this->detailTaskId) : null,
            'allOpenTasksForDependency' => Task::whereIn('status', Task::OPEN_STATUSES)->orderBy('title')->get(['id', 'title']),
        ])->layout('layouts.app');
    }
}
