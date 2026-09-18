<?php

namespace App\Services\TaskPlanner;

use App\Models\Tenant\TaskPlanner\EmployeeSchedule;
use App\Models\Tenant\TaskPlanner\EmployeeUnavailability;
use Carbon\Carbon;

/**
 * Construye los "eventos de fondo" de FullCalendar (no interactivos, mismo
 * color) que marcan las franjas en las que un trabajador NO está disponible:
 * indisponibilidades registradas (vacaciones, incapacidad, permiso, etc.),
 * horas fuera de su jornada laboral (antes/después de turno, almuerzo) y
 * días completos sin horario configurado (no trabaja ese día).
 *
 * Usado tanto por el calendario de gerencia (cuando filtra por un trabajador
 * específico) como por el calendario propio del trabajador.
 */
class AvailabilityCalendarService
{
    protected const UNAVAILABLE_COLOR = '#ef4444';

    public function backgroundEventsForUser(int $userId, $start, $end): array
    {
        $rangeStart = Carbon::parse($start);
        $rangeEnd = Carbon::parse($end);
        $events = [];

        $unavailabilities = EmployeeUnavailability::where('user_id', $userId)
            ->where('start_datetime', '<', $rangeEnd)
            ->where('end_datetime', '>', $rangeStart)
            ->get();

        foreach ($unavailabilities as $unavailability) {
            $events[] = [
                'start' => $unavailability->start_datetime->toIso8601String(),
                'end' => $unavailability->end_datetime->toIso8601String(),
                'display' => 'background',
                'backgroundColor' => self::UNAVAILABLE_COLOR,
                'extendedProps' => [
                    'type' => 'unavailability',
                    'reason' => $unavailability->reason,
                ],
            ];
        }

        $schedulesByDay = EmployeeSchedule::where('user_id', $userId)
            ->get()
            ->keyBy('day_of_week');

        $cursor = $rangeStart->copy()->startOfDay();
        while ($cursor->lt($rangeEnd)) {
            $dayStart = $cursor->copy();
            $dayEnd = $cursor->copy()->addDay();
            $schedule = $schedulesByDay->get((int) $cursor->dayOfWeek);

            if (!$schedule) {
                $events[] = [
                    'start' => $dayStart->toIso8601String(),
                    'end' => $dayEnd->toIso8601String(),
                    'display' => 'background',
                    'backgroundColor' => self::UNAVAILABLE_COLOR,
                    'extendedProps' => ['type' => 'non_working_day'],
                ];
            } else {
                $shiftStart = $cursor->copy()->setTimeFromTimeString($schedule->start_time);
                $shiftEnd = $cursor->copy()->setTimeFromTimeString($schedule->end_time);

                if ($shiftStart->gt($dayStart)) {
                    $events[] = [
                        'start' => $dayStart->toIso8601String(),
                        'end' => $shiftStart->toIso8601String(),
                        'display' => 'background',
                        'backgroundColor' => self::UNAVAILABLE_COLOR,
                        'extendedProps' => ['type' => 'outside_hours'],
                    ];
                }

                if ($shiftEnd->lt($dayEnd)) {
                    $events[] = [
                        'start' => $shiftEnd->toIso8601String(),
                        'end' => $dayEnd->toIso8601String(),
                        'display' => 'background',
                        'backgroundColor' => self::UNAVAILABLE_COLOR,
                        'extendedProps' => ['type' => 'outside_hours'],
                    ];
                }

                if ($schedule->break_start && $schedule->break_end) {
                    $events[] = [
                        'start' => $cursor->copy()->setTimeFromTimeString($schedule->break_start)->toIso8601String(),
                        'end' => $cursor->copy()->setTimeFromTimeString($schedule->break_end)->toIso8601String(),
                        'display' => 'background',
                        'backgroundColor' => self::UNAVAILABLE_COLOR,
                        'extendedProps' => ['type' => 'break'],
                    ];
                }
            }

            $cursor->addDay();
        }

        return $events;
    }
}
