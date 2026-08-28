<?php

namespace App\Models\Tenant\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRequestLog extends Model
{
    public $timestamps = false;

    protected $connection = 'tenant';
    protected $table = 'mkt_video_request_logs';

    protected $fillable = [
        'video_request_id',
        'user_id',
        'action',
        'channel',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected $casts = [
        'video_request_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(VideoRequest::class, 'video_request_id');
    }
}
