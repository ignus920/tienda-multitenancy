<?php

namespace App\Models\TenantModels\Customers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TenantModels\Customers\CnfTypeIdentification;
use App\Models\TenantModels\Customers\CnfFiscalResponsibility;

class VntCompanies extends Model
{
    use HasFactory, SoftDeletes;
         // protected $connection = 'central';
    protected $connection = 'tenant';

    /**
     * Nombre de la tabla asociada al modelo
     */
    protected $table = 'vnt_companies';

    /**
     * Nombre de la columna de creación
     */
    const CREATED_AT = 'createdAt';

    /**
     * Nombre de la columna de actualización
     */
    const UPDATED_AT = 'updatedAt';

    /**
     * Nombre de la columna de eliminación suave
     */
    const DELETED_AT = 'deletedAt';

    /**
     * Los atributos que son asignables en masa
     */
    protected $fillable = [
        'businessName',
        'billingEmail',
        'firstName',
        'lastName',
        'secondLastName',
        'secondName',
        'identification',
        'checkDigit',
        'status',
        'integrationDataId',
        'typePerson',
        'typeIdentificationId',
        'regimeId',
        'fiscalResponsibilityId',
        'code_ciiu',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected $casts = [
        'status' => 'integer',
        'checkDigit' => 'integer',
        'integrationDataId' => 'integer',
        'typePerson' => 'integer',
        'typeIdentificationId' => 'integer',
        'regimeId' => 'integer',
        'fiscalResponsibilityId' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    /**
     * Valores por defecto de los atributos
     */
    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Relación: Una compañía pertenece a un tipo de identificación
     */
    public function typeIdentification()
    {
        return $this->belongsTo(CnfTypeIdentification::class, 'typeIdentificationId');
    }

    /**
     * Relación: Una compañía pertenece a una responsabilidad fiscal
     */
    public function fiscalResponsibility()
    {
        return $this->belongsTo(CnfFiscalResponsibility::class, 'fiscalResponsibilityId');
    }

    /**
     * Scope para filtrar compañías activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope para filtrar compañías inactivas
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Accessor para obtener el nombre completo de la persona
     */
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->firstName,
            $this->secondName,
            $this->lastName,
            $this->secondLastName,
        ]);
        
        return implode(' ', $names);
    }

    /**
     * Accessor para obtener el nombre a mostrar (razón social o nombre completo)
     */
    public function getDisplayNameAttribute()
    {
        return $this->businessName ?? $this->full_name;
    }

}
