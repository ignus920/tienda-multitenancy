<?php

namespace App\Models\Tenant\Invoices;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VntCreditNote extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'vnt_credit_notes';

    protected $fillable = [
        'invoice_id',
        'reason',
        'payment_method',
        'observations',
        'total',
        'status',
        'api_data_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VntInvoices::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VntCreditNoteItem::class, 'credit_note_id');
    }
}
