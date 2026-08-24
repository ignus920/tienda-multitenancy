<?php

namespace App\Events\Tenant\Projects;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewProjectNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $projectId;
    public $projectTitle;
    public $senderName;
    public $messagePreview;
    public $type;
    public $notificationId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $userId,
        int $projectId,
        string $projectTitle,
        string $senderName,
        string $messagePreview,
        string $type = 'mensaje',
        int $notificationId = 0
    ) {
        $this->userId = $userId;
        $this->projectId = $projectId;
        $this->projectTitle = $projectTitle;
        $this->senderName = $senderName;
        $this->messagePreview = $messagePreview;
        $this->type = $type;
        $this->notificationId = $notificationId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'NewProjectNotification';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notificationId' => $this->notificationId,
            'projectId' => $this->projectId,
            'projectTitle' => $this->projectTitle,
            'senderName' => $this->senderName,
            'messagePreview' => $this->messagePreview,
            'type' => $this->type,
        ];
    }
}
