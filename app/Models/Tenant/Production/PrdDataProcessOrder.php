<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrdDataProcessOrder extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table      = 'prd_data_process_order';

    protected $fillable = [
        'production_order_id',
        'field_processId',
        'field_value',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(PrdProductionOrder::class, 'production_order_id');
    }

    public function field()
    {
        return $this->belongsTo(PrdFieldsProcess::class, 'field_processId');
    }
}
