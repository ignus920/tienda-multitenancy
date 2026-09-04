<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskTimeLog;
use App\Models\Tenant\TaskPlanner\TaskPause;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use Illuminate\Support\Facades\DB;
use Exception;

class TimeTrackingService
{
    public function start(Task $task, $userId): TaskTimeLog
    {
        if ($task->has_pending_dependencies) {
            throw new Exception('Esta tarea depende de otra que aún no ha sido terminada.');
        }

        return DB::connection('tenant')->transaction(function () use ($task, $userId) {
            $previousStatus = $task->status;

            $log = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'started_at' => now(),
                'estimated_minutes' => $task->estimated_minutes,
            ]);

            $task->update(['status' => 'en_proceso']);

            TaskSchedule::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->update(['schedule_status' => 'en_proceso']);

            TaskHistory::log($task->id, $userId, 'iniciada', $previousStatus, 'en_proceso');

            return $log;
        });
    }

    public function pause(Task $task, $userId, string $reason, ?string $observation = null): TaskPause
    {
        return DB::connection('tenant')->transaction(function () use ($task, $userId, $reason, $observation) {
            $pause = TaskPause::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'started_at' => now(),
                'reason' => $reason,
                'observation' => $observation,
            ]);

            $task->update(['status' => 'pausada']);

            TaskSchedule::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->update(['schedule_status' => 'pausada']);

            TaskHistory::log($task->id, $userId, 'pausada', 'en_proceso', 'pausada', $reason);

            return $pause;
        });
    }

    public function resume(Task $task, $userId): void
    {
        DB::connection('tenant')->transaction(function () use ($task, $userId) {
            $pause = TaskPause::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->whereNull('ended_at')
                ->latest('id')
                ->first();

            if ($pause) {
                $pause->update(['ended_at' => now()]);
            }

            $task->update(['status' => 'en_proceso']);

            TaskSchedule::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->update(['schedule_status' => 'en_proceso']);

            TaskHistory::log($task->id, $userId, 'reanudada', 'pausada', 'en_proceso');
        });
    }

    public function finish(Task $task, $userId, ?string $note = null): TaskTimeLog
    {
        return DB::connection('tenant')->transaction(function () use ($task, $userId, $note) {
            $openPause = TaskPause::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->whereNull('ended_at')
                ->latest('id')
                ->first();

            if ($openPause) {
                $openPause->update(['ended_at' => now()]);
            }

            $log = TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->whereNull('finished_at')
                ->latest('id')
                ->first();

            $finishedAt = now();
            $previousStatus = $task->status;

            if ($log) {
                $pausedMinutes = TaskPause::where('task_id', $task->id)
                    ->where('user_id', $userId)
                    ->where('started_at', '>=', $log->started_at)
                    ->get()
                    ->sum(function ($p) use ($finishedAt) {
                        return $p->started_at->diffInMinutes($p->ended_at ?? $finishedAt);
                    });

                $realMinutes = max($log->started_at->diffInMinutes($finishedAt) - $pausedMinutes, 0);

                $log->update([
                    'finished_at' => $finishedAt,
                    'real_minutes' => $realMinutes,
                ]);
            } else {
                $log = TaskTimeLog::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'started_at' => $finishedAt,
                    'finished_at' => $finishedAt,
                    'estimated_minutes' => $task->estimated_minutes,
                    'real_minutes' => 0,
                ]);
            }

            $task->update(['status' => 'terminada']);

            TaskSchedule::where('task_id', $task->id)
                ->where('user_id', $userId)
                ->update(['schedule_status' => 'terminada']);

            TaskHistory::log($task->id, $userId, 'terminada', $previousStatus, 'terminada', $note);

            $this->unblockDependentTasks($task);

            return $log;
        });
    }

    public function requestMoreTime(Task $task, $userId, int $extraMinutes, ?string $reason = null): array
    {
        $log = TaskTimeLog::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->whereNull('finished_at')
            ->latest('id')
            ->first();

        if ($log) {
            $log->update([
                'extra_time_requested_minutes' => $extraMinutes,
                'extra_time_reason' => $reason,
            ]);
        }

        TaskHistory::log($task->id, $userId, 'solicito_mas_tiempo', null, $extraMinutes . ' min', $reason);

        $schedule = TaskSchedule::where('task_id', $task->id)->where('user_id', $userId)->first();
        $affected = [];

        if ($schedule) {
            $affected = TaskSchedule::where('user_id', $userId)
                ->where('id', '!=', $schedule->id)
                ->whereIn('schedule_status', ['programada'])
                ->whereDate('scheduled_start', $schedule->scheduled_start->toDateString())
                ->where('scheduled_start', '>=', $schedule->scheduled_end)
                ->where('scheduled_start', '<', $schedule->scheduled_end->copy()->addMinutes($extraMinutes))
                ->with('task')
                ->get()
                ->toArray();
        }

        return $affected;
    }

    public function block(Task $task, $userId, string $reason): void
    {
        $previousStatus = $task->status;
        $task->update(['status' => 'bloqueada', 'blocked_reason' => $reason]);
        TaskHistory::log($task->id, $userId, 'bloqueada', $previousStatus, 'bloqueada', $reason);
    }

    public function unblock(Task $task, $userId): void
    {
        $newStatus = $task->currentSchedule ? 'programada' : 'sin_programar';
        $task->update(['status' => $newStatus, 'blocked_reason' => null]);
        TaskHistory::log($task->id, $userId, 'desbloqueada', 'bloqueada', $newStatus);
    }

    protected function unblockDependentTasks(Task $task): void
    {
        $dependentTaskIds = $task->dependentTasks()->pluck('task_id');

        foreach ($dependentTaskIds as $taskId) {
            $dependent = Task::find($taskId);
            if ($dependent && $dependent->status === 'bloqueada' && !$dependent->has_pending_dependencies) {
                $newStatus = $dependent->currentSchedule ? 'programada' : 'sin_programar';
                $dependent->update(['status' => $newStatus, 'blocked_reason' => null]);
                TaskHistory::log($dependent->id, null, 'desbloqueada_automaticamente', 'bloqueada', $newStatus, 'Se terminó la tarea de la cual dependía');
            }
        }
    }
}
