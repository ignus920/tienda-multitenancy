<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use Carbon\Carbon;

class SchedulingService
{
    public function __construct(protected ConflictService $conflictService)
    {
    }

    /**
     * Revisa disponibilidad de todos los usuarios asignados para el rango dado.
     * Devuelve ['user_id' => [conflictos...]] solo para los que sí tienen conflicto.
     */
    public function checkAvailabilityForUsers(array $userIds, Carbon $start, Carbon $end, $ignoreTaskId = null): array
    {
        $conflicts = [];

        foreach ($userIds as $userId) {
            $ignoreScheduleId = null;
            if ($ignoreTaskId) {
                $existing = TaskSchedule::where('task_id', $ignoreTaskId)->where('user_id', $userId)->first();
                $ignoreScheduleId = $existing?->id;
            }

            $userConflicts = $this->conflictService->checkUserAvailability($userId, $start, $end, $ignoreScheduleId);
            if (!empty($userConflicts)) {
                $conflicts[$userId] = $userConflicts;
            }
        }

        return $conflicts;
    }

    /**
     * Programa (o reprograma) una tarea para uno o varios usuarios en el mismo bloque de tiempo.
     * No valida conflictos: eso se hace antes con checkAvailabilityForUsers para poder
     * mostrarle la opción de confirmar igualmente al administrador.
     */
    public function scheduleTask(Task $task, array $userIds, Carbon $start, Carbon $end, $actingUserId, $rescheduleReason = null): void
    {
        $wasScheduled = $task->status !== 'sin_programar';

        foreach ($userIds as $userId) {
            TaskSchedule::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $userId],
                [
                    'scheduled_start' => $start,
                    'scheduled_end' => $end,
                    'schedule_status' => 'programada',
                    'reschedule_reason' => $wasScheduled ? $rescheduleReason : null,
                ]
            );
        }

        // Elimina programaciones de usuarios que ya no están asignados a la tarea
        TaskSchedule::where('task_id', $task->id)
            ->whereNotIn('user_id', $userIds)
            ->delete();

        $newStatus = $task->has_pending_dependencies ? 'bloqueada' : ($start->isFuture() ? 'programada' : 'disponible');

        TaskHistory::log(
            $task->id,
            $actingUserId,
            $wasScheduled ? 'reprogramada' : 'programada',
            $wasScheduled ? $task->status : null,
            $start->format('d/m/Y H:i') . ' - ' . $end->format('H:i'),
            $rescheduleReason
        );

        $task->update(['status' => $newStatus]);
    }

    /**
     * Quita una tarea del calendario y la regresa a la bandeja de "sin programar".
     */
    public function unscheduleTask(Task $task, $actingUserId, $reason = null): void
    {
        TaskSchedule::where('task_id', $task->id)->delete();
        TaskHistory::log($task->id, $actingUserId, 'quitada_del_calendario', $task->status, 'sin_programar', $reason);
        $task->update(['status' => 'sin_programar']);
    }

    /**
     * Busca los primeros huecos libres (dentro del horario laboral configurado)
     * con al menos $durationMinutes de duración, para cada usuario dado.
     * Escaneo simple día a día hasta $maxDays.
     */
    public function findAvailableSlots(array $userIds, int $durationMinutes, ?Carbon $deadline = null, int $maxDays = 10, int $maxResults = 3): array
    {
        $results = [];
        $day = Carbon::now()->startOfDay();

        for ($i = 0; $i < $maxDays && count($results) < $maxResults; $i++) {
            $day = $day->copy()->addDay();

            if ($deadline && $day->gt($deadline)) {
                break;
            }

            $daySchedules = EmployeeSchedule::whereIn('user_id', $userIds)
                ->where('day_of_week', $day->dayOfWeek)
                ->get()
                ->keyBy('user_id');

            // Todos los usuarios deben tener horario configurado y coincidir para trabajo en equipo
            if ($daySchedules->count() < count($userIds)) {
                continue;
            }

            // Intersección del horario laboral de todos los usuarios (para tareas en equipo)
            $windowStart = null;
            $windowEnd = null;
            foreach ($daySchedules as $sched) {
                $s = $day->copy()->setTimeFromTimeString($sched->start_time);
                $e = $day->copy()->setTimeFromTimeString($sched->end_time);
                $windowStart = $windowStart === null ? $s : $windowStart->max($s);
                $windowEnd = $windowEnd === null ? $e : $windowEnd->min($e);
            }

            if (!$windowStart || !$windowEnd || $windowStart->gte($windowEnd)) {
                continue;
            }

            $slotStart = $windowStart->copy();
            while ($slotStart->copy()->addMinutes($durationMinutes)->lte($windowEnd)) {
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                $conflicts = $this->checkAvailabilityForUsers($userIds, $slotStart, $slotEnd);
                if (empty($conflicts)) {
                    $results[] = ['start' => $slotStart->copy(), 'end' => $slotEnd->copy()];
                    if (count($results) >= $maxResults) {
                        break;
                    }
                }

                $slotStart->addMinutes(30);
            }
        }

        return $results;
    }
}
