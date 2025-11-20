<?php

namespace App\Models\Tenant\PettyCash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntDetailPettyCash extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'vnt_detail_petty_cash';

    protected $fillable = [
        'status',
        'value',
        'created_at',
        'updated_at',
        'pettyCashId',
        'reasonPettyCashId',
        'methodPaymentId',
        'invoiceId',
        'observations'
    ];
}
