<?php

namespace App\Models\Tenant\Transfers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvDetailTransfer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
     protected $connection = 'tenant';
     protected $table = 'inv_detail_transfers';

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
        'quantity',
        'transferId',
        'itemId',
        'amount_received',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'amount_received' => 'integer',
    ];

    /**
     * Define la relación con la transferencia principal.
     * Un detalle pertenece a una transferencia.
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InvTransfer::class, 'transferId', 'id');
    }

    /**
     * Relación con el item
     */
    public function item()
    {
        return $this->belongsTo(\App\Models\Tenant\Items\Items::class, 'itemId');
    }
}