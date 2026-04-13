<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CnfLogAcceso extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_log_accesos';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'access_type',
        'user_agent',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
