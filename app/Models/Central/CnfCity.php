<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CnfCity extends Model
{
    protected $connection = 'central';
    protected $table = 'cities';

    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'country_code'
    ];

    protected $casts = [
        'id' => 'integer',
        'country_id' => 'integer',
        'state_id' => 'integer'
    ];

    public $timestamps = false;

    /**
     * Departamento / estado al que pertenece la ciudad (RAP central).
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(CnfState::class, 'state_id');
    }
}
