<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpShipmentComments extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imp_shipment_comments';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shipment_id',
        'comment',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'shipment_id' => 'integer',
        'comment' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * Relación con el envío
     */
    public function shipment()
    {
        return $this->belongsTo(ImpShippments::class, 'shipment_id', 'id');
    }
}
