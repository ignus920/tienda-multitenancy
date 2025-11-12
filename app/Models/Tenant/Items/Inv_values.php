<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inv_values extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'inv_values';

    protected $fillable = [
        'date',
        'values',
        'type',
        'itemId',
        'warehouseId',
        'label',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
