<?php

namespace App\Models\Tenant\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VntCompany extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada con el modelo.
     * La migración usa 'vnt_companies'.
     *
     * @var string
     */

    protected $connection = 'tenant';
    protected $table = 'vnt_companies';

    /**
     * El nombre de la llave primaria de la tabla.
     * La migración usa 'id'.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Los atributos que son asignables masivamente (Mass Assignable).
     * Incluye todas las columnas no de auditoría (created_at, updated_at, deleted_at).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'businessName',
        'billingEmail',
        'firstName',
        'integrationDataId',
        'identification',
        'checkDigit',
        'lastName',
        'secondLastName',
        'secondName',
        'status', // Tiene un default(1) en la migración
        'type',
        'typePerson',
        'typeIdentificationId',
        'regimeId',
        'code_ciiu',
        'fiscalResponsabilityId',
        'api_data_id', // Para sincronización con API
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * 'created_at', 'updated_at', y 'deleted_at' son manejados automáticamente si usas SoftDeletes y no los defines aquí.
     * Se especifican para claridad si quieres un formato específico, pero no es estrictamente necesario aquí.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'created_at' => 'datetime', // Laravel maneja esto por defecto
        // 'updated_at' => 'datetime', // Laravel maneja esto por defecto
        // 'deleted_at' => 'datetime', // SoftDeletes maneja esto
        'status' => 'integer',
        'integrationDataId' => 'integer',
        'checkDigit' => 'integer',
        'typeIdentificationId' => 'integer',
        'regimeId' => 'integer',
        'fiscalResponsabilityId' => 'integer',
        'api_data_id' => 'string',
    ];

    /**
     * Los atributos de fecha del modelo.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'deleted_at',
    ];

    protected static function booted()
    {
        static::saving(function ($company) {
            if ($company->identification) {
                $exists = self::where('identification', $company->identification)
                    ->where('id', '!=', $company->id)
                    ->first();
                if ($exists) {
                    $name = $exists->businessName ?: trim(implode(' ', array_filter([$exists->firstName, $exists->secondName, $exists->lastName, $exists->secondLastName])));
                    throw new \Exception("La identificación {$company->identification} ya se encuentra registrada para el cliente '{$name}' (ID: {$exists->id}).");
                }
            }
        });
    }


    // --- Relaciones ---

    /**
     * Relación con las sucursales (warehouses)
     */
    public function warehouses()
    {
        return $this->hasMany(VntWarehouse::class, 'companyId');
    }

    /**
     * Sucursal principal
     */
    public function mainWarehouse()
    {
        return $this->hasOne(VntWarehouse::class, 'companyId')->where('main', 1);
    }

    /**
     * Todos los contactos de la empresa a través de sus almacenes
     */
    public function contacts()
    {
        return $this->hasManyThrough(
            VntContacts::class,
            VntWarehouse::class,
            'companyId', // Foreign key en vnt_warehouses
            'warehouseId', // Foreign key en vnt_contacts
            'id', // Local key en vnt_companies
            'id' // Local key en vnt_warehouses
        );
    }

    /**
     * Contactos activos de la empresa
     */
    public function activeContacts()
    {
        return $this->hasManyThrough(
            VntContacts::class,
            VntWarehouse::class,
            'companyId',
            'warehouseId',
            'id',
            'id'
        )->where('vnt_contacts.status', 1);
    }

    /**
     * Relación con las rutas de la empresa
     */
    public function routes()
    {
        return $this->hasMany(VntCompanyRoute::class, 'company_id');
    }

    /**
     * Obtiene el nombre del cliente (Razón Social o Nombre Completo)
     * Mantiene compatibilidad con código existente.
     */
    public function getCustomerNameAttribute()
    {
        return $this->getDisplayNameAttribute();
    }

    /**
     * Obtiene el nombre para mostrar según el tipo de identificación:
     * - typeIdentificationId = 1 → Persona Natural → firstName + secondName + lastName + secondLastName
     * - typeIdentificationId = 2 → Persona Jurídica → businessName
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        // Tipo 2 = Persona Jurídica (NIT) → Razón Social
        if ((int) $this->typeIdentificationId === 2 && !empty(trim($this->businessName ?? ''))) {
            return $this->businessName;
        }

        // Tipo 1 = Persona Natural (o fallback si businessName está vacío) → Nombre completo concatenado
        return trim(implode(' ', array_filter([
            $this->firstName,
            $this->secondName,
            $this->lastName,
            $this->secondLastName,
        ])));
    }
}
