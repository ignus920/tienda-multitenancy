<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvItemPricingParam extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'inv_item_pricing_params';

    protected $fillable = [
        'item_id',
        'exw',
        'exchange_rate',
        'freight_percent',
        'factor_list',
        'factor_min',
        'max_discount',
        'box_discount',
        'web_price',
        'scale_1_qty',
        'scale_1_discount',
        'scale_2_qty',
        'scale_2_discount',
        'scale_3_qty',
        'scale_3_discount',
        'scale_4_qty',
        'scale_4_discount',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }
}
