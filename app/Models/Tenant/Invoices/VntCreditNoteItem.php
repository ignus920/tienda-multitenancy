<?php

namespace App\Models\Tenant\Invoices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant\Items\Items;

class VntCreditNoteItem extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_credit_note_items';

    protected $fillable = [
        'credit_note_id',
        'item_id',
        'item_name',
        'item_code',
        'quantity',
        'unit_price',
        'tax',
        'subtotal',
        'total',
        'detail_type',
        'detail_id',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax'        => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(VntCreditNote::class, 'credit_note_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
