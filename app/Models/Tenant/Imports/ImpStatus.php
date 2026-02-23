<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpStatus extends Model
{
    use SoftDeletes;

    protected $connection = "tenant";
    protected $table = 'imp_status';
    protected $fillable = [
        'name',
        'translated_name',
        'in_progress',
        'function',
        'supplier',
        'edition',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'name' => 'string',
        'translated_name' => 'string',
        'in_progress' => 'integer',
        'function' => 'string',
        'supplier' => 'integer',
        'edition' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
