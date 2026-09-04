<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TaskSchedule extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_task_schedules';

    protected $fillable = [
        'task_id',
        'user_id',
        'scheduled_start',
        'scheduled_end',
        'schedule_status',
        'reschedule_reason',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
    ];

    const RESCHEDULE_REASONS = [
        'urgencia_cliente' => 'Urgencia cliente',
        'falta_material' => 'Falta de material',
        'ausencia_trabajador' => 'Ausencia trabajador',
        'actividad_anterior_tomo_mas_tiempo' => 'Actividad anterior tomó más tiempo',
        'cambio_solicitado_cliente' => 'Cambio solicitado por cliente',
        'prioridad_gerencia' => 'Prioridad de Gerencia',
        'problema_tecnico' => 'Problema técnico',
        'otro' => 'Otro',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
