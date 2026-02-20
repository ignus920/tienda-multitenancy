<?php

namespace App\Models\Tenant\Imports;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpLabels extends Model
{
    use SoftDeletes;

    protected $connection = "tenant"; 
    protected $table = 'imp_labels'; 
    protected $fillable = [
        'name',
        'asap',
        'estimated_date',
        'description',
        'status',
        'user_id',
    ];

    protected $casts = [
        'asap' => 'boolean',
        'status' => 'boolean',
        'estimated_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
