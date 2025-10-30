<?php

namespace App\Models\TenantModels\Customers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnfFiscalResponsibility extends Model
{
    //
    use HasFactory, SoftDeletes;

      // protected $connection = 'central';

    protected $connection = 'tenant';
    // 1. Nombre de la tabla
    protected $table = 'cnf_fiscal_responsabilities';

    // 2. Clave primaria (Laravel asume 'id' por defecto)
    // protected $primaryKey = 'id';

    // 3. Atributos "Mass Assignable" (Campos que se pueden llenar masivamente)
    protected $fillable = [
        'description',
        'integrationDataId',
    ];

    // 4. Fechas Personalizadas (Sobrescribir las convenciones de nombres)
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    protected $dates = ['deletedAt']; // Campo usado por SoftDeletes

    // 5. Deshabilitar la administración automática de timestamps
    // Si usaste $table->timestamps(); en la migración, NO uses las 
    // constantes de arriba y NO incluyas esta línea.
    // En tu caso, como tienes 'createdAt' y 'updatedAt' específicos, 
    // SÍ debes incluir esta línea:
    public $timestamps = true;
}
