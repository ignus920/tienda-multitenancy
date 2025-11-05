<?php

namespace App\Models\Tenant;

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
        'typePerson',
        'typeIdentificationId',
        'regimeId',
        'code_ciiu',
        'fiscalResponsabilityId',
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
    ];

    /**
     * Los atributos de fecha del modelo.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'deleted_at',
    ];


    // --- Relaciones (Puedes agregar las relaciones Eloquent aquí) ---

    // Ejemplo de relación (Asumiendo que 'typeIdentificationId' es una FK)
    /*
    public function typeIdentification()
    {
        return $this->belongsTo(TypeIdentification::class, 'typeIdentificationId');
    }
    */
}
