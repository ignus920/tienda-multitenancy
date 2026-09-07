<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;

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
