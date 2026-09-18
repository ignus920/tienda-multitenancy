<?php

namespace App\Models\Tenant\CostCalculation;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class CostCalculationItem extends Model
{
    protected $connection = 'tenant';

    protected $table = 'cnf_cost_calculation_items';

    protected $fillable = [
        'cost_calculation_id',
        'origin',
        'item_id',
        'description',
        'mode',
        'quantity',
        'cm_quantity',
        'unit_value',
        'line_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cm_quantity' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'line_cost' => 'decimal:2',
    ];

    public function calculation()
    {
        return $this->belongsTo(CostCalculation::class, 'cost_calculation_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
