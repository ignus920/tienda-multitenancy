<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;

class TaskDepartment extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_departments';

    protected $fillable = [
        'name',
        'description',
        'color',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'department_id');
    }
}
