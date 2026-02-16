<?php

namespace App\Models\Tenant\Items;

use App\Models\Central\VntWarehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvStore extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_store';

    // Specify custom timestamp column names
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'name',
        'warehouseId',
        'store_manager',
        'status',
        'api_data_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'warehouseId' => 'integer',
        'store_manager' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the warehouse (sucursal) associated with this store.
     * Note: This crosses database connections (tenant -> central)
     *
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'warehouseId', 'id');
    }
}
