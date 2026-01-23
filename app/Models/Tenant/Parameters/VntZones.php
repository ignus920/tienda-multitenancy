<?php

namespace App\Models\Tenant\Parameters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntZones extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'vnt_zones';

    protected $fillable = [
        'id',
        'name',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
