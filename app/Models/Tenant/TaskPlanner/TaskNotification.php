<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Events\Tenant\TaskPlanner\NewTaskPlannerNotification;
use Illuminate\Support\Facades\Schema;

class TaskNotification extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_notifications';

    protected $fillable = [
        'user_id',
        'task_id',
        'type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Crea una notificación in-app para uno o varios usuarios.
     * Silenciosa si la tabla aún no existe (módulo a medio instalar).
     */
    public static function notify($userIds, ?int $taskId, string $type, string $message, ?int $exceptUserId = null): void
    {
        try {
            if (!Schema::connection('tenant')->hasTable('tsk_notifications')) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        foreach (array_unique((array) $userIds) as $uid) {
            $uid = (int) $uid;
            if (!$uid || $uid === $exceptUserId) {
                continue;
            }

            $row = static::create([
                'user_id' => $uid,
                'task_id' => $taskId,
                'type' => $type,
                'message' => $message,
            ]);

            // Aviso en tiempo real por WebSocket (Reverb). Si Reverb está caído
            // NO debe romper el guardado de la tarea.
            try {
                broadcast(new NewTaskPlannerNotification($uid, $row->id, $taskId, $type, $message));
            } catch (\Throwable $e) {
                // se ignora: la notificación queda en BD y aparece al recargar / poll
            }
        }
    }
}
