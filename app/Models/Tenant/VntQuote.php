<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VntQuote extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_quotes';

    protected $fillable = [
        'consecutive',
        'status',
        'typeQuote',
        'customerId',
        'warehouseId',
        'userId',
        'observations',
        'branchId'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(VntDetailQuote::class, 'quoteId');
    }

    public function getTotalAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->quantity * $detalle->value;
        });
    }
}