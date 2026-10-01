<?php

namespace App\Models\Tenant\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class LabLeftover extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_lab_leftovers';

    protected $fillable = [
        'item_id',
        'available_cm',
    ];

    protected $casts = [
        'available_cm' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
