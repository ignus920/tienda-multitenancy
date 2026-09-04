<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TaskPause extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_task_pauses';

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'reason',
        'observation',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    const REASONS = [
        'falta_material' => 'Falta material',
        'esperando_cliente' => 'Esperando cliente',
        'esperando_autorizacion' => 'Esperando autorización',
        'falta_herramienta' => 'Falta herramienta',
        'tarea_urgente' => 'Se presentó tarea urgente',
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
