<?php

namespace App\Models\Tenant\Quoter;

use App\Models\Tenant\Customer\VntContacts;
use App\Models\Tenant\Customer\VntWarehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VntQuote extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_quotes';

    protected $fillable = [
        'consecutive',
        'status',
        'typeQuote',
        'customerId',
        'warehouseId',
        'userId',
        'observations',
        'branchId',
        'flete',
        'empaque'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($quote) {
            if ($quote->branchId && $quote->customerId) {
                $branch = \App\Models\Tenant\Customer\VntWarehouse::find($quote->branchId);
                $contact = \App\Models\Tenant\Customer\VntContacts::where('warehouseId', $quote->customerId)->first();

                if ($branch && $contact) {
                    $contactWarehouse = \App\Models\Tenant\Customer\VntWarehouse::find($contact->warehouseId);
                    if ($contactWarehouse && $branch->companyId !== $contactWarehouse->companyId) {
                        throw new \Exception("La sucursal seleccionada no pertenece al cliente de la cotización.");
                    }
                }
            }
        });
    }

    // Relaciones
    public function detalles(): HasMany
    {
        return $this->hasMany(VntDetailQuote::class, 'quoteId');
    }

    public function customer(): BelongsTo
    {
        // customerId almacena el warehouseId del contacto (vnt_contacts.warehouseId = vnt_quotes.customerId)
        return $this->belongsTo(VntContacts::class, 'customerId', 'warehouseId');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'warehouseId');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(VntWarehouse::class, 'branchId');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(\App\Models\Tenant\Sales\VntObservation::class, 'reference_id')
                    ->where('reference_type', 'quote');
    }

    // Métodos de utilidad
    public function getSubTotalAttribute()
    {
        // IMPORTANTE: Los valores en vnt_detail_quotes YA incluyen impuestos
        // Por lo tanto, debemos dividir entre (1 + tax/100) para obtener el valor base SIN impuestos
        return $this->detalles->sum(function ($detalle) {
            $valorSinImpuesto = $detalle->value / (1 + $detalle->tax / 100);
            return $detalle->quantity * $valorSinImpuesto;
        });
    }

    public function getTotalAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return ($detalle->value + ($detalle->value * $detalle->tax / 100)) * $detalle->quantity;
        });
    }

    public function getCustomerNameAttribute(): string
    {
        // Obtener el nombre desde la empresa (igual a como se muestra en el formulario)
        // Ruta: quote.branchId → vnt_warehouses.id → vnt_warehouses.companyId → vnt_companies
        if ($this->relationLoaded('branch') && $this->branch?->relationLoaded('company') && $this->branch?->company) {
            return $this->branch->company->customer_name;
        }

        // Fallback: cargar la empresa si la relación no fue eager-loaded
        if ($this->branchId) {
            $branch = $this->branch ?? VntWarehouse::find($this->branchId);
            if ($branch) {
                $company = $branch->company ?? \App\Models\Tenant\Customer\VntCompany::find($branch->companyId);
                if ($company) {
                    return $company->customer_name;
                }
            }
        }

        // Último recurso: nombre del contacto
        if ($this->customer) {
            return $this->customer->full_name ?: 'Sin nombre';
        }

        return 'Cliente no encontrado';
    }

    public function getWarehouseNameAttribute()
    {
        return $this->warehouse ? $this->warehouse->name : 'Sucursal no encontrada';
    }

    /**
     * Obtiene el nombre del vendedor/usuario que creó la cotización
     * Carga el usuario desde la base de datos central
     */
    public function getSellerNameAttribute()
    {
        if (!$this->userId) {
            return 'Sin vendedor';
        }

        // Cargar el usuario desde la base de datos central
        $user = \App\Models\Auth\User::on('central')->find($this->userId);

        return $user ? $user->name : 'Sin vendedor';
    }

    /**
     * Obtiene el teléfono del vendedor/usuario que creó la cotización
     */
    public function getSellerPhoneAttribute()
    {
        $user = $this->getUser();
        return $user ? $user->phone : 'N/A';
    }

    /**
     * Obtiene el usuario/vendedor desde la base de datos central
     * Este método se puede usar para acceder al objeto completo del usuario
     */
    public function getUser()
    {
        if (!$this->userId) {
            return null;
        }

        return \App\Models\Auth\User::on('central')->find($this->userId);
    }

    /**
     * Obtiene el nombre de la bodega (sucursal) asignada al usuario que creó la cotización.
     * La bodega está almacenada en el campo 'store' de vnt_contacts (BD central)
     * y apunta a inv_store (BD tenant).
     */
    public function getStorageName()
    {
        \Illuminate\Support\Facades\Log::info('🏪 getStorageName() - Obteniendo bodega para cotización', [
            'quote_id' => $this->id,
            'userId' => $this->userId
        ]);

        // Obtener el usuario de la cotización
        $user = \App\Models\Auth\User::find($this->userId);

        if (!$user || !$user->contact_id) {
            \Illuminate\Support\Facades\Log::warning('⚠️ Usuario no encontrado o sin contacto', [
                'quote_id' => $this->id,
                'userId' => $this->userId
            ]);
            return 'Sin usuario';
        }

        // Obtener el contacto del usuario desde la BD central
        $contact = \App\Models\Central\VntContact::on('central')->find($user->contact_id);

        if (!$contact || !$contact->store) {
            \Illuminate\Support\Facades\Log::warning('⚠️ Contacto sin bodega asignada', [
                'quote_id' => $this->id,
                'contact_id' => $user->contact_id
            ]);
            return 'Sin bodega';
        }

        // Obtener el nombre de la bodega desde inv_store en la BD tenant
        $store = \App\Models\Tenant\Movements\InvStore::on('tenant')->find($contact->store);

        $storeName = $store ? $store->name : 'Bodega no encontrada';

        \Illuminate\Support\Facades\Log::info('✅ Bodega obtenida para cotización', [
            'quote_id' => $this->id,
            'store_id' => $contact->store,
            'store_name' => $storeName
        ]);

        return $storeName;
    }
}
