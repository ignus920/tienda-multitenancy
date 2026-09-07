<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskAssignment;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function createTask(array $data, array $assignedUserIds, $actingUserId): Task
    {
        return DB::connection('tenant')->transaction(function () use ($data, $assignedUserIds, $actingUserId) {
            $task = Task::create(array_merge($data, [
                'created_by' => $actingUserId,
                'status' => 'sin_programar',
            ]));

            foreach (array_unique($assignedUserIds) as $userId) {
                TaskAssignment::create(['task_id' => $task->id, 'user_id' => $userId]);
            }

            TaskHistory::log($task->id, $actingUserId, 'creada', null, $task->title);

            return $task;
        });
    }

    public function updateAssignedUsers(Task $task, array $assignedUserIds, $actingUserId): void
    {
        $current = $task->assignments()->pluck('user_id')->toArray();
        $new = array_unique($assignedUserIds);

        TaskAssignment::where('task_id', $task->id)->whereNotIn('user_id', $new)->delete();

        foreach ($new as $userId) {
            TaskAssignment::firstOrCreate(['task_id' => $task->id, 'user_id' => $userId]);
        }

        if ($current != $new) {
            TaskHistory::log($task->id, $actingUserId, 'responsables_actualizados', implode(',', $current), implode(',', $new));
        }
    }

    public function cancelTask(Task $task, $actingUserId, ?string $reason = null): void
    {
        $previousStatus = $task->status;
        $task->update(['status' => 'cancelada']);
        TaskHistory::log($task->id, $actingUserId, 'cancelada', $previousStatus, 'cancelada', $reason);
    }

    public function markOverdueTasks(): int
    {
        return Task::whereIn('status', Task::OPEN_STATUSES)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->update(['status' => 'vencida']);
    }
}
