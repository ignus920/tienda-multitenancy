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

    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function boot()
    {
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
        ])->layout('layouts.app');
    }
}
