<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Events\Tenant\NewUserNotification;
use Illuminate\Support\Facades\Schema;

/**
 * Notificaciones genéricas ("Tareas por hacer"), NO atadas a un solo módulo
 * (a diferencia de ProjectNotification o TaskNotification). Cualquier módulo
 * del sistema puede avisarle a un usuario que le toca continuar un proceso
 * llamando a UsrNotification::notify(...).
 */
class UsrNotification extends Model
{
    protected $connection = 'tenant';

    protected $table = 'usr_notifications';

    protected $fillable = [
        'user_id',
        'module',
        'reference_type',
        'reference_id',
        'title',
        'message',
        'link',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Crea una notificación in-app para uno o varios usuarios y la envía en
     * vivo por WebSocket. Silenciosa si la tabla aún no existe (empresa que
     * no ha corrido el SQL de este módulo todavía).
     */
    public static function notify(
        $userIds,
        string $module,
        ?string $referenceType,
        ?int $referenceId,
        string $title,
        string $message,
        ?string $link = null,
        ?int $exceptUserId = null
    ): void {
        try {
            if (!Schema::connection('tenant')->hasTable('usr_notifications')) {
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
                'module' => $module,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'created_by' => $exceptUserId,
            ]);

            // Aviso en tiempo real por WebSocket (Reverb). Si Reverb está caído
            // NO debe romper el flujo que generó la notificación.
            try {
                broadcast(new NewUserNotification($uid, $row->id, $module, $title, $message, $link));
            } catch (\Throwable $e) {
                // se ignora: la notificación queda en BD y aparece al recargar / poll
            }
        }
    }
}
