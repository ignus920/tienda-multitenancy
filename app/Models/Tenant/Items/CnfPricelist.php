<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;

class CnfPricelist extends Model
{
    protected $connection = 'tenant';
    protected $table = 'cnf_pricelist';

    protected $fillable = [
        'title',
        'value',
        'create_at',
        'update_at',
        'delete_at',
        'status'
    ];

    protected $casts = [
        'value' => 'float',
        'status' => 'integer',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}