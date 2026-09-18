<?php

namespace App\Events\Tenant\Projects;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Señal técnica (sin mensaje de chat ni notificación en la campana): avisa
 * que la Lista de Materiales de un proyecto cambió, para que quien esté
 * viendo la Solicitud de Materiales en otra pestaña/sesión sepa que lo que
 * ve puede estar desactualizado antes de enviarla a Importaciones.
 */
class MaterialsListSynced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MaterialsListSynced';
    }
}
