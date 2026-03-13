<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportPriceCalculation extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'inv_items_import_price_calculations';

    protected $fillable = [
        'trm',
        'price_per_kilo',
    ];

    protected $casts = [
        'trm' => 'decimal:2',
        'price_per_kilo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
