<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TaskHistory extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'tsk_task_history';

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'reason',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log($taskId, $userId, $action, $oldValue = null, $newValue = null, $reason = null)
    {
        return static::create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
        ]);
    }
}
