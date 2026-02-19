<?php

namespace App\Models\Tenant\Remissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvDeliveryType extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'inv_delivery_types';

    protected $fillable = [
        'name',
        'status',
        'ask_details',
        'detail'
    ];

    protected $casts = [
        'status' => 'boolean',
        'ask_details' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Scope para obtener solo los tipos de entrega activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Relación con las remisiones que usan este tipo de entrega
     */
    public function remissions()
    {
        return $this->hasMany(InvRemissions::class, 'deliveryTypeId', 'id');
    }
}