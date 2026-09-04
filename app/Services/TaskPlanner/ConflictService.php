<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeUnavailability;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use Carbon\Carbon;

class ConflictService
{
    /**
     * Revisa si un usuario puede ser programado en el rango [start, end].
     * Devuelve un arreglo de conflictos (vacío si no hay ninguno).
     */
    public function checkUserAvailability($userId, Carbon $start, Carbon $end, $ignoreScheduleId = null): array
    {
        $conflicts = [];

        $overlapping = TaskSchedule::where('user_id', $userId)
            ->whereIn('schedule_status', ['programada', 'en_proceso', 'pausada'])
            ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
            ->where('scheduled_start', '<', $end)
            ->where('scheduled_end', '>', $start)
            ->with('task')
            ->get();

        foreach ($overlapping as $schedule) {
            $conflicts[] = [
                'type' => 'horario_ocupado',
                'message' => 'Ya tiene asignada "' . ($schedule->task->title ?? 'una tarea') . '" de ' .
                    $schedule->scheduled_start->format('H:i') . ' a ' . $schedule->scheduled_end->format('H:i'),
                'schedule_id' => $schedule->id,
                'task_id' => $schedule->task_id,
            ];
        }

        $unavailable = EmployeeUnavailability::where('user_id', $userId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->get();

        foreach ($unavailable as $u) {
            $conflicts[] = [
                'type' => 'indisponibilidad',
                'message' => (EmployeeUnavailability::TYPES[$u->type] ?? $u->type) .
                    ' de ' . $u->start_datetime->format('d/m H:i') . ' a ' . $u->end_datetime->format('d/m H:i'),
                'unavailability_id' => $u->id,
            ];
        }

        $workingHoursConflict = $this->checkOutsideWorkingHours($userId, $start, $end);
        if ($workingHoursConflict) {
            $conflicts[] = $workingHoursConflict;
        }

        return $conflicts;
    }

    /**
     * Valida contra el horario laboral configurado del empleado para ese día.
     * Si el empleado no tiene horario configurado para ese día, no bloquea
     * (se asume que aún no se ha configurado su horario).
     */
    protected function checkOutsideWorkingHours($userId, Carbon $start, Carbon $end): ?array
    {
        if (!$start->isSameDay($end)) {
            return [
                'type' => 'horario_laboral',
                'message' => 'La tarea no puede cruzar la medianoche entre dos días.',
            ];
        }

        $daySchedule = EmployeeSchedule::where('user_id', $userId)
            ->where('day_of_week', $start->dayOfWeek)
            ->first();

        if (!$daySchedule) {
            return null;
        }

        $dayStart = $start->copy()->setTimeFromTimeString($daySchedule->start_time);
        $dayEnd = $start->copy()->setTimeFromTimeString($daySchedule->end_time);

        if ($start->lt($dayStart) || $end->gt($dayEnd)) {
            return [
                'type' => 'horario_laboral',
                'message' => 'Fuera del horario laboral (' . $daySchedule->start_time . ' - ' . $daySchedule->end_time . ')',
            ];
        }

        if ($daySchedule->break_start && $daySchedule->break_end) {
            $breakStart = $start->copy()->setTimeFromTimeString($daySchedule->break_start);
            $breakEnd = $start->copy()->setTimeFromTimeString($daySchedule->break_end);

            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                return [
                    'type' => 'horario_almuerzo',
                    'message' => 'Cruza el horario de almuerzo (' . $daySchedule->break_start . ' - ' . $daySchedule->break_end . ')',
                ];
            }
        }

        return null;
    }
}
