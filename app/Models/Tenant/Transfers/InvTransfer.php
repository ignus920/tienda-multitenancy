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
        'storeFromId',
        'storeToId',
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
        'packing' => 'string',
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
     * Relación con la bodega de origen
     */
    public function storeFrom()
    {
        return $this->belongsTo(\App\Models\Tenant\Movements\InvStore::class, 'storeFromId', 'id');
    }

    /**
     * Relación con la bodega de destino
     */
    public function storeTo()
    {
        return $this->belongsTo(\App\Models\Tenant\Movements\InvStore::class, 'storeToId', 'id');
    }

    /**
     * Obtener el warehouse de origen a través del store
     */
    public function getWarehouseFromAttribute()
    {
        if ($this->storeFrom && $this->storeFrom->warehouseId) {
            return \App\Models\Central\VntWarehouse::find($this->storeFrom->warehouseId);
        }
        return null;
    }

    /**
     * Obtener el warehouse de destino a través del store
     */
    public function getWarehouseToAttribute()
    {
        if ($this->storeTo && $this->storeTo->warehouseId) {
            return \App\Models\Central\VntWarehouse::find($this->storeTo->warehouseId);
        }
        return null;
    }

    /**
     * Relación con el usuario que creó la transferencia (base de datos central)
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'userId', 'id');
    }
}