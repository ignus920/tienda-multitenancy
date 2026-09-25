<?php

namespace App\Models\Tenant\Invoices;

use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Tenant\Customer\VntWarehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VntInvoices extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'vnt_invoices';

    protected $fillable = [
        'id',
        'consecutive',
        'status',
        'status_payment',
        'api_data_id',
        'api_data_id_pay',
        'partialPayment',
        'created_at',
        'updated_at',
        'deleted_at',
        'quoteId',
        'warehouseId',
        'remission',
        'creditNoteId',
        'invoiceNumber',
        'retentionFuente',
        'retentionIca',
        'retentionIva',
        'creditNote',
        'orderNumber'
    ];

    protected $casts = [
        'partialPayment' => 'decimal:2',
        'retentionFuente' => 'decimal:2',
        'retentionIca' => 'decimal:2',
        'retentionIva' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($invoice) {
            // Lógica de asignación automática de vendedor (2 ventas en 30 días)
            try {
                $quote = $invoice->quote;
                if (!$quote || !$quote->userId) return;

                $sellerId = $quote->userId; // Asesor que hizo la cotización/venta
                $companyId = $quote->branch?->companyId;
                
                if (!$companyId) return;

                $companyData = \App\Models\Tenant\Customer\VntCompany::find($companyId);
                
                // Si el cliente ya tiene vendedor fijo, respetarlo y no hacer nada
                if (!$companyData || $companyData->seller_id) return;

                $thirtyDaysAgo = now()->subDays(30);

                // Contar cuántas facturas se han hecho a este cliente, por este asesor, en los últimos 30 días
                $count = self::where('created_at', '>=', $thirtyDaysAgo)
                    ->whereHas('quote', function($q) use ($sellerId, $companyId) {
                        $q->where('userId', $sellerId)
                          ->whereHas('branch', function($b) use ($companyId) {
                              $b->where('companyId', $companyId);
                          });
                    })->count();

                if ($count >= 2) {
                    $companyData->seller_id = $sellerId;
                    $companyData->save();
                    \Illuminate\Support\Facades\Log::info("✅ Asignación automática de vendedor ID {$sellerId} al cliente ID {$companyId} (2 compras en 30 días).");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("❌ Error en auto-asignación de vendedor: " . $e->getMessage());
            }
        });
    }

    // Relaciones
    public function quote(): BelongsTo
    {
        return $this->belongsTo(VntQuote::class, 'quoteId');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'warehouseId');
    }

    public function payments()
    {
        return $this->hasMany(VntInvoicePayments::class, 'invoiceId');
    }

    public function creditNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VntCreditNote::class, 'invoice_id');
    }

    // Métodos de utilidad
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'REGISTRADO' => 'Registrado',
            'FACTURADO' => 'Facturado',
            'ANULADO' => 'Anulado',
            'SIN EMITIR' => 'Sin emitir'
        };
    }

    public function getPaymentStatusTextAttribute(): string
    {
        return match ($this->status_payment) {
            'REGISTRADO' => 'Registrado',
            'ABONO' => 'Abono',
            'PAGADO' => 'Pagado',
            'ANULADO' => 'Anulado'
        };
    }
}
