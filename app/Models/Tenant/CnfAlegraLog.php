<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CnfAlegraLog extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_alegra_logs';
    public $timestamps = false;

    protected $fillable = [
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'status_code',
        'response_time_ms',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'status_code' => 'integer',
        'response_time_ms' => 'float',
    ];
}
