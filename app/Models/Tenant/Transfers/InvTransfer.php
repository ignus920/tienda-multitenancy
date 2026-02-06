<?php

namespace App\Models\Tenant\Transfers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvTransfer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
     protected $connection = 'tenant';
    protected $table = 'inv_transfers';

    /**
     * El nombre de la clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'observations',
        'status',
        'api_data_id',
        'warehouseFromId',
        'warehouseToId',
        'consecutive',
        'userId',
        'packing',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'status' => 'integer',
        'packing' => 'integer',
    ];

    /**
     * Define la relación con los detalles de la transferencia.
     * Una transferencia tiene muchos detalles.
     */
    public function details(): HasMany
    {
        return $this->hasMany(InvDetailTransfer::class, 'transferId', 'id');
    }

    /**
     * Relación con el almacén de origen (base de datos central)
     */
    public function warehouseFrom()
    {
        return $this->belongsTo(\App\Models\Central\VntWarehouse::class, 'warehouseFromId', 'id');
    }

    /**
     * Relación con el almacén de destino (base de datos central)
     */
    public function warehouseTo()
    {
        return $this->belongsTo(\App\Models\Central\VntWarehouse::class, 'warehouseToId', 'id');
    }

    /**
     * Relación con el usuario que creó la transferencia (base de datos central)
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'userId', 'id');
    }
}