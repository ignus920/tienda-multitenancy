<?php

namespace App\Models\Tenant\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\User;

class InventoryConfirmation extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'inv_inventory_confirmations';

    protected $fillable = [
        'item_id',
        'requested_quantity',
        'requester_id',
        'confirmed_quantity',
        'confirmer_id',
        'observations',
        'confirmation_observations',
        'status',
        'requested_at',
        'confirmed_at',
        'system_stock'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Relación con el producto (Item)
     */
    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    /**
     * Relación con el usuario que solicita
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relación con el usuario que confirma
     */
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmer_id');
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
