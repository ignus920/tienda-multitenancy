<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskAssignment;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use App\Models\Tenant\TaskPlanner\TaskNotification;
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

            // No se notifica al crear: la tarea nace "sin programar" y todavía
            // no es accionable para el trabajador (no aparece en "Mis Tareas de Hoy").
            // El aviso se envía cuando la tarea se programa (SchedulingService).

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

            // Avisar al nuevo responsable SOLO si la tarea ya está en el calendario.
            // Si sigue "sin programar", se enterará cuando se programe.
            if (!in_array($task->status, ['sin_programar', 'terminada', 'cancelada'])) {
                $added = array_values(array_diff($new, $current));
                $when = optional($task->currentSchedule)->scheduled_start;
                $suffix = $when ? ' — ' . $when->format('d/m H:i') : '';

                TaskNotification::notify(
                    $added,
                    $task->id,
                    'asignacion',
                    'Se te asignó una tarea: ' . $task->title . $suffix,
                    $actingUserId
                );
            }
        }
    }

    public function cancelTask(Task $task, $actingUserId, ?string $reason = null): void
    {
        $previousStatus = $task->status;
        $assigned = $task->assignments()->pluck('user_id')->toArray();

        $task->update(['status' => 'cancelada']);
        TaskHistory::log($task->id, $actingUserId, 'cancelada', $previousStatus, 'cancelada', $reason);

        TaskNotification::notify($assigned, $task->id, 'cancelacion', 'Se canceló una tarea: ' . $task->title, $actingUserId);
    }

    /**
     * Marca como "vencida" solo las tareas que aún no se han empezado.
     * Una tarea "en_proceso", "pausada" o "bloqueada" NO se toca: seguir
     * ejecutándola es más importante que reflejar el atraso en el estado.
     * El atraso de esas se calcula en caliente (ver Task::is_overdue y la
     * consulta de "Atrasadas" en ManageTasks::render).
     */
    public function markOverdueTasks(): int
    {
        return Task::whereIn('status', ['sin_programar', 'pendiente'])
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->update(['status' => 'vencida']);
    }
}
