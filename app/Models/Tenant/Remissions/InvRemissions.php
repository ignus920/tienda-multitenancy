<?php

namespace App\Models\Tenant\Remissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Customer\VntWarehouse;


class InvRemissions extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'inv_remissions';

    protected $fillable = [
        'id',
        'consecutive',
        'status',
        'created_at',
        'updated_at',
        'quoteId',
        'warehouseId',
        'deliveryTypeId', // Campo correcto para tipo de entrega
        'methodPaymentId',
        'userId',
        'created_by',
        'deliveryDate',
        'delivery_id',
        'expiration',
        'modify',
        'observations_return',
        'obs',
        'observations_delivery',
        'flete',
        'proof_payment',
        'payment_details',
        'from_portal',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'payment_details' => 'array',
        'from_portal' => 'boolean',
    ];

    public function quote()
    {
        return $this->belongsTo(\App\Models\Tenant\Quoter\VntQuote::class, 'quoteId');
    }

    /**
     * Relación con los detalles de la remisión
     */
    public function details()
    {
        return $this->hasMany(InvDetailRemissions::class, 'remissionId', 'id');
    }

    public function delivery()
    {
        return $this->belongsTo(\App\Models\Tenant\DeliveriesList\DisDeliveries::class, 'delivery_id');
    }

    /**
     * Relación con el usuario/vendedor desde la base central
     * Nota: Esta relación no usa Eloquent estándar porque cruza bases de datos
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /**
     * Obtiene el usuario/vendedor desde la base de datos central
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->userId) {
            return null;
        }
        return \App\Models\Auth\User::on('central')->find($this->userId);
    }

    /**
     * Relación con el almacén (inv_store)
     */
    public function store()
    {
        return $this->belongsTo(\App\Models\Tenant\Items\InvStore::class, 'warehouseId', 'id');
    }

    /**
     * Relación con la factura a través de vnt_invoicesXsales
     * Permite obtener la factura asociada a esta remisión (individual o agrupada)
     */
    public function invoice()
    {
        return $this->hasOneThrough(
            \App\Models\Tenant\Invoices\VntInvoices::class,
            \App\Models\Tenant\Invoices\VntInvoicesXsales::class,
            'remissionId', // Foreign key en vnt_invoicesXsales
            'id',          // Foreign key en vnt_invoices
            'id',          // Local key en inv_remissions
            'invoiceId'    // Local key en vnt_invoicesXsales
        );
    }

    /**
     * Relación directa con el registro en vnt_invoicesXsales
     */
    public function invoiceSale()
    {
        return $this->hasOne(\App\Models\Tenant\Invoices\VntInvoicesXsales::class, 'remissionId', 'id');
    }

    /**
     * Relación con el tipo de entrega
     */
    public function deliveryTypeModel()
    {
        return $this->belongsTo(InvDeliveryType::class, 'deliveryTypeId', 'id');
    }

    /**
     * Relación con el método de pago
     */
    public function methodPayment()
    {
        return $this->belongsTo(\App\Models\Tenant\MethodPayments\VntMethodPayMents::class, 'methodPaymentId', 'id');
    }

    public function observations()
    {
        return $this->hasMany(\App\Models\Tenant\Sales\VntObservation::class, 'reference_id')
            ->where('reference_type', 'remission');
    }

    /**
     * Relación con las autorizaciones de Cartera
     */
    public function authorizations()
    {
        return $this->hasMany(\App\Models\Tenant\Sales\VntOrderAuthorization::class, 'remission_id');
    }

    /**
     * Determina si el pedido tiene empaque autorizado pero despacho de cartera pendiente/sin autorizar
     */
    public function isEmpaqueAuthorizedButDespachoPending(): bool
    {
        if (in_array($this->status, ['ANULADO', 'ENTREGADO'])) {
            return false;
        }

        $hasEmpaque = $this->authorizations->where('auth_type', 'empaque')->where('status', 1)->isNotEmpty();
        $hasDespacho = $this->authorizations->where('auth_type', 'despacho')->where('status', 1)->isNotEmpty();

        return $hasEmpaque && !$hasDespacho;
    }

    /**
     * Getters para compatibilidad con las vistas de impresión del cotizador
     */
    public function getDetallesAttribute()
    {
        return $this->details;
    }

    public function getObservationsAttribute()
    {
        return $this->observations_return;
    }

    public function getStoreNameAttribute()
    {
        return $this->store?->name;
    }

    /**
     * Accessor para obtener el nombre completo del vendedor
     * @return string
     */
    public function getSellerNameAttribute()
    {
        $user = $this->getUser();
        if (!$user) {
            return 'N/A';
        }
        return trim($user->name . ' ' . ($user->lastName ?? ''));
    }

    /**
     * Obtiene el teléfono del vendedor/usuario que creó la remisión
     */
    public function getSellerPhoneAttribute()
    {
        $user = $this->getUser();
        return $user ? $user->phone : 'N/A';
    }

    public function getSubTotalRemAttribute()
    {
        return $this->details->sum(function ($detail) {
            return $detail->quantity * $detail->value;
        });
    }

    public function getTotalRemAttribute()
    {
        $total = $this->details->sum(function ($detail) {
            return ($detail->value + ($detail->value * $detail->tax / 100)) * $detail->quantity;
        });

        return $total + ($this->flete ?? 0);
    }

    /**
     * Calcula el peso total de la remisión en gramos sumando el peso de cada item por su cantidad.
     */
    public function getWeightGramos(): float
    {
        return $this->details->sum(function ($detail) {
            return (float) ($detail->item?->dimensions?->weight ?? 0) * $detail->quantity;
        });
    }

    /**
     * Calcula el Valor Declarado según el peso y el valor base.
     */
    public function getDeclaredValue(float $baseValue): float
    {
        $pesoGramos = $this->getWeightGramos();

        if ($pesoGramos < 5500) {
            // Peso inferior a 5500 gramos (5.50 kg)
            if ($baseValue < 114000) {
                return $baseValue;
            } else {
                return $baseValue * 0.5;
            }
        } else {
            // Peso mayor o igual a 5500 gramos (5.50 kg)
            if ($baseValue < 896000) {
                return $baseValue;
            } elseif ($baseValue >= 896000 && $baseValue <= 1791999) {
                return ($baseValue * 0.05) + 896000;
            } else {
                return $baseValue * 0.5;
            }
        }
    }
}

