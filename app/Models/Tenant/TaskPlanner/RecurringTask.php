<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringTask extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tsk_recurring_tasks';

    protected $fillable = [
        'base_task_id',
        'cron_expression',
        'is_active',
        'next_run_at',
        'last_run_at'
    ];

    /**
     * `cron_expression` guarda un token simple: 'daily', 'weekly:{0-6}' o 'monthly:{1-28}'.
     * Devuelve la próxima fecha/hora de ejecución (siempre futura respecto a $from).
     */
    public static function nextRunFrom(?string $token, ?Carbon $from = null): Carbon
    {
        $from = ($from ?? Carbon::now())->copy();
        $at = fn(Carbon $d) => $d->startOfDay()->addHours(5); // 05:00

        if ($token === 'daily' || $token === null) {
            return $at($from->addDay());
        }

        if (str_starts_with($token, 'weekly:')) {
            $weekday = (int) explode(':', $token)[1];
            $d = $from->copy()->addDay();
            while ($d->dayOfWeek !== $weekday) {
                $d->addDay();
            }
            return $at($d);
        }

        if (str_starts_with($token, 'monthly:')) {
            $monthday = (int) explode(':', $token)[1];
            $d = $from->copy()->addMonthNoOverflow()->startOfMonth();
            $d->day(min(max($monthday, 1), $d->daysInMonth));
            return $at($d);
        }

        return $at($from->addDay());
    }

    public function frequencyLabel(): string
    {
        $t = $this->cron_expression;
        if ($t === 'daily') return 'Diaria';
        if (str_starts_with((string) $t, 'weekly:')) {
            $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
            return 'Semanal (' . ($dias[(int) explode(':', $t)[1]] ?? '') . ')';
        }
        if (str_starts_with((string) $t, 'monthly:')) {
            return 'Mensual (día ' . explode(':', $t)[1] . ')';
        }
        return $t ?? '—';
    }

    protected $casts = [
        'is_active' => 'boolean',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    /**
     * Get the base task template.
     */
    public function baseTask()
    {
        return $this->belongsTo(Task::class, 'base_task_id');
    }
}
