<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Items extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'inv_items';

    protected $fillable = [
        'categoryId',
        'name',
        'internal_code',
        'sku',
        'description',
        'type',
        'commandId',
        'brandId',
        'houseId',
        'inventoriable',
        'purchasing_unit',
        'consumption_unit',
        'status',
        'generic',
        'deleted_at',
    ];

    


}
