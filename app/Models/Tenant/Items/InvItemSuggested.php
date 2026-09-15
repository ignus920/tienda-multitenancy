<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;

class InvItemSuggested extends Model
{
    protected $connection = 'tenant';
    protected $table = 'inv_items_suggested';

    protected $fillable = [
        'item',
        'suggested_item',
    ];

    protected $casts = [
        'item'           => 'integer',
        'suggested_item' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item');
    }

    public function suggestedItem()
    {
        return $this->belongsTo(Items::class, 'suggested_item');
    }
}
