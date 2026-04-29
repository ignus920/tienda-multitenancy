<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VntObservation extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_observations';

    protected $fillable = [
        'reference_id',
        'reference_type',
        'observation_type',
        'observation',
        'userId',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación polimórfica (simulada o real) con el documento.
     */
    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
