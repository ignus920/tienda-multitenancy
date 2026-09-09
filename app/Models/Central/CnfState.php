<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

/**
 * Departamento / estado (RAP - BD central, tabla `states`).
 * cities.state_id -> states.id
 */
class CnfState extends Model
{
    protected $connection = 'central';
    protected $table = 'states';

    protected $fillable = [
        'country_id',
        'name',
    ];

    protected $casts = [
        'id' => 'integer',
        'country_id' => 'integer',
    ];

    public $timestamps = false;
}
