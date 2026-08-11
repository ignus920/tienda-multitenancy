<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemObservation extends Model
{
    use SoftDeletes;

    /**
     * La conexión asociada al modelo.
     */
    protected $connection = 'tenant';

    /**
     * La tabla asociada al modelo.
     */
    protected $table = 'inv_item_observations';

    /**
     * Los atributos que se pueden asignar de manera masiva.
     */
    protected $fillable = [
        'item_id',
        'observations',
        'technical_specifications',
        'commercial_observations',
        'status',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'item_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
