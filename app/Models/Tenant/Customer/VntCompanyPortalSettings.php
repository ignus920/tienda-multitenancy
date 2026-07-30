<?php

namespace App\Models\Tenant\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntCompanyPortalSettings extends Model
{
    use HasFactory;

    /**
     * Conexión a la base de datos del tenant
     */
    protected $connection = 'tenant';

    /**
     * Nombre de la tabla
     */
    protected $table = 'vnt_company_portal_settings';

    /**
     * Campos asignables masivamente
     */
    protected $fillable = [
        'company_id',
        'cash_pricelist_id',
        'credit_pricelist_id',
    ];
}
