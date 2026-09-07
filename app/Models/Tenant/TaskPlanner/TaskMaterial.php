<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class TaskMaterial extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tsk_task_materials';

    protected $fillable = [
        'task_id',
        'item_id',
        'name',
        'estimated_quantity',
        'used_quantity',
        'unit_cost'
    ];

    /**
     * Get the task that owns the material.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Get the inventory item associated with the material (if applicable).
     */
    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
