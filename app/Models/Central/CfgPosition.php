<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfgPosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'cfg_positions';

    protected $fillable = [
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'status' => 'integer',
        ];
    }
}
