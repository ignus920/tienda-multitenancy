<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CnfAccesoHorario extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_acceso_horarios';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'day_of_week' => 'integer',
        'is_active' => 'boolean',
    ];
}
