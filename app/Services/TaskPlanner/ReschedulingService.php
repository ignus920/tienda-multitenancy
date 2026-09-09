<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeUnavailability;
use Carbon\Carbon;

/**
 * Arma PROPUESTAS de reorganización cuando algo desacomoda la agenda de un
 * trabajador (se insertó una urgencia, una tarea se alargó, se registró un
 * permiso...). No aplica nada por su cuenta: devuelve la propuesta para que
 * Gerencia la revise y confirme (spec puntos 14, 17, 18, 39).
 */
class ReschedulingService
{
    public function __construct(
        protected SchedulingService $schedulingService
    ) {}

    /**
     * @param  int|null  $keepTaskId  tarea que NO se debe mover (la urgencia recién insertada)
     * @return array<int, array> filas de propuesta; [] si no hay nada que mover.
     */
    public function proposeForUser(int $userId, Carbon $pivot, ?int $keepTaskId = null): array
    {
        $day = $pivot->copy()->startOfDay();

        $sched = EmployeeSchedule::where('user_id', $userId)
            ->where('day_of_week', $day->dayOfWeek)
            ->first();
        if (!$sched) {
            return [];
        }

        $dayEnd = $day->copy()->setTimeFromTimeString($sched->end_time);
        $dayStart = $day->copy()->setTimeFromTimeString($sched->start_time);

        // Bloques que NO se mueven: tareas ya iniciadas, indisponibilidades, almuerzo.
        $busy = [];

        $running = TaskSchedule::where('user_id', $userId)
            ->whereDate('scheduled_start', $day->toDateString())
            ->whereHas('task', fn($q) => $q->whereIn('status', ['en_proceso', 'pausada']))
            ->get();
        foreach ($running as $r) {
            $busy[] = [$r->scheduled_start->copy(), $r->scheduled_end->copy()];
        }

        // La urgencia recién insertada tampoco se mueve: es un bloque fijo.
        if ($keepTaskId) {
            $keep = TaskSchedule::where('user_id', $userId)
                ->where('task_id', $keepTaskId)
                ->whereDate('scheduled_start', $day->toDateString())
                ->first();
            if ($keep) {
                $busy[] = [$keep->scheduled_start->copy(), $keep->scheduled_end->copy()];
            }
        }

        $unav = EmployeeUnavailability::where('user_id', $userId)
            ->where('start_datetime', '<', $dayEnd)
            ->where('end_datetime', '>', $dayStart)
            ->get();
        foreach ($unav as $u) {
            $busy[] = [$u->start_datetime->copy(), $u->end_datetime->copy()];
        }

        if ($sched->break_start && $sched->break_end) {
            $busy[] = [
                $day->copy()->setTimeFromTimeString($sched->break_start),
                $day->copy()->setTimeFromTimeString($sched->break_end),
            ];
        }

        // Tareas movibles: pendientes de ese día que terminan después del pivote,
        // ordenadas por prioridad (P1 primero) y luego por su hora original.
        $movable = TaskSchedule::where('user_id', $userId)
            ->whereDate('scheduled_start', $day->toDateString())
            ->where('scheduled_end', '>', $pivot)
            ->when($keepTaskId, fn($q) => $q->where('task_id', '!=', $keepTaskId))
            ->whereHas('task', fn($q) => $q->where('status', 'pendiente'))
            ->with(['task.assignments'])
            ->get()
            ->sort(function ($a, $b) {
                $pa = Task::PRIORITY_WEIGHTS[$a->task->priority] ?? 9;
                $pb = Task::PRIORITY_WEIGHTS[$b->task->priority] ?? 9;
                return $pa <=> $pb ?: $a->scheduled_start <=> $b->scheduled_start;
            })
            ->values();

        if ($movable->isEmpty()) {
            return [];
        }

        $cursor = $pivot->gt($dayStart) ? $pivot->copy() : $dayStart->copy();
        $proposal = [];
        $anyChange = false;

        foreach ($movable as $m) {
            $duration = (int) $m->scheduled_start->diffInMinutes($m->scheduled_end);
            $slot = $this->nextFreeSlot($cursor, $duration, $busy, $dayEnd);

            $row = [
                'schedule_id' => $m->id,
                'task_id'     => $m->task_id,
                'title'       => $m->task->title,
                'priority'    => $m->task->priority_label,
                'old_label'   => $m->scheduled_start->format('d/m H:i') . ' - ' . $m->scheduled_end->format('H:i'),
            ];

            if (!$slot) {
                $row += ['overflow' => true, 'new_start' => null, 'new_end' => null,
                         'new_label' => 'No cabe hoy — requiere otro día', 'changed' => true,
                         'deadline_exceeded' => false];
                $anyChange = true;
                $proposal[] = $row;
                continue;
            }

            [$s, $e] = $slot;
            $changed = $s->format('Y-m-d H:i') !== $m->scheduled_start->format('Y-m-d H:i');
            $row += [
                'overflow'          => false,
                'new_start'         => $s->format('Y-m-d H:i:s'),
                'new_end'           => $e->format('Y-m-d H:i:s'),
                'new_label'         => $s->format('d/m H:i') . ' - ' . $e->format('H:i'),
                'changed'           => $changed,
                'deadline_exceeded' => $m->task->deadline_at ? $e->gt($m->task->deadline_at) : false,
            ];
            $anyChange = $anyChange || $changed;
            $busy[] = [$s, $e];
            $cursor = $e->copy();
            $proposal[] = $row;
        }

        return $anyChange ? $proposal : [];
    }

    /**
     * Primer hueco de $duration minutos a partir de $from que no choque con
     * ningún bloque de $busy y quepa antes de $dayEnd.
     */
    protected function nextFreeSlot(Carbon $from, int $duration, array $busy, Carbon $dayEnd): ?array
    {
        $start = $from->copy();

        for ($i = 0; $i < 300; $i++) {
            $end = $start->copy()->addMinutes($duration);
            if ($end->gt($dayEnd)) {
                return null;
            }

            $hit = null;
            foreach ($busy as [$bs, $be]) {
                if ($start->lt($be) && $end->gt($bs)) {
                    $hit = $be;
                    break;
                }
            }

            if (!$hit) {
                return [$start, $end];
            }
            $start = $hit->copy();
        }

        return null;
    }

    /**
     * Aplica las filas de la propuesta que tienen nueva hora.
     * @return int cuántas tareas se reprogramaron
     */
    public function apply(array $items, int $actingUserId): int
    {
        $count = 0;

        foreach ($items as $it) {
            if (empty($it['new_start']) || empty($it['new_end'])) {
                continue;
            }

            $schedule = TaskSchedule::with('task.assignments')->find($it['schedule_id']);
            if (!$schedule || !$schedule->task) {
                continue;
            }

            $userIds = $schedule->task->assignments->pluck('user_id')->toArray();

            $this->schedulingService->scheduleTask(
                $schedule->task,
                $userIds,
                Carbon::parse($it['new_start']),
                Carbon::parse($it['new_end']),
                $actingUserId,
                'reorganizacion_automatica'
            );
            $count++;
        }

        return $count;
    }
}
