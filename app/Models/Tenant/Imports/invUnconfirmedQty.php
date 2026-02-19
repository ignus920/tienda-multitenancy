<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvUnconfirmedQty extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */

    protected $connection = 'tenant';

    protected $table = 'inv_unconfirmed_qty'; 

    protected $fillable = [
        'item_id',
        'qty',
        'status',
    ];

    protected $casts = [
        'qty' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con el item
     */
    public function item()
    {
        return $this->belongsTo(\App\Models\Tenant\Items\Items::class, 'item_id', 'id');
    }
}
