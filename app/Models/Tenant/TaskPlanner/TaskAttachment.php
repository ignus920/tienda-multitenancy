<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tsk_task_attachments';

    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by'
    ];

    /**
     * Get the task that owns the attachment.
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
