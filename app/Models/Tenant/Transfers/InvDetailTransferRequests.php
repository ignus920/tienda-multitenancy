<?php

namespace App\Models\Tenant\Transfers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvDetailTransferRequests extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $connection = 'tenant';
    protected $table = 'inv_detail_transfer_requests';

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
        'quantitySend',
        'transferRequestId',
        'itemId',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'quantitySend' => 'integer',
    ];

    /**
     * Define la relación con la transferencia principal.
     * Un detalle pertenece a una transferencia.
     */
    public function transfer_requests(): BelongsTo
    {
        return $this->belongsTo(InvTransferRequest::class, 'transferRequestId', 'id');
    }

    /**
     * Relación con el item
     */
    public function item()
    {
        return $this->belongsTo(\App\Models\Tenant\Items\Items::class, 'itemId');
    }
}
