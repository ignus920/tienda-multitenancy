<?php

namespace App\Services\Tenant\Marketing;

use App\Models\Tenant\Items\ItemObservation;
use App\Models\Tenant\Marketing\VideoRequest;
use App\Models\Tenant\Marketing\VideoRequestLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza el enlace de YouTube de una solicitud dentro del campo
 * "Observaciones técnicas" (inv_item_observations.technical_specifications).
 *
 * Regla: un único bloque encabezado por "Videos del producto:" al inicio del
 * texto, con una URL por línea, separado del resto por una línea en blanco.
 * Nunca se borra ni se reemplaza el texto libre existente.
 */
class TechnicalSpecsVideoSync
{
    private const HEADER = 'Videos del producto:';

    public function sync(VideoRequest $request): void
    {
        try {
            $newUrl = trim((string) $request->youtube_url);
            $oldUrl = trim((string) $request->youtube_synced_url);

            if ($newUrl === '' && $oldUrl === '') {
                return;
            }

            if ($newUrl !== '' && ! VideoRequest::linkMatchesChannel('youtube', $newUrl)) {
                return; // enlace inválido: no se toca la ficha
            }

            $observation = ItemObservation::on('tenant')->firstOrNew(['item_id' => $request->item_id]);
            $currentText = (string) ($observation->technical_specifications ?? '');

            [$urls, $freeText] = $this->splitBlock($currentText);

            if ($newUrl === '') {
                $urls = array_values(array_filter($urls, fn ($u) => $u !== $oldUrl));
            } elseif ($oldUrl !== '' && in_array($oldUrl, $urls, true)) {
                $urls = array_map(fn ($u) => $u === $oldUrl ? $newUrl : $u, $urls);
            } elseif (! in_array($newUrl, $urls, true)) {
                $urls[] = $newUrl;
            }

            $urls = array_values(array_unique(array_filter(array_map('trim', $urls))));

            $rebuilt = $this->rebuild($urls, $freeText);

            if ($rebuilt === $currentText && $request->youtube_synced_url === ($newUrl ?: null)) {
                return;
            }

            $observation->technical_specifications = $rebuilt;
            if (! $observation->exists) {
                $observation->status = 1;
            }
            $observation->save();

            $request->forceFill([
                'youtube_synced_url' => $newUrl !== '' ? $newUrl : null,
                'youtube_synced_at'  => now(),
            ])->saveQuietly();

            VideoRequestLog::create([
                'video_request_id' => $request->id,
                'user_id'          => Auth::id(),
                'action'           => 'sync_youtube',
                'channel'          => 'youtube',
                'old_value'        => $oldUrl ?: null,
                'new_value'        => $newUrl ?: null,
                'created_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('TechnicalSpecsVideoSync error', [
                'video_request_id' => $request->id ?? null,
                'item_id'          => $request->item_id ?? null,
                'message'          => $e->getMessage(),
            ]);

            VideoRequestLog::create([
                'video_request_id' => $request->id,
                'user_id'          => Auth::id(),
                'action'           => 'sync_youtube_error',
                'channel'          => 'youtube',
                'old_value'        => null,
                'new_value'        => $e->getMessage(),
                'created_at'       => now(),
            ]);
        }
    }

    /**
     * Separa el bloque de videos (si existe al inicio) del texto libre.
     *
     * @return array{0: string[], 1: string}
     */
    private function splitBlock(string $text): array
    {
        $normalized = str_replace("\r\n", "\n", $text);
        $leftTrimmed = ltrim($normalized);

        if (! str_starts_with($leftTrimmed, self::HEADER)) {
            return [[], trim($normalized)];
        }

        $parts = preg_split('/\n[ \t]*\n/', $leftTrimmed, 2);
        $block = $parts[0] ?? '';
        $freeText = trim($parts[1] ?? '');

        $lines = array_map('trim', explode("\n", $block));
        array_shift($lines); // quitar la línea del encabezado
        $urls = array_values(array_filter($lines, fn ($l) => $l !== ''));

        return [$urls, $freeText];
    }

    /**
     * @param string[] $urls
     */
    private function rebuild(array $urls, string $freeText): string
    {
        if (empty($urls)) {
            return $freeText;
        }

        $block = self::HEADER . "\n" . implode("\n", $urls);

        return $freeText === '' ? $block : $block . "\n\n" . $freeText;
    }
}
