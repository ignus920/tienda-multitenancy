<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Production\PrdProductionOrder;
use App\Models\Tenant\Customer\VntContacts as Customer;

class InvItemsCutDetails extends Model
{
    protected $connection = 'tenant';
    protected $table = 'inv_items_cut_details';

    protected $fillable = [
        'item_id',
        'repeat_in',
        'plan_centimeters',
        'plan_millimeters',
        'production_order_id',
        'accumulated',
        'remaining',
        'status',
        'cut_id',
        'customer_id',
        'notes',
        'created_by',
        'justification',
        'accumulated_cm',
        'remaining_cm',
        'length_cm',
        'length_mm',
    ];

    protected $casts = [
        'repeat_in' => 'integer',
        'production_order_id' => 'integer',
        'accumulated' => 'decimal:2',
        'remaining' => 'decimal:2',
        'status' => 'integer',
        'cut_id' => 'integer',
        'customer_id' => 'integer',
        'accumulated_cm' => 'decimal:2',
        'remaining_cm' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'length_mm' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el Item
     */
    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    /**
     * Relación con la Orden de Producción
     */
    public function productionOrder()
    {
        return $this->belongsTo(PrdProductionOrder::class, 'production_order_id');
    }

    /**
     * Relación con el Cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
