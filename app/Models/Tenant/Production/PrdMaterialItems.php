<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;

class PrdMaterialItems extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table      = 'prd_material_items';

    protected $fillable = [
        'qty',
        'unit_measurement',
        'material_itemId',
        'production_order_id',
        'process_id',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(PrdProductionOrder::class, 'production_order_id');
    }

    public function process()
    {
        return $this->belongsTo(PrdProcess::class, 'process_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'material_itemId');
    }
}
