<?php

namespace App\Models\Tenant\Marketing;

use App\Models\Tenant\Items\Items;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRequest extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'mkt_video_requests';

    protected $fillable = [
        'request_number',
        'item_id',
        'product_code',
        'product_name',
        'requested_by',
        'gestor_id',
        'instructions',
        'status',
        'progress_done',
        'progress_total',
        'progress_percent',
        'youtube_url',
        'youtube_synced_url',
        'youtube_synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'requested_by' => 'integer',
        'gestor_id' => 'integer',
        'progress_done' => 'integer',
        'progress_total' => 'integer',
        'progress_percent' => 'integer',
        'youtube_synced_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Actividades de la lista de chequeo. La clave es el canal.
     */
    public const CHANNELS = [
        'celular'   => ['label' => 'Video tomado en celular', 'order' => 1, 'requires_link' => false],
        'youtube'   => ['label' => 'Video subido a YouTube',   'order' => 2, 'requires_link' => true],
        'web'       => ['label' => 'Video subido a página web', 'order' => 3, 'requires_link' => true],
        'tiktok'    => ['label' => 'Video subido a TikTok',     'order' => 4, 'requires_link' => true],
        'instagram' => ['label' => 'Video subido a Instagram',  'order' => 5, 'requires_link' => true],
    ];

    public const STATUS_LABELS = [
        'pendiente'   => 'Pendiente',
        'en_proceso'  => 'En proceso',
        'terminado'   => 'Terminado',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(VideoRequestTask::class, 'video_request_id')->orderBy('sort_order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VideoRequestLog::class, 'video_request_id')->latest('created_at');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    /**
     * Código de producto vigente (maestro) con respaldo al snapshot.
     */
    public function getProductCodeActualAttribute(): ?string
    {
        return $this->item?->internal_code ?: ($this->item?->sku ?: $this->product_code);
    }

    /**
     * Descripción de producto vigente (maestro) con respaldo al snapshot.
     */
    public function getProductNameActualAttribute(): ?string
    {
        return $this->item?->name ?: $this->product_name;
    }

    /**
     * Comprueba si un enlace corresponde al dominio esperado del canal.
     */
    public static function linkMatchesChannel(string $channel, ?string $link): bool
    {
        $link = trim((string) $link);

        if ($link === '' || filter_var($link, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = strtolower((string) parse_url($link, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);

        return match ($channel) {
            'youtube'   => in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be'], true),
            'tiktok'    => $host === 'tiktok.com' || str_ends_with($host, '.tiktok.com'),
            'instagram' => $host === 'instagram.com' || str_ends_with($host, '.instagram.com'),
            'web'       => $host !== '',
            default     => false,
        };
    }
}
