<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Projects\Project;

class Task extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_tasks';

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'priority',
        'status',
        'estimated_minutes',
        'deadline_at',
        'suggested_date',
        'location_type',
        'location',
        'travel_minutes_before',
        'travel_minutes_after',
        'origin_type',
        'origin_project_id',
        'blocked_reason',
        'created_by',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'suggested_date' => 'date',
        'estimated_minutes' => 'integer',
        'travel_minutes_before' => 'integer',
        'travel_minutes_after' => 'integer',
    ];

    const PRIORITIES = [
        'p1_urgente' => 'P1 - Urgente',
        'p2_alta' => 'P2 - Alta',
        'p3_normal' => 'P3 - Normal',
        'p4_baja' => 'P4 - Baja',
    ];

    const PRIORITY_WEIGHTS = [
        'p1_urgente' => 1,
        'p2_alta' => 2,
        'p3_normal' => 3,
        'p4_baja' => 4,
    ];

    const PRIORITY_COLORS = [
        'p1_urgente' => 'red',
        'p2_alta' => 'orange',
        'p3_normal' => 'blue',
        'p4_baja' => 'gray',
    ];

    const STATUSES = [
        'sin_programar' => 'Sin programar',
        'programada' => 'Programada',
        'pendiente' => 'Pendiente',
        'disponible' => 'Disponible',
        'en_proceso' => 'En proceso',
        'pausada' => 'Pausada',
        'bloqueada' => 'Bloqueada',
        'terminada' => 'Terminada',
        'vencida' => 'Vencida',
        'reprogramada' => 'Reprogramada',
        'cancelada' => 'Cancelada',
    ];

    const OPEN_STATUSES = ['sin_programar', 'programada', 'pendiente', 'disponible', 'en_proceso', 'pausada', 'bloqueada', 'reprogramada'];

    const ORIGIN_TYPES = [
        'cliente' => 'Cliente',
        'pedido' => 'Pedido',
        'proyecto' => 'Proyecto',
        'mantenimiento' => 'Mantenimiento',
        'instalacion' => 'Instalación',
        'exhibicion' => 'Exhibición',
        'adecuacion_interna' => 'Adecuación interna',
        'laboratorio' => 'Laboratorio',
        'gerencia' => 'Gerencia',
        'otro' => 'Otro',
    ];

    public function department()
    {
        return $this->belongsTo(TaskDepartment::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function originProject()
    {
        return $this->belongsTo(Project::class, 'origin_project_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    public function assignedUsers()
    {
        return User::whereIn('id', $this->assignments()->pluck('user_id'))->get();
    }

    public function schedules()
    {
        return $this->hasMany(TaskSchedule::class, 'task_id')->orderBy('scheduled_start');
    }

    public function currentSchedule()
    {
        return $this->hasOne(TaskSchedule::class, 'task_id')
            ->whereIn('schedule_status', ['programada', 'en_proceso', 'pausada'])
            ->orderBy('scheduled_start');
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependentTasks()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function activeTimeLog()
    {
        return $this->hasOne(TaskTimeLog::class, 'task_id')->whereNull('finished_at')->latestOfMany();
    }

    public function pauses()
    {
        return $this->hasMany(TaskPause::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function activePause()
    {
        return $this->hasOne(TaskPause::class, 'task_id')->whereNull('ended_at')->latestOfMany();
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderBy('created_at', 'asc');
    }

    public function history()
    {
        return $this->hasMany(TaskHistory::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function getPriorityLabelAttribute()
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute()
    {
        return self::PRIORITY_COLORS[$this->priority] ?? 'gray';
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->deadline_at || in_array($this->status, ['terminada', 'cancelada'])) {
            return false;
        }

        return now()->greaterThan($this->deadline_at);
    }

    public function getTotalOccupiedMinutesAttribute()
    {
        return $this->estimated_minutes + $this->travel_minutes_before + $this->travel_minutes_after;
    }

    public function getHasPendingDependenciesAttribute()
    {
        if ($this->dependencies->isEmpty()) {
            return false;
        }

        return $this->dependencies->contains(function ($dependency) {
            return $dependency->dependsOnTask && $dependency->dependsOnTask->status !== 'terminada';
        });
    }
}
