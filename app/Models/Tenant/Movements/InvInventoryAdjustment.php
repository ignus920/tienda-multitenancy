<?php

namespace App\Models\Tenant\Movements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class InvInventoryAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_inventory_adjustments';

    protected $fillable = [
        'date',
        'observations',
        'type',
        'status',
        'api_data_id',
        'warehouseId',
        'reasonId',
        'consecutive',
        'userId',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'date' => 'datetime',
            'status' => 'integer',
            'warehouseId' => 'integer',
            'reasonId' => 'integer',
            'consecutive' => 'integer',
            'userId' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the details for this inventory adjustment
     */
    public function details()
    {
        return $this->hasMany(InvDetailInventoryAdjustment::class, 'inventoryAdjustmentId', 'id');
    }

    /**
     * Get the reason for this adjustment
     */
    public function reason()
    {
        return $this->belongsTo(InvReason::class, 'reasonId', 'id');
    }

    /**
     * Get the warehouse/store for this adjustment
     */
    public function warehouse()
    {
        return $this->belongsTo(InvStore::class, 'warehouseId', 'id');
    }

    /**
     * Get the user who created this adjustment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    // Scopes

    /**
     * Scope to filter by status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to filter inactive adjustments
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouseId', $warehouseId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Accessors

    /**
     * Get formatted consecutive number
     */
    public function getFormattedConsecutiveAttribute()
    {
        return str_pad($this->consecutive, 6, '0', STR_PAD_LEFT);
    }
}
