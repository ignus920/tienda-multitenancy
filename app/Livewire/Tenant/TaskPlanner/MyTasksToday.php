<?php

namespace App\Livewire\Tenant\TaskPlanner;

use Livewire\Component;
use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskComment;
use App\Models\Tenant\TaskPlanner\TaskDepartment;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\TaskPlanner\TimeTrackingService;
use Illuminate\Support\Facades\Auth;
use Exception;

class MyTasksToday extends Component
{
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

    public function boot()
    {
        $this->ensureTenantConnection();
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

        $service->finish(Task::findOrFail($this->finishingTaskId), Auth::id(), $this->finishNote);

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

    public function render()
    {
        $this->ensureTenantConnection();
        $userId = Auth::id();
        $today = now();

        $todaySchedules = TaskSchedule::where('user_id', $userId)
            ->whereDate('scheduled_start', $today->toDateString())
            ->whereNotIn('schedule_status', ['cancelada'])
            ->with(['task.department', 'task.comments.user', 'task.pauses'])
            ->orderBy('scheduled_start')
            ->get();

        $daySchedule = EmployeeSchedule::where('user_id', $userId)
            ->where('day_of_week', $today->dayOfWeek)
            ->first();

        $scheduledMinutes = $todaySchedules->sum(function ($s) {
            return $s->scheduled_start->diffInMinutes($s->scheduled_end);
        });

        $currentSchedule = $todaySchedules->first(function ($s) use ($today) {
            return $s->task->status === 'en_proceso' || $s->task->status === 'pausada';
        }) ?? $todaySchedules->first(function ($s) use ($today) {
            return in_array($s->task->status, ['programada', 'disponible']) && $s->scheduled_start->lte($today);
        }) ?? $todaySchedules->first(function ($s) {
            return in_array($s->task->status, ['programada', 'disponible']);
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

        return view('livewire.tenant.task-planner.my-tasks-today', [
            'today' => $today,
            'daySchedule' => $daySchedule,
            'todaySchedules' => $todaySchedules,
            'scheduledMinutes' => $scheduledMinutes,
            'currentSchedule' => $currentSchedule,
            'upcomingSchedules' => $upcomingSchedules,
            'fillerTasks' => $fillerTasks,
            'pauseReasons' => \App\Models\Tenant\TaskPlanner\TaskPause::REASONS,
        ])->layout('layouts.app', ['header' => 'Mis Tareas de Hoy']);
    }
}
