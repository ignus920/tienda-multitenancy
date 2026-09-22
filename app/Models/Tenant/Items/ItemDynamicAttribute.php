<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;

class ItemDynamicAttribute extends Model
{
    protected $connection = 'tenant';
    
    protected $table = 'inv_item_dynamic_attributes';

    protected $fillable = [
        'item_id',
        'label',
        'field_type',
        'options',
        'value',
        'order_index',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    /**
     * Devuelve las opciones como arreglo si es tipo select
     */
    public function getOptionsArrayAttribute()
    {
        if ($this->field_type === 'select' && !empty($this->options)) {
            $opts = explode(',', $this->options);
            return array_map('trim', $opts);
        }
        return [];
    }
}
