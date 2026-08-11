<?php

namespace App\Models\Tenant\Invoices;

use App\Models\Tenant\MethodPayments\VntMethodPayMents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VntInvoicePayments extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_invoice_payments';
    public $timestamps = false;

    protected $fillable = [
        'value',
        'invoiceId',
        'methodPaymentId',
        'proof_payment'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VntInvoices::class, 'invoiceId');
    }

    public function methodPayment(): BelongsTo
    {
        return $this->belongsTo(VntMethodPayMents::class, 'methodPaymentId');
    }
}
