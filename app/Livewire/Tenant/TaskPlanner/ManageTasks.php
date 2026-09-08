<?php

namespace App\Livewire\Tenant\TaskPlanner;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskDepartment;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskComment;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use App\Models\Tenant\TaskPlanner\TaskAssignment;
use App\Models\Tenant\TaskPlanner\TaskTimeLog;
use App\Models\Tenant\TaskPlanner\TaskPause;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeUnavailability;
use App\Models\Tenant\Projects\Project;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\TaskPlanner\TaskMaterial;
use App\Models\Tenant\TaskPlanner\TaskChecklist;
use App\Models\Tenant\TaskPlanner\TaskAttachment;
use App\Models\Tenant\TaskPlanner\RecurringTask;
use App\Services\Tenant\TenantManager;
use App\Services\TaskPlanner\TaskService;
use App\Services\TaskPlanner\SchedulingService;
use App\Services\TaskPlanner\ReschedulingService;
use App\Services\TaskPlanner\TimeTrackingService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Livewire\WithFileUploads;

class ManageTasks extends Component
{
    use WithPagination, WithFileUploads;

    public $userId;
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

    // Recurrencia
    public $recurrenceType = 'none'; // none | daily | weekly | monthly
    public $recurrenceWeekday = 1;
    public $recurrenceMonthday = 1;

    // Colecciones temporales para crear/editar
    public $tempMaterials = [];
    public $tempChecklists = [];
    public $tempAttachments = []; // Nuevos archivos subidos
    public $existingAttachments = []; // Archivos que ya tenía la tarea

    // Buscador híbrido de materiales
    public $searchMaterial = '';
    public $searchMaterialResults = [];
    public $materialSearchMode = 'inventory'; // 'inventory' o 'free'
    public $freeMaterialName = '';
    public $freeMaterialQty = 1;

    // Modal Programar / Reprogramar
    public $showScheduleModal = false;
    public $schedulingTaskId = null;
    public $scheduleDate = '';
    public $scheduleStartTime = '';
    public $scheduleEndTime = '';
    public $rescheduleReason = '';
    public $scheduleConflicts = [];
    public $suggestedSlots = [];

    // Modal propuesta de reprogramación
    public $showProposalModal = false;
    public $proposalGroups = [];

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
    public $calendarUserId = '';

    // Tab Reportes
    public $reportFrom = '';
    public $reportTo = '';

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
        // El "Panel de Gerencia" es solo para administración (perfiles 1 y 2).
        // El menú lateral ya lo oculta; esto cierra el acceso por URL directa
        // y también en cada petición de Livewire, no solo en la carga inicial.
        abort_unless(in_array(Auth::user()?->profile_id, [1, 2]), 403);

        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->userId = Auth::id();
        $this->deadlineDate = now()->addDay()->format('Y-m-d');
        $this->reportFrom = now()->subDays(30)->format('Y-m-d');
        $this->reportTo = now()->format('Y-m-d');
    }

    /**
     * Refresca el panel cuando llega un aviso del planificador por WebSocket
     * (ej. un trabajador pidió más tiempo). El toast/sonido lo da la campanita.
     */
    #[On('echo-private:user.{userId},.NewTaskPlannerNotification')]
    public function onRealtimeUpdate()
    {
        // El re-render de Livewire ocurre solo con que este método se ejecute.
    }

    /**
     * Reportes de productividad y de exactitud de tiempos (spec 42-43).
     */
    private function buildReports($assignableUsers, $departments): array
    {
        $from = Carbon::parse(($this->reportFrom ?: now()->subDays(30)->format('Y-m-d')) . ' 00:00:00');
        $to = Carbon::parse(($this->reportTo ?: now()->format('Y-m-d')) . ' 23:59:59');

        $deptNames = $departments->pluck('name', 'id');

        $perWorker = [];
        foreach ($assignableUsers as $user) {
            $assignedTaskIds = TaskAssignment::where('user_id', $user->id)->pluck('task_id');

            $logs = TaskTimeLog::where('user_id', $user->id)
                ->whereNotNull('finished_at')
                ->whereBetween('finished_at', [$from, $to])
                ->get();

            $done = $logs->count();
            $estSum = (int) $logs->sum('estimated_minutes');
            $realSum = (int) $logs->sum('real_minutes');

            $scheduledMin = (int) TaskSchedule::where('user_id', $user->id)
                ->whereBetween('scheduled_start', [$from, $to])
                ->get()
                ->sum(fn($s) => $s->scheduled_start->diffInMinutes($s->scheduled_end));

            $pauses = TaskPause::where('user_id', $user->id)
                ->whereBetween('started_at', [$from, $to])
                ->selectRaw('reason, COUNT(*) as c')
                ->groupBy('reason')
                ->orderByDesc('c')
                ->limit(3)
                ->get();

            $perWorker[] = [
                'name' => $user->name,
                'assigned' => Task::whereIn('id', $assignedTaskIds)->whereBetween('created_at', [$from, $to])->count(),
                'done' => $done,
                'pending' => Task::whereIn('id', $assignedTaskIds)->whereIn('status', ['sin_programar', 'pendiente'])->count(),
                'overdue' => Task::whereIn('id', $assignedTaskIds)->where('status', 'vencida')->count(),
                'scheduled_min' => $scheduledMin,
                'worked_min' => $realSum,
                'avg_est' => $done ? (int) round($estSum / $done) : 0,
                'avg_real' => $done ? (int) round($realSum / $done) : 0,
                'diff' => $realSum - $estSum,
                'reschedules' => TaskHistory::where('action', 'reprogramada')
                    ->whereIn('task_id', $assignedTaskIds)
                    ->whereBetween('created_at', [$from, $to])
                    ->count(),
                'pause_causes' => $pauses->map(fn($p) => (TaskPause::REASONS[$p->reason] ?? $p->reason) . ' (' . $p->c . ')')->implode(', ') ?: '—',
            ];
        }

        // Exactitud de tiempos por departamento
        $accuracy = TaskTimeLog::query()
            ->join('tsk_tasks', 'tsk_tasks.id', '=', 'tsk_task_time_logs.task_id')
            ->whereNotNull('tsk_task_time_logs.finished_at')
            ->whereBetween('tsk_task_time_logs.finished_at', [$from, $to])
            ->selectRaw('tsk_tasks.department_id, COUNT(*) as c, AVG(tsk_task_time_logs.estimated_minutes) as est, AVG(tsk_task_time_logs.real_minutes) as real_m')
            ->groupBy('tsk_tasks.department_id')
            ->get()
            ->map(fn($r) => [
                'department' => $deptNames[$r->department_id] ?? '—',
                'count' => $r->c,
                'avg_est' => (int) round($r->est),
                'avg_real' => (int) round($r->real_m),
                'deviation_pct' => $r->est > 0 ? (int) round((($r->real_m - $r->est) / $r->est) * 100) : 0,
            ])->toArray();

        return [
            'from' => $from,
            'to' => $to,
            'per_worker' => $perWorker,
            'accuracy' => $accuracy,
        ];
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
            'originType', 'originProjectId', 'assignedUserIds',
            'tempMaterials', 'tempChecklists', 'tempAttachments', 'existingAttachments',
            'searchMaterial', 'searchMaterialResults', 'freeMaterialName', 'freeMaterialQty',
            'recurrenceType', 'recurrenceWeekday', 'recurrenceMonthday',
        ]);
        $this->materialSearchMode = 'inventory';
        $this->priority = 'p3_normal';
        $this->estimatedMinutes = 30;
        $this->locationType = 'empresa';
        $this->recurrenceType = 'none';
        $this->recurrenceWeekday = 1;
        $this->recurrenceMonthday = 1;
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
            $task = $taskService->createTask($data, $this->assignedUserIds, Auth::id());
        }

        // Guardar Materiales
        $task->materials()->delete();
        foreach ($this->tempMaterials as $mat) {
            $task->materials()->create([
                'item_id' => $mat['item_id'],
                'name' => $mat['name'],
                'estimated_quantity' => $mat['estimated_quantity']
            ]);
        }

        // Guardar Checklists
        $task->checklists()->delete();
        foreach (array_values($this->tempChecklists) as $idx => $chk) {
            if (trim($chk['description'] ?? '') === '') {
                continue;
            }
            $task->checklists()->create([
                'description' => $chk['description'],
                'order' => $idx + 1,
                'is_required' => $chk['is_required'] ?? false,
                'is_completed' => $chk['is_completed'] ?? false,
            ]);
        }

        // Guardar Archivos adjuntos
        if (!empty($this->tempAttachments)) {
            foreach ($this->tempAttachments as $file) {
                $path = $file->store('task_attachments', 'public');
                $task->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize()
                ]);
            }
        }

        $this->syncRecurrence($task);

        $this->showTaskModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea guardada con éxito.']);
    }

    /**
     * Crea / actualiza / borra la plantilla recurrente ligada a la tarea.
     */
    private function syncRecurrence(Task $task): void
    {
        $existing = RecurringTask::where('base_task_id', $task->id)->first();

        if ($this->recurrenceType === 'none') {
            $existing?->delete();
            return;
        }

        $token = match ($this->recurrenceType) {
            'daily'   => 'daily',
            'weekly'  => 'weekly:' . (int) $this->recurrenceWeekday,
            'monthly' => 'monthly:' . min(max((int) $this->recurrenceMonthday, 1), 28),
            default   => 'daily',
        };

        $payload = [
            'cron_expression' => $token,
            'is_active' => true,
        ];

        if (!$existing || $existing->cron_expression !== $token) {
            $payload['next_run_at'] = RecurringTask::nextRunFrom($token, now());
        }

        if ($existing) {
            $existing->update($payload);
        } else {
            RecurringTask::create(array_merge(['base_task_id' => $task->id], $payload));
        }
    }

    public function editTask($taskId)
    {
        $this->ensureTenantConnection();
        $task = Task::with('assignments')->findOrFail($taskId);

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

        $this->tempMaterials = $task->materials->map(function($m) {
            return [
                'id' => $m->id,
                'item_id' => $m->item_id,
                'name' => $m->name,
                'estimated_quantity' => $m->estimated_quantity,
                'inventory_name' => $m->item_id ? ($m->item->name ?? 'Ítem eliminado') : null
            ];
        })->toArray();

        $this->tempChecklists = $task->checklists->map(function($c) {
            return [
                'id' => $c->id,
                'description' => $c->description,
                'is_required' => (bool) $c->is_required,
                'is_completed' => $c->is_completed
            ];
        })->toArray();

        $this->existingAttachments = $task->attachments->toArray();
        $this->tempAttachments = [];

        // Recurrencia existente
        $this->recurrenceType = 'none';
        $this->recurrenceWeekday = 1;
        $this->recurrenceMonthday = 1;
        $rt = RecurringTask::where('base_task_id', $task->id)->first();
        if ($rt && $rt->is_active) {
            $t = $rt->cron_expression;
            if ($t === 'daily') {
                $this->recurrenceType = 'daily';
            } elseif (str_starts_with((string) $t, 'weekly:')) {
                $this->recurrenceType = 'weekly';
                $this->recurrenceWeekday = (int) explode(':', $t)[1];
            } elseif (str_starts_with((string) $t, 'monthly:')) {
                $this->recurrenceType = 'monthly';
                $this->recurrenceMonthday = (int) explode(':', $t)[1];
            }
        }

        $this->showTaskModal = true;
    }

    // --- Lógica de Checklists en Modal ---
    public function addChecklistItem()
    {
        $this->tempChecklists[] = ['description' => '', 'is_required' => false, 'is_completed' => false];
    }

    public function removeChecklistItem($index)
    {
        unset($this->tempChecklists[$index]);
        $this->tempChecklists = array_values($this->tempChecklists);
    }

    // --- Lógica de Materiales en Modal ---
    public function updatedSearchMaterial()
    {
        if (strlen($this->searchMaterial) >= 2) {
            $this->searchMaterialResults = Items::where('name', 'like', '%' . $this->searchMaterial . '%')
                ->orWhere('internal_code', 'like', '%' . $this->searchMaterial . '%')
                ->limit(8)
                ->get(['id', 'name', 'internal_code'])
                ->toArray();
        } else {
            $this->searchMaterialResults = [];
        }
    }

    public function addInventoryMaterial($itemId, $name)
    {
        $this->tempMaterials[] = [
            'item_id' => $itemId,
            'name' => null,
            'estimated_quantity' => 1,
            'inventory_name' => $name
        ];
        $this->searchMaterial = '';
        $this->searchMaterialResults = [];
    }

    public function addFreeMaterial()
    {
        if (trim($this->freeMaterialName) === '') return;
        
        $this->tempMaterials[] = [
            'item_id' => null,
            'name' => $this->freeMaterialName,
            'estimated_quantity' => $this->freeMaterialQty ?: 1,
            'inventory_name' => null
        ];
        $this->freeMaterialName = '';
        $this->freeMaterialQty = 1;
    }

    public function removeMaterial($index)
    {
        unset($this->tempMaterials[$index]);
        $this->tempMaterials = array_values($this->tempMaterials);
    }

    public function deleteExistingAttachment($id)
    {
        $this->ensureTenantConnection();
        $attachment = TaskAttachment::findOrFail($id);
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();
        $this->existingAttachments = array_filter($this->existingAttachments, fn($a) => $a['id'] != $id);
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
        $this->suggestedSlots = [];
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

    /**
     * "Buscar disponibilidad": propone los primeros huecos libres dentro del
     * horario laboral de el/los responsable(s), respetando la fecha límite.
     */
    public function findSlots(SchedulingService $schedulingService)
    {
        $this->ensureTenantConnection();

        $task = Task::with('assignments')->findOrFail($this->schedulingTaskId);
        $userIds = $task->assignments->pluck('user_id')->toArray();

        if (empty($userIds)) {
            $this->suggestedSlots = [];
            $this->addError('scheduleStartTime', 'La tarea no tiene responsables asignados.');
            return;
        }

        $slots = $schedulingService->findAvailableSlots(
            $userIds,
            max($task->total_occupied_minutes, 5),
            $task->deadline_at
        );

        $this->suggestedSlots = collect($slots)->map(fn($slot) => [
            'date'  => $slot['start']->format('Y-m-d'),
            'start' => $slot['start']->format('H:i'),
            'end'   => $slot['end']->format('H:i'),
            'label' => ucfirst($slot['start']->translatedFormat('D d/m')) . ' · '
                     . $slot['start']->format('H:i') . ' - ' . $slot['end']->format('H:i'),
        ])->toArray();

        if (empty($this->suggestedSlots)) {
            $this->addError('scheduleStartTime', 'No se encontraron huecos libres antes de la fecha límite. Revisa los horarios laborales.');
        }
    }

    public function applySlot($index)
    {
        $slot = $this->suggestedSlots[$index] ?? null;
        if (!$slot) {
            return;
        }

        $this->scheduleDate = $slot['date'];
        $this->scheduleStartTime = $slot['start'];
        $this->scheduleEndTime = $slot['end'];
        $this->scheduleConflicts = [];
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

        $hadConflicts = !empty($this->scheduleConflicts);

        $schedulingService->scheduleTask($task, $userIds, $start, $end, Auth::id(), $this->rescheduleReason ?: null);

        $this->showScheduleModal = false;
        $this->scheduleConflicts = [];
        $this->suggestedSlots = [];
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Tarea programada.']);
        $this->dispatch('calendar-refresh');

        // Si se metió sobre una agenda ocupada, ofrecer reorganizar lo que quedó pisado.
        if ($hadConflicts) {
            $this->buildRescheduleProposal($userIds, $start, $task->id);
        }
    }

    // ---------------------------------------------------------------
    // Propuesta de reprogramación (el sistema propone, Gerencia decide)
    // ---------------------------------------------------------------

    private function buildRescheduleProposal(array $userIds, Carbon $pivot, ?int $keepTaskId = null): void
    {
        $service = app(ReschedulingService::class);
        $groups = [];

        foreach (array_unique($userIds) as $userId) {
            $rows = $service->proposeForUser((int) $userId, $pivot, $keepTaskId);
            if (!empty($rows)) {
                $groups[] = [
                    'user_id' => $userId,
                    'user_name' => optional(User::find($userId))->name ?? 'Trabajador',
                    'rows' => $rows,
                ];
            }
        }

        if (!empty($groups)) {
            $this->proposalGroups = $groups;
            $this->showProposalModal = true;
        }
    }

    public function acceptProposal()
    {
        $this->ensureTenantConnection();

        $service = app(ReschedulingService::class);
        $total = 0;

        foreach ($this->proposalGroups as $group) {
            $total += $service->apply($group['rows'], Auth::id());
        }

        $this->showProposalModal = false;
        $this->proposalGroups = [];
        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Se reprogramaron {$total} tarea(s)."]);
        $this->dispatch('calendar-refresh');
    }

    public function dismissProposal()
    {
        $this->showProposalModal = false;
        $this->proposalGroups = [];
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
            'programadas' => 'pendiente',
            'en_proceso' => 'en_proceso',
            'pausadas' => 'pausada',
            'bloqueadas' => 'bloqueada',
            'terminadas_hoy' => 'terminada',
            'sin_programar' => 'sin_programar',
        ];

        $this->activeTab = 'bandeja';
        $this->search = '';
        $this->filterDepartment = '';
        $this->filterPriority = '';

        if ($card === 'urgentes') {
            $this->filterStatus = '';
            $this->filterPriority = 'p1_urgente';
            return;
        }

        $this->filterStatus = $map[$card] ?? '';
    }

    public function updatedCalendarDepartmentId()
    {
        $this->dispatch('calendar-refresh');
    }

    public function updatedCalendarUserId()
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

        if ($this->calendarUserId) {
            $query->where('user_id', $this->calendarUserId);
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
        $this->suggestedSlots = [];
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

        // ¿Hay tareas ya programadas para ese trabajador dentro del rango?
        // No las movemos automáticamente (Gerencia decide), pero sí dejamos
        // constancia en el historial y avisamos para que se reprogramen.
        $clashing = TaskSchedule::where('user_id', $this->unavailUserId)
            ->whereIn('schedule_status', ['pendiente', 'en_proceso', 'pausada'])
            ->where('scheduled_start', '<', $this->unavailEnd)
            ->where('scheduled_end', '>', $this->unavailStart)
            ->with('task')
            ->get();

        foreach ($clashing as $sch) {
            TaskHistory::log(
                $sch->task_id,
                Auth::id(),
                'conflicto_indisponibilidad',
                null,
                $sch->scheduled_start->format('d/m H:i') . ' - ' . $sch->scheduled_end->format('H:i'),
                'Se registró una indisponibilidad del responsable en ese horario'
            );
        }

        $this->showUnavailabilityModal = false;

        if ($clashing->count() > 0) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Indisponibilidad registrada. Hay ' . $clashing->count() .
                    ' tarea(s) programada(s) en ese horario que debes reprogramar.',
            ]);
        } else {
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Indisponibilidad registrada.']);
        }
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
            'programadas' => Task::where('status', 'pendiente')->count(),
            'en_proceso' => Task::where('status', 'en_proceso')->count(),
            'pausadas' => Task::where('status', 'pausada')->count(),
            'bloqueadas' => Task::where('status', 'bloqueada')->count(),
            'terminadas_hoy' => Task::where('status', 'terminada')->whereDate('updated_at', now()->toDateString())->count(),
            'atrasadas' => $overdueTasks->count(),
            'sin_programar' => Task::where('status', 'sin_programar')->count(),
            'urgentes' => Task::where('priority', 'p1_urgente')->whereIn('status', Task::OPEN_STATUSES)->count(),
        ];

        // "¿Qué está haciendo cada persona?" (solo se calcula si la pestaña está activa)
        $currentActivity = [];
        if ($this->activeTab === 'actividad') {
            foreach ($assignableUsers as $user) {
                $active = TaskSchedule::where('user_id', $user->id)
                    ->whereHas('task', fn($q) => $q->whereIn('status', ['en_proceso', 'pausada']))
                    ->with('task.department')
                    ->orderBy('scheduled_start')
                    ->first();

                $next = null;
                if (!$active) {
                    $next = TaskSchedule::where('user_id', $user->id)
                        ->whereDate('scheduled_start', now()->toDateString())
                        ->whereHas('task', fn($q) => $q->where('status', 'pendiente'))
                        ->with('task.department')
                        ->orderBy('scheduled_start')
                        ->first();
                }

                $activeLog = $active
                    ? \App\Models\Tenant\TaskPlanner\TaskTimeLog::where('task_id', $active->task_id)
                        ->where('user_id', $user->id)
                        ->whereNull('finished_at')
                        ->latest('id')
                        ->first()
                    : null;

                $currentActivity[] = [
                    'user' => $user,
                    'active' => $active,
                    'active_started_at' => $activeLog?->started_at,
                    'next' => $next,
                ];
            }
        }

        $reports = $this->activeTab === 'reportes'
            ? $this->buildReports($assignableUsers, $departments)
            : null;

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
            'currentActivity' => $currentActivity,
            'reports' => $reports,
            'unavailabilities' => $unavailabilities,
            'projectsForOrigin' => $projectsForOrigin,
            'detailTask' => $this->detailTaskId ? Task::with(['department', 'assignments.user', 'comments.user', 'history.user', 'schedules', 'pauses.user', 'timeLogs.user', 'materials.item', 'checklists', 'attachments'])->find($this->detailTaskId) : null,
        ])->layout('layouts.app');
    }
}
