<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tsk_task_checklists';

    protected $fillable = [
        'task_id',
        'description',
        'is_completed',
        'completed_at'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the task that owns the checklist item.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
