<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CnfAccesoIp extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_acceso_ips';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_allowed',
        'description',
        'is_active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
