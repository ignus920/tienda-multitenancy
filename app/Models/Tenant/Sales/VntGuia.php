<?php

namespace App\Models\Tenant\Sales;

use App\Models\Tenant\Production\PrdProductionOrder;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VntGuia extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'vnt_guias';

    protected $fillable = [
        'production_order_id',
        'user_id',
        'guide_number',
        'package_count',
        'carrier',
    ];

    // Relaciones
    public function productionOrder()
    {
        return $this->belongsTo(PrdProductionOrder::class, 'production_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
