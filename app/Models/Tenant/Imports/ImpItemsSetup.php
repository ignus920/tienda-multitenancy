<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImpItemsSetup extends Model
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
    protected $table = 'imp_items_setup';

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
        'item_id',
        'percentage',
        'cantidad_min',
        'supplier_id',
        'factory_ref',
        'exw',
        'purchase_unit',
        'freight_increase',
        'pvp_factor',
        'pvp_min_factor',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'item_id' => 'integer',
        'percentage' => 'double',
        'supplier_id' => 'integer',
        'factory_ref' => 'string',
        'exw' => 'decimal',
        'purchase_unit' => 'integer',
        'freight_increase' => 'decimal',
        'pvp_factor' => 'decimal',
        'pvp_min_factor' => 'decimal',
    ];
}
