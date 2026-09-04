<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TaskTimeLog extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_task_time_logs';

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'finished_at',
        'estimated_minutes',
        'real_minutes',
        'extra_time_requested_minutes',
        'extra_time_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'estimated_minutes' => 'integer',
        'real_minutes' => 'integer',
        'extra_time_requested_minutes' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getDifferenceMinutesAttribute()
    {
        if (is_null($this->real_minutes) || is_null($this->estimated_minutes)) {
            return null;
        }

        return $this->real_minutes - $this->estimated_minutes;
    }
}
