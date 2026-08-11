<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CnfModelLog extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_model_logs';
    public $timestamps = false;

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'user_id' => 'integer',
    ];
}
