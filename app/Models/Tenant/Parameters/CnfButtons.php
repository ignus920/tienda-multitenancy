<?php

namespace App\Models\Tenant\Parameters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CnfButtons extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'cnf_buttons';

    protected $fillable = [
        'tittle',
        'status',
        'color',
        'module',
        'link',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
