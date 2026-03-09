<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Customer\VntContacts;

class PrdProductionOrder extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'prd_production_order';

    protected $fillable = [
        'date',
        'item_id',
        'requested_qty',
        'warehouse_customer_id',
        'customer_order',
        'delivery_date',
        'productive_stage',
        'status',
        'sales_order',
    ];

    protected $casts = [
        'date'            => 'date',
        'delivery_date'   => 'date',
        'requested_qty'   => 'integer',
        'productive_stage'=> 'integer',
        'status'          => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function contact()
    {
        return $this->belongsTo(VntContacts::class, 'warehouse_customer_id');
    }

    public function productiveProcess()
    {
        return $this->belongsTo(PrdProcess::class, 'productive_stage');
    }
}
