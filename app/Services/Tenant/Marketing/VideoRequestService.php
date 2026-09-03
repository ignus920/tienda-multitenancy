<?php

namespace App\Services\Tenant\Marketing;

use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Marketing\VideoRequest;
use App\Models\Tenant\Marketing\VideoRequestLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VideoRequestService
{
    public function __construct(private TechnicalSpecsVideoSync $youtubeSync)
    {
    }

    /**
     * Crea una solicitud de video y su lista de chequeo (5 actividades).
     */
    public function create(int $itemId, ?string $instructions, ?int $gestorId = null): VideoRequest
    {
        $item = Items::on('tenant')->findOrFail($itemId);

        return DB::connection('tenant')->transaction(function () use ($item, $instructions, $gestorId) {
            $request = VideoRequest::create([
                'request_number'  => $this->nextRequestNumber(),
                'item_id'         => $item->id,
                'product_code'    => $item->internal_code ?: $item->sku,
                'product_name'    => $item->name,
                'requested_by'    => Auth::id(),
                'gestor_id'       => $gestorId,
                'instructions'    => $instructions,
                'status'          => 'pendiente',
                'progress_done'   => 0,
                'progress_total'  => count(VideoRequest::CHANNELS),
                'progress_percent' => 0,
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
            ]);

            foreach (VideoRequest::CHANNELS as $channel => $cfg) {
                $request->tasks()->create([
                    'channel'    => $channel,
                    'status'     => 'pendiente',
                    'sort_order' => $cfg['order'],
                ]);
            }

            $this->log($request, 'creada', null, null, "Solicitud {$request->request_number} · {$item->name}");

            return $request;
        });
    }

    /**
     * Guarda cambios de la lista de chequeo (guardado parcial permitido).
     *
     * @param array<string,array{status?:string,link?:string|null}> $taskInput  clave = canal
     */
    public function saveTasks(VideoRequest $request, array $taskInput, ?int $gestorId = null): VideoRequest
    {
        return DB::connection('tenant')->transaction(function () use ($request, $taskInput, $gestorId) {
            if ($gestorId !== null && (int) $gestorId !== (int) $request->gestor_id) {
                $request->gestor_id = $gestorId ?: null;
            }

            foreach ($request->tasks as $task) {
                $input = $taskInput[$task->channel] ?? [];
                $requiresLink = $task->requiresLink();

                $newLink = $requiresLink ? (isset($input['link']) ? trim((string) $input['link']) : $task->link) : null;
                $newLink = $newLink === '' ? null : $newLink;

                // Estado: para canales con enlace lo decide el enlace válido;
                // el canal "celular" se marca manualmente.
                if ($requiresLink) {
                    $manualEnProceso = ($input['status'] ?? null) === 'en_proceso';
                    $newStatus = VideoRequest::linkMatchesChannel($task->channel, $newLink)
                        ? 'listo'
                        : ($manualEnProceso ? 'en_proceso' : 'pendiente');
                } else {
                    $newStatus = in_array($input['status'] ?? null, ['pendiente', 'en_proceso', 'listo'], true)
                        ? $input['status']
                        : $task->status;
                }

                $linkChanged = $newLink !== $task->link;
                $statusChanged = $newStatus !== $task->status;

                if ($linkChanged) {
                    $this->log($request, 'enlace_actualizado', $task->channel, $task->link, $newLink);
                    $task->link = $newLink;
                }

                if ($statusChanged) {
                    $this->log($request, 'tarea_actualizada', $task->channel, $task->status, $newStatus);
                    $task->status = $newStatus;
                }

                if ($newStatus === 'listo' && ($statusChanged || $task->completed_at === null)) {
                    $task->completed_at = now();
                    $task->completed_by = Auth::id();
                } elseif ($newStatus !== 'listo') {
                    $task->completed_at = null;
                    $task->completed_by = null;
                }

                if ($task->isDirty()) {
                    $task->save();
                }
            }

            $request->refresh()->load('tasks');
            $this->applyYoutubeUrl($request);
            $this->recalculate($request);

            $request->updated_by = Auth::id();
            $request->save();

            $this->youtubeSync->sync($request->fresh());

            return $request->fresh(['tasks', 'logs']);
        });
    }

    /**
     * Recalcula avance y estado general en base a las actividades.
     */
    public function recalculate(VideoRequest $request): void
    {
        $tasks = $request->tasks;
        $total = max(1, $tasks->count());
        $done = $tasks->where('status', 'listo')->count();

        $percent = (int) round($done / $total * 100);
        $status = $done === 0 ? 'pendiente' : ($done >= $total ? 'terminado' : 'en_proceso');

        $changed = $request->progress_done !== $done
            || $request->progress_percent !== $percent
            || $request->status !== $status;

        $request->progress_done = $done;
        $request->progress_total = $total;
        $request->progress_percent = $percent;
        $request->status = $status;

        if ($changed) {
            $this->log($request, 'estado_recalculado', null, null, "{$done}/{$total} · {$percent}% · {$status}");
        }
    }

    /**
     * Toma el enlace de la actividad YouTube como enlace vigente de la solicitud.
     */
    private function applyYoutubeUrl(VideoRequest $request): void
    {
        $youtubeTask = $request->tasks->firstWhere('channel', 'youtube');
        $request->youtube_url = $youtubeTask?->link ?: null;
    }

    private function nextRequestNumber(): string
    {
        $lastId = (int) VideoRequest::withTrashed()->max('id');

        return 'SV-' . str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
    }

    public function log(VideoRequest $request, string $action, ?string $channel, ?string $old, ?string $new): void
    {
        VideoRequestLog::create([
            'video_request_id' => $request->id,
            'user_id'          => Auth::id(),
            'action'           => $action,
            'channel'          => $channel,
            'old_value'        => $old,
            'new_value'        => $new,
            'created_at'       => now(),
        ]);
    }
}
