<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\ReservationStatus;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'inv_reservations';

    protected $fillable = [
        'quantity',
        'customer_id',
        'item_id',
        'due_date',
        'advance_payment',
        'obs',
        'status_id',
        'description',
        'user_id',
        'stock_type',
    ];

    protected $casts = [
        'due_date' => 'date',
        'quantity' => 'integer',
        'status_id' => 'integer',
        'customer_id' => 'integer',
        'item_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(VntCompany::class, 'customer_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(ReservationStatus::class, 'status_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id', 'id');
    }
}
