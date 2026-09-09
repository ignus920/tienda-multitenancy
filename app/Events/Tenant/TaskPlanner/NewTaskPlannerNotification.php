<?php

namespace App\Events\Tenant\TaskPlanner;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso en tiempo real del Planificador Operativo (tsk_*).
 * Viaja por el mismo canal privado personal que ya usa Proyectos
 * (`user.{id}`, autorizado en routes/channels.php) pero con un
 * broadcastAs distinto para no mezclarse con NewProjectNotification.
 */
class NewTaskPlannerNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public ?int $notificationId = null,
        public ?int $taskId = null,
        public string $type = 'asignacion',
        public string $message = ''
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewTaskPlannerNotification';
    }

    public function broadcastWith(): array
    {
        return [
            'notificationId' => $this->notificationId,
            'taskId' => $this->taskId,
            'type' => $this->type,
            'message' => $this->message,
        ];
    }
}
