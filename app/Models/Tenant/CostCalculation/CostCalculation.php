<?php

namespace App\Models\Tenant\CostCalculation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class CostCalculation extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'cnf_cost_calculations';

    protected $fillable = [
        'name',
        'type',
        'price_list_label',
        'sale_price',
        'max_discount_percent',
        'created_by',
        'updated_by',
        'deleted_by',
        'deletion_reason',
        'time_1_hours',
        'time_1_minutes',
        'time_3_hours',
        'time_3_minutes',
        'time_5_hours',
        'time_5_minutes',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'max_discount_percent' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(CostCalculationItem::class, 'cost_calculation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
