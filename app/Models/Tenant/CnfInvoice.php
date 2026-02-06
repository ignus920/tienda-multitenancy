<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CnfInvoice extends Model
{
    public $timestamps = false;

    protected $connection = 'tenant';
    protected $table = 'cnf_invoices';

    protected $fillable = [
        'id',
        'token',
        'id_warehouses',
        'numeracion',
        'facturador', // Este campo contiene la base_url
        'username',
        'timeout'
    ];

    protected $casts = [
        'id' => 'integer',
        'id_warehouses' => 'integer',
        'numeracion' => 'integer',
    ];

    /**
     * Relación con el warehouse
     */
    // public function warehouse(): BelongsTo
    // {
    //     return $this->belongsTo(\App\Models\Tenant\Customer\VntWarehouse::class, 'id_warehouses');
    // }

    public function warehouseRap(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\VntWarehouse::class, 'id_warehouses');
    }

    /**
     * Scope para filtrar por warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('id_warehouses', $warehouseId);
    }
}
