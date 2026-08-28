<?php

namespace App\Models\Tenant\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRequestTask extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'mkt_video_request_tasks';

    protected $fillable = [
        'video_request_id',
        'channel',
        'status',
        'link',
        'sort_order',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'video_request_id' => 'integer',
        'sort_order' => 'integer',
        'completed_at' => 'datetime',
        'completed_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(VideoRequest::class, 'video_request_id');
    }

    public function getLabelAttribute(): string
    {
        return VideoRequest::CHANNELS[$this->channel]['label'] ?? $this->channel;
    }

    public function requiresLink(): bool
    {
        return VideoRequest::CHANNELS[$this->channel]['requires_link'] ?? false;
    }
}
