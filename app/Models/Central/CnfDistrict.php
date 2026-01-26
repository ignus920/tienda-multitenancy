<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnfDistrict extends Model
{
    protected $connection = 'central';
    protected $table = 'district';

    protected $fillable = [
        'id',
        'city_id',
        'status',
        'district',
        'created_at',
        'updated_at	'
    ];

    protected $casts = [
        'id' => 'integer',
        'city_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public $timestamps = true;

    public function city(): BelongsTo
    {
        return $this->belongsTo(CnfCity::class, 'city_id', 'id');
    }
}
