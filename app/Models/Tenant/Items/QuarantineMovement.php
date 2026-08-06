<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Movements\InvStore;

class QuarantineMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'inv_quarantine_movements';

    protected $fillable = [
        'item_id',
        'store_id',
        'quantity',
        'justification',
        'user_id',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'store_id' => 'integer',
        'quantity' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id', 'id');
    }
}
