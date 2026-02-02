<?php

namespace App\Models\Tenant\Invoices;

use App\Models\Tenant\Quoter\VntQuote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VntInvoicesXsales extends Model
{

    protected $connection = 'tenant';
    protected $table = 'vnt_invoicesXsales';
    public $timestamps = false; // No tiene campos created_at/updated_at

    protected $fillable = [
        'remissionId',
        'quoteId',
        'invoiceId'
    ];



    // Relaciones
    public function quote(): BelongsTo
    {
        return $this->belongsTo(VntQuote::class, 'quoteId');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VntInvoices::class, 'invoiceId');
    }
}