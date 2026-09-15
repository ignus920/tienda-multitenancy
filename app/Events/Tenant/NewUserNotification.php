<?php

namespace App\Events\Tenant;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso en tiempo real de una notificación genérica ("Tareas por hacer"),
 * disparada por UsrNotification::notify(). Viaja por el mismo canal privado
 * personal que ya usan Proyectos y el Planificador de Tareas (`user.{id}`,
 * autorizado en routes/channels.php) pero con un broadcastAs propio para no
 * mezclarse con esos otros eventos.
 */
class NewUserNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public ?int $notificationId = null,
        public string $module = '',
        public string $title = '',
        public string $message = '',
        public ?string $link = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewUserNotification';
    }

    public function broadcastWith(): array
    {
        return [
            'notificationId' => $this->notificationId,
            'module' => $this->module,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
        ];
    }
}
