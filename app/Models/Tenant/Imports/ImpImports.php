<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpImports extends Model
{
    use HasFactory, SoftDeletes;

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
    protected $table = 'imp_imports';

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
        'user_id',
        'label_id',
        'qty_requested',
        'qty_shipped',
        'price',
        'status',
        'packing_id',
        'news',
        'priority',
        'priority_assigned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'item_id' => 'integer',
        'user_id' => 'integer',
        'label_id' => 'integer',
        'qty_requested' => 'integer',
        'qty_shipped' => 'integer',
        'price' => 'double',
        'status' => 'integer',
        'packing_id' => 'integer',
        'news' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con el item
     */
    public function itemsSetup()
    {
        return $this->belongsTo(ImpItemsSetup::class, 'item_id', 'id');
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id', 'id');
    }

    /**
     * Relación con la etiqueta
     */
    public function label()
    {
        return $this->belongsTo(ImpLabels::class, 'label_id', 'id');
    }

    /**
     * Relación con los estados
     */
    public function status()
    {
        return $this->belongsTo(ImpStatus::class, 'status', 'id');
    }

    /**
     * Relación con el Item (a través de itemsSetup)
     */
    public function invItem()
    {
        return $this->hasOneThrough(
            \App\Models\Tenant\Items\Items::class,
            ImpItemsSetup::class,
            'id', // Foreign key on imp_items_setup table...
            'id', // Foreign key on items table...
            'item_id', // Local key on imp_imports table...
            'item_id'  // Local key on imp_items_setup table...
        );
    }

    public function packing()
    {
        return $this->belongsTo(ImpPacking::class, 'packing_id');
    }
}
