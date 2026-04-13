<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;

class InvItemAccesorios extends Model
{
    protected $connection = 'tenant';
    protected $table = 'inv_items_accessories';
    public $timestamps = false;

    protected $fillable = [
        'item',
        'insumo',
        'observacion',
    ];

    protected $casts = [
        'item'   => 'integer',
        'insumo' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item');
    }

    public function insumo()
    {
        return $this->belongsTo(Items::class, 'insumo');
    }
}
