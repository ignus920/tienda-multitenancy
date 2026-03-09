<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrdFieldsProcess extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'prd_fields_process';
    protected $primaryKey = 'id';

    protected $fillable = [
        'processId',
        'name',
        'label',
        'type',
        'class',
        'options',
        'status',
        'parent_field',
        'parent_value',
    ];

    public function process()
    {
        return $this->belongsTo(PrdProcess::class, 'processId', 'id');
    }
}
