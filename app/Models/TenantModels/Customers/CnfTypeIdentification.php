<?php

namespace App\Models\TenantModels\Customers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnfTypeIdentification extends Model
{
    //
    use HasFactory, SoftDeletes;
    // protected $connection = 'central';

    protected $connection = 'tenant';
    // 1. Especifica el nombre de la tabla
    // Necesario porque el nombre de la tabla no sigue la convención por defecto (type_identifications).
    protected $table = 'cnf_type_identifications';

    // 2. Define los campos que se pueden llenar masivamente (Mass Assignable)
    protected $fillable = [
        'name',
        'acronym',
        'api_data_id',
        'status',
    ];

    // 3. Sobrescribe los nombres de las columnas de fecha (Timestamps)
    // Esto asegura que Laravel use 'createdAt' y 'updatedAt' en lugar de 'created_at' y 'updated_at'.
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    
    // 4. Marca el campo para Soft Deletes
    // Define el campo que usa SoftDeletes.
    protected $dates = ['deletedAt'];

    // 5. Especifica el tipo de dato para la columna 'status'
    // Convierte el valor 'status' a booleano automáticamente al acceder al modelo.
    protected $casts = [
        'status' => 'boolean',
    ];
}
