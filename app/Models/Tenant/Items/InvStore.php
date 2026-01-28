<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvStore extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'inv_store';
    
    // Specify custom timestamp column names
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

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
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];
}
