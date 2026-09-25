<?php

namespace App\Livewire\Tenant\TaskPlanner;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskComment;
use App\Models\Tenant\TaskPlanner\TaskDepartment;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\TaskPlanner\TimeTrackingService;
use App\Services\TaskPlanner\AvailabilityCalendarService;
use Illuminate\Support\Facades\Auth;
use Exception;

class MyTasksToday extends Component
{
    use WithFileUploads;

    public $showPauseModal = false;
    public $pausingTaskId = null;
    public $pauseReason = '';
    public $pauseObservation = '';

    public $showFinishModal = false;
    public $finishingTaskId = null;
    public $finishNote = '';

    public $showMoreTimeModal = false;
    public $moreTimeTaskId = null;
    public $moreTimeMinutes = 30;
    public $moreTimeReason = '';

    public $showCommentModal = false;
    public $commentTaskId = null;
    public $newComment = '';

    public $showAttachModal = false;
    public $attachTaskId = null;
    public $attachFiles = [];

    public $showDetailModal = false;
    public $detailTaskId = null;

    public $showBlockModal = false;
    public $blockingTaskId = null;
    public $blockReason = '';

    public $activeView = 'today'; // 'today' | 'calendar'

    // Funcionalidad Auto-asignar
    public $enableAutoAssign = true; // Activo para pruebas
    public $showAutoAssignModal = false;
    public $availableTasksToAssign = [];

    public $userId;

    // --- NUEVAS PROPIEDADES PARA CREACIÓN DE TAREAS ---
    public $showCreateTaskModal = false;
    public $createTitle = '';
    public $createDescription = '';
    public $createDepartmentId = '';
    public $createEstimatedTime = 30;
    public $createPriority = 'p3_normal';
    public $departments = [];

    // --- NUEVAS PROPIEDADES PARA AUTO-AGENDAMIENTO ---
    public $showSelfScheduleModal = false;
    public $selfScheduleTaskId = null;
    public $selfScheduleDate = '';
    public $selfScheduleStartTime = '';
    public $selfScheduleEndTime = '';

    public function mount()
    {
        $this->userId = Auth::id();
        // Obtener departamentos una sola vez para el select
        $this->ensureTenantConnection();
        $this->departments = \App\Models\Tenant\TaskPlanner\TaskDepartment::orderBy('name')->get()->toArray();
    }

    public function boot()
    {
        abort_unless(
            \App\Helpers\PermissionHelper::isSuperAdmin()
            || \App\Helpers\PermissionHelper::userCan('Planeacion Mis Tareas', 'show')
            || \App\Helpers\PermissionHelper::userCan('Planeacion de Tareas', 'show'),
            403
        );

        $this->ensureTenantConnection();
    }

    /**
     * Cuando llega un aviso del planificador por WebSocket, refrescamos la
     * pantalla para que la tarjeta "AHORA DEBE REALIZAR" y la lista aparezcan
     * sin recargar. El toast/sonido lo maneja la campanita (NotificationBell).
     */
    #[On('echo-private:user.{userId},.NewTaskPlannerNotification')]
    public function onRealtimeUpdate()
    {
        // El solo hecho de que este método corra dispara un re-render de Livewire.
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

    // --- MÉTODOS DE CREACIÓN DE TAREAS (EMPLEADO) ---
    public function openCreateTaskModal()
    {
        $this->ensureTenantConnection();
        $this->reset(['createTitle', 'createDescription', 'createDepartmentId', 'createEstimatedTime', 'createPriority']);
        $this->createEstimatedTime = 30;
        $this->createPriority = 'p3_normal';
        $this->showCreateTaskModal = true;
    }

    public function saveNewTask(\App\Services\TaskPlanner\TaskService $taskService)
    {
        $this->ensureTenantConnection();

        $this->validate([
            'createTitle' => 'required|string|max:255',
            'createEstimatedTime' => 'required|integer|min:1',
            'createPriority' => 'required|in:p1_urgente,p2_alta,p3_normal,p4_baja',
        ]);

        $minutes = (int) $this->createEstimatedTime;

        if ($minutes < 1) {
            $this->addError('createEstimatedTime', 'El tiempo estimado debe ser mayor a 0 minutos.');
            return;
        }

        $data = [
            'title' => $this->createTitle,
            'description' => $this->createDescription,
            'department_id' => $this->departments[0]['id'] ?? null,
            'estimated_minutes' => $minutes,
            'priority' => $this->createPriority,
            'location_type' => 'empresa', // Por defecto interno
        ];

        // El usuario se autoasigna la tarea que él mismo crea
        $task = $taskService->createTask($data, [$this->userId], $this->userId);

        $this->showCreateTaskModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea creada y asignada a ti.']);
    }

    // --- MÉTODOS DE AUTO-AGENDAMIENTO (EMPLEADO) ---
    public function openSelfScheduleModal($taskId)
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($taskId), 403);

        $this->selfScheduleTaskId = $taskId;
        $this->selfScheduleDate = now()->toDateString();
        
        $now = now();
        $this->selfScheduleStartTime = $now->copy()->addMinutes(30 - ($now->minute % 30))->format('H:i');
        
        $task = Task::findOrFail($taskId);
        $this->selfScheduleEndTime = \Carbon\Carbon::parse($this->selfScheduleStartTime)
            ->addMinutes($task->estimated_minutes)
            ->format('H:i');

        $this->showSelfScheduleModal = true;
    }

    public function updatedSelfScheduleStartTime($val)
    {
        if ($this->selfScheduleTaskId && $val) {
            $this->ensureTenantConnection();
            $task = Task::find($this->selfScheduleTaskId);
            if ($task) {
                $this->selfScheduleEndTime = \Carbon\Carbon::parse($val)
                    ->addMinutes($task->estimated_minutes)
                    ->format('H:i');
            }
        }
    }

    public function confirmSelfSchedule(\App\Services\TaskPlanner\SchedulingService $schedulingService)
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($this->selfScheduleTaskId), 403);

        $this->validate([
            'selfScheduleDate' => 'required|date',
            'selfScheduleStartTime' => 'required',
            'selfScheduleEndTime' => 'required',
        ]);

        $start = \Carbon\Carbon::parse("{$this->selfScheduleDate} {$this->selfScheduleStartTime}");
        $end = \Carbon\Carbon::parse("{$this->selfScheduleDate} {$this->selfScheduleEndTime}");

        if ($start->startOfDay()->isPast() && !$start->isToday()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No puedes agendar una tarea para un día que ya pasó.']);
            return;
        }

        if ($end->lte($start)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La hora de fin debe ser posterior a la de inicio.']);
            return;
        }

        $task = Task::findOrFail($this->selfScheduleTaskId);

        // Agendar la tarea directamente para el usuario
        $schedulingService->scheduleTask($task, [$this->userId], $start, $end, $this->userId, 'Auto-agendada por trabajador');

        $this->showSelfScheduleModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea agendada exitosamente.']);
        $this->dispatch('calendar-refresh');
    }

    public function updateScheduleFromCalendar($scheduleId, $startIso, $endIso = null)
    {
        $this->ensureTenantConnection();
        
        $schedule = TaskSchedule::with('task')->find($scheduleId);
        if (!$schedule || $schedule->user_id !== $this->userId) {
            return;
        }

        // Si ya está iniciada, no debería dejarse arrastrar, pero por si acaso validamos
        if (in_array($schedule->task->status, ['en_proceso', 'terminada', 'cancelada'])) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No puedes cambiar la hora de una tarea iniciada o terminada.']);
            return;
        }

        $start = \Carbon\Carbon::parse($startIso);
        
        if ($start->startOfDay()->isPast() && !$start->isToday()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No puedes mover una tarea a un día que ya pasó.']);
            $this->dispatch('calendar-refresh');
            return;
        }

        if ($endIso) {
            $end = \Carbon\Carbon::parse($endIso);
        } else {
            // Si el calendario no envía endIso (sólo soltó, no redimensionó), mantener la duración
            $duration = $schedule->scheduled_start->diffInMinutes($schedule->scheduled_end);
            $end = $start->copy()->addMinutes($duration);
        }

        $schedulingService = app(\App\Services\TaskPlanner\SchedulingService::class);
        $schedulingService->scheduleTask($schedule->task, [$this->userId], $start, $end, $this->userId, 'Movida en el calendario personal');
        
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Horario actualizado.']);
        $this->dispatch('calendar-refresh');
    }


    public function startTask($taskId, TimeTrackingService $service)
    {
        $this->ensureTenantConnection();

        try {
            $service->start(Task::findOrFail($taskId), Auth::id());
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea iniciada.']);
        } catch (Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function toggleChecklistItem($checkId)
    {
        $this->ensureTenantConnection();
        $checklist = \App\Models\Tenant\TaskPlanner\TaskChecklist::findOrFail($checkId);
        
        // Solo se pueden marcar los pasos mientras la tarea se está ejecutando.
        if (in_array($checklist->task->status, ['en_proceso', 'pausada'])) {
            $checklist->update([
                'is_completed' => !$checklist->is_completed,
                'completed_at' => !$checklist->is_completed ? now() : null,
            ]);
        }
    }

    public function openPauseModal($taskId)
    {
        $this->reset(['pauseReason', 'pauseObservation']);
        $this->pausingTaskId = $taskId;
        $this->showPauseModal = true;
    }

    public function confirmPause(TimeTrackingService $service)
    {
        $this->ensureTenantConnection();

        $this->validate([
            'pauseReason' => 'required|string',
        ], [
            'pauseReason.required' => 'Selecciona el motivo de la pausa.',
        ]);

        $service->pause(Task::findOrFail($this->pausingTaskId), Auth::id(), $this->pauseReason, $this->pauseObservation);

        $this->showPauseModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea pausada.']);
    }

    public function resumeTask($taskId, TimeTrackingService $service)
    {
        $this->ensureTenantConnection();
        $service->resume(Task::findOrFail($taskId), Auth::id());
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea reanudada.']);
    }

    public function openFinishModal($taskId)
    {
        $this->reset(['finishNote']);
        $this->finishingTaskId = $taskId;
        $this->showFinishModal = true;
    }

    public function confirmFinish(TimeTrackingService $service)
    {
        $this->ensureTenantConnection();

        try {
            $service->finish(Task::findOrFail($this->finishingTaskId), Auth::id(), $this->finishNote);
        } catch (Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->showFinishModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea terminada.']);
    }

    public function openMoreTimeModal($taskId)
    {
        $this->reset(['moreTimeMinutes', 'moreTimeReason']);
        $this->moreTimeMinutes = 30;
        $this->moreTimeTaskId = $taskId;
        $this->showMoreTimeModal = true;
    }

    public function confirmMoreTime(TimeTrackingService $service)
    {
        $this->ensureTenantConnection();

        $affected = $service->requestMoreTime(Task::findOrFail($this->moreTimeTaskId), Auth::id(), (int) $this->moreTimeMinutes, $this->moreTimeReason);

        $this->showMoreTimeModal = false;

        $msg = count($affected) > 0
            ? 'Se avisó a Gerencia. Esto afecta ' . count($affected) . ' tarea(s) programada(s) después.'
            : 'Se registró la solicitud de más tiempo.';

        $this->dispatch('show-toast', ['type' => 'success', 'message' => $msg]);
    }

    public function openCommentModal($taskId)
    {
        $this->reset(['newComment']);
        $this->commentTaskId = $taskId;
        $this->showCommentModal = true;
    }

    public function openAttachModal($taskId)
    {
        $this->reset(['attachFiles']);
        $this->attachTaskId = $taskId;
        $this->showAttachModal = true;
    }

    public function saveAttachments()
    {
        $this->ensureTenantConnection();

        $this->validate([
            'attachFiles' => 'required|array|min:1',
            'attachFiles.*' => 'file|max:10240',
        ], [
            'attachFiles.required' => 'Selecciona al menos un archivo o foto.',
            'attachFiles.*.max' => 'Cada archivo debe pesar máximo 10 MB.',
        ]);

        $task = Task::findOrFail($this->attachTaskId);

        foreach ($this->attachFiles as $file) {
            $path = $file->store('task_attachments', 'public');
            $task->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        \App\Models\Tenant\TaskPlanner\TaskHistory::log($task->id, Auth::id(), 'adjunto_agregado', null, count($this->attachFiles) . ' archivo(s)');

        $this->showAttachModal = false;
        $this->reset(['attachFiles']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Archivos adjuntados.']);
    }

    public function addComment()
    {
        $this->ensureTenantConnection();

        $this->validate(['newComment' => 'required|string']);

        TaskComment::create([
            'task_id' => $this->commentTaskId,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Observación agregada.']);
    }

    /**
     * Un trabajador normal solo puede ver/comentar/bloquear tareas que tiene
     * asignadas o programadas — nunca cualquier tarea del sistema por ID.
     */
    private function userOwnsTask($taskId): bool
    {
        $userId = Auth::id();

        return Task::where('id', $taskId)
            ->where(function ($q) use ($userId) {
                $q->whereHas('assignments', fn($qq) => $qq->where('user_id', $userId))
                  ->orWhereHas('schedules', fn($qq) => $qq->where('user_id', $userId));
            })
            ->exists();
    }

    public function openDetailModal($taskId)
    {
        $this->ensureTenantConnection();

        abort_unless($this->userOwnsTask($taskId), 403);

        $this->detailTaskId = $taskId;
        $this->newComment = '';
        $this->showDetailModal = true;
    }

    public function addDetailComment()
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($this->detailTaskId), 403);

        $this->validate(['newComment' => 'required|string']);

        TaskComment::create([
            'task_id' => $this->detailTaskId,
            'user_id' => Auth::id(),
            'content' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Comentario agregado.']);
    }

    public function openBlockModal($taskId)
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($taskId), 403);

        $this->blockingTaskId = $taskId;
        $this->blockReason = '';
        $this->showBlockModal = true;
    }

    public function confirmBlock(TimeTrackingService $service)
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($this->blockingTaskId), 403);

        $this->validate(['blockReason' => 'required|string']);
        $service->block(Task::findOrFail($this->blockingTaskId), Auth::id(), $this->blockReason);
        $this->showBlockModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea marcada como bloqueada.']);
    }

    public function unblockTask($taskId, TimeTrackingService $service)
    {
        $this->ensureTenantConnection();
        abort_unless($this->userOwnsTask($taskId), 403);

        $service->unblock(Task::findOrFail($taskId), Auth::id());
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea desbloqueada.']);
    }

    /**
     * Fuente de eventos para el calendario de solo lectura del trabajador
     * (su propio "Mi Calendario"): siempre sus propias tareas programadas,
     * más las mismas marcas de indisponibilidad/horario que ve gerencia.
     */
    public function getMyCalendarEvents($start, $end, AvailabilityCalendarService $availabilityService)
    {
        $this->ensureTenantConnection();

        $userId = Auth::id();

        $colors = [
            'p1_urgente' => '#ef4444',
            'p2_alta' => '#f97316',
            'p3_normal' => '#3b82f6',
            'p4_baja' => '#9ca3af',
        ];

        $events = TaskSchedule::with('task')
            ->where('user_id', $userId)
            ->whereNotIn('schedule_status', ['cancelada'])
            ->where('scheduled_start', '<', $end)
            ->where('scheduled_end', '>', $start)
            ->get()
            ->map(function ($schedule) use ($colors) {
                $color = $colors[$schedule->task->priority] ?? '#6366f1';

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->task->title,
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

        return array_merge($events, $availabilityService->backgroundEventsForUser($userId, $start, $end));
    }

    public function openAutoAssignModal()
    {
        $this->ensureTenantConnection();
        $userId = Auth::id();

        // Buscamos tareas sin programar que estén explícitamente asignadas al usuario, 
        // o si es posible, de su departamento (asumiendo asignación general).
        // Por seguridad, limitamos a las tareas donde él es responsable pero están sin agendar,
        // o tareas sueltas de sus departamentos activos hoy.
        
        $todaySchedules = TaskSchedule::where('user_id', $userId)
            ->whereDate('scheduled_start', now()->toDateString())
            ->whereNotIn('schedule_status', ['cancelada'])
            ->get();
        $departmentIds = $todaySchedules->pluck('task.department_id')->unique();

        $query = Task::where('status', 'sin_programar');

        if ($departmentIds->isNotEmpty()) {
            $query->where(function ($q) use ($userId, $departmentIds) {
                $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $userId))
                  ->orWhereIn('department_id', $departmentIds);
            });
        } else {
            $query->whereHas('assignments', fn($sq) => $sq->where('user_id', $userId));
        }

        $this->availableTasksToAssign = $query->orderBy('priority')->get();
        $this->showAutoAssignModal = true;
    }

    public function confirmAutoAssign($taskId, \App\Services\TaskPlanner\SchedulingService $scheduleService)
    {
        $this->ensureTenantConnection();
        $task = Task::findOrFail($taskId);
        
        $duration = $task->estimated_minutes > 0 ? $task->estimated_minutes : 60; // 1 hora por defecto si no tiene estimación
        $slots = $scheduleService->findAvailableSlots([Auth::id()], $duration);

        if (empty($slots)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes huecos libres en tu agenda de los próximos días para esta tarea (' . $duration . ' min).']);
            return;
        }

        \App\Models\Tenant\TaskPlanner\TaskAssignment::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => Auth::id()
        ]);

        $slot = $slots[0];
        $scheduleService->scheduleTask($task, [Auth::id()], $slot['start'], $slot['end'], Auth::id(), 'Autoasignada por el usuario');

        $this->showAutoAssignModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea autoasignada correctamente. Revisa tu agenda.']);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $userId = Auth::id();
        $today = now();

        $todaySchedules = TaskSchedule::where('user_id', $userId)
            ->whereDate('scheduled_start', $today->toDateString())
            ->whereNotIn('schedule_status', ['cancelada'])
            ->with(['task.department', 'task.comments.user', 'task.pauses', 'task.materials.item', 'task.checklists', 'task.attachments'])
            ->orderBy('scheduled_start')
            ->get();

        $daySchedule = EmployeeSchedule::where('user_id', $userId)
            ->where('day_of_week', $today->dayOfWeek)
            ->first();

        $scheduledMinutes = $todaySchedules->sum(function ($s) {
            return $s->scheduled_start->diffInMinutes($s->scheduled_end);
        });

        // Tiempo laboral disponible hoy (jornada menos almuerzo)
        $availableMinutes = 0;
        if ($daySchedule) {
            $availableMinutes = (int) \Carbon\Carbon::parse($daySchedule->start_time)
                ->diffInMinutes(\Carbon\Carbon::parse($daySchedule->end_time));
            if ($daySchedule->break_start && $daySchedule->break_end) {
                $availableMinutes -= (int) \Carbon\Carbon::parse($daySchedule->break_start)
                    ->diffInMinutes(\Carbon\Carbon::parse($daySchedule->break_end));
            }
        }

        // "pendiente" y "vencida" son iniciables por el trabajador: una tarea que
        // pasó su fecha límite no debe desaparecer de su pantalla si sigue agendada hoy.
        $startable = ['pendiente', 'vencida'];

        $currentSchedule = $todaySchedules->first(function ($s) {
            return $s->task->status === 'en_proceso' || $s->task->status === 'pausada';
        }) ?? $todaySchedules->first(function ($s) use ($today, $startable) {
            return in_array($s->task->status, $startable) && $s->scheduled_start->lte($today);
        }) ?? $todaySchedules->first(function ($s) use ($startable) {
            return in_array($s->task->status, $startable);
        });

        $upcomingSchedules = $todaySchedules->reject(function ($s) use ($currentSchedule) {
            return $currentSchedule && $s->id === $currentSchedule->id;
        })->filter(function ($s) {
            return !in_array($s->task->status, ['terminada', 'cancelada']);
        });

        // Tareas de aprovechamiento (P4 sin programar) del/los departamento(s) del trabajador
        $fillerTasks = collect();
        if (!$currentSchedule) {
            $departmentIds = $todaySchedules->pluck('task.department_id')->unique();
            if ($departmentIds->isNotEmpty()) {
                $fillerTasks = Task::where('status', 'sin_programar')
                    ->where('priority', 'p4_baja')
                    ->whereIn('department_id', $departmentIds)
                    ->whereHas('assignments', fn($q) => $q->where('user_id', $userId))
                    ->limit(5)
                    ->get();
            }
        }

        // Vista rápida de los próximos días (hasta 7 días hacia adelante)
        $upcomingDays = TaskSchedule::where('user_id', $userId)
            ->whereNotIn('schedule_status', ['cancelada', 'terminada'])
            ->whereDate('scheduled_start', '>', $today->toDateString())
            ->whereDate('scheduled_start', '<=', $today->copy()->addDays(7)->toDateString())
            ->with('task.department')
            ->orderBy('scheduled_start')
            ->get()
            ->groupBy(fn($s) => $s->scheduled_start->toDateString());

        $detailTask = $this->detailTaskId
            ? Task::with(['department', 'assignments.user', 'comments.user', 'history.user', 'schedules', 'pauses.user', 'timeLogs.user', 'materials.item', 'checklists', 'attachments'])->find($this->detailTaskId)
            : null;

        return view('livewire.tenant.task-planner.my-tasks-today', [
            'today' => $today,
            'daySchedule' => $daySchedule,
            'todaySchedules' => $todaySchedules,
            'scheduledMinutes' => $scheduledMinutes,
            'availableMinutes' => $availableMinutes,
            'currentSchedule' => $currentSchedule,
            'upcomingSchedules' => $upcomingSchedules,
            'upcomingDays' => $upcomingDays,
            'fillerTasks' => $fillerTasks,
            'pauseReasons' => \App\Models\Tenant\TaskPlanner\TaskPause::REASONS,
            'detailTask' => $detailTask,
        ])->layout('layouts.app');
    }
}
