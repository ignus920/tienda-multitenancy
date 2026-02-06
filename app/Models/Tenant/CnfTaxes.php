<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnfTaxes extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'cnf_taxes';

    protected $fillable = [
        'name',
        'percentage',
        'status',
        'api_data_id',
        'inventoryAccount',
        'inventariablePurchaseAccount',
        'categoryAccount',
    ];

    protected $casts = [
        'percentage' => 'float',
        'status' => 'integer',
        'api_data_id' => 'integer',
        'inventoryAccount' => 'integer',
        'inventariablePurchaseAccount' => 'integer',
        'categoryAccount' => 'integer',
    ];
}