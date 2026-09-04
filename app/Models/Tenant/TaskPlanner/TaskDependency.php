<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;

class TaskDependency extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_task_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function dependsOnTask()
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }
}
