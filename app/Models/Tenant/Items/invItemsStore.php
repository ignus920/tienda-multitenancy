<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // Trait para softDeletes

/**
 * @property int $id
 * @property int $itemId
 * @property int $storeId
 * @property float|null $initial_stock
 * @property float|null $stock_items_store
 * @property float $stock_min
 * @property float $stock_max
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\InvItem $item
 * @property-read \App\Models\InvStore $store
 */
class InvItemsStore extends Model
{
    // Usa los traits SoftDeletes y HasFactory
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $connection = 'tenant';
    protected $table = 'inv_items_store';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el ID es autoincremental.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * El tipo de datos de la clave primaria.
     * En la migración usaste unsignedInteger, que corresponde a 'int'
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Los atributos que son asignables masivamente (Mass Assignable).
     * Esto permite usar el método create() o update() con un array de datos.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'itemId',
        'storeId',
        'initial_stock',
        'stock_items_store',
        'stock_min',
        'stock_max',
    ];

    /**
     * Los atributos que deben ser "casteados" a tipos nativos.
     * En la migración, 'decimal' debe ser casteado a 'float' o 'decimal'.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'initial_stock' => 'float',
        'stock_items_store' => 'float',
        'stock_min' => 'float',
        'stock_max' => 'float',
        'deleted_at' => 'datetime', // Requerido por SoftDeletes
    ];

    // --- Relaciones Eloquent ---

    /**
     * Obtiene el artículo (item) al que pertenece este registro de stock.
     * Relación: Mucho a Uno (Belongs To) con 'inv_items'.
     */
    public function item(): BelongsTo
    {
        // Asume que el modelo del artículo se llama 'InvItem'
        return $this->belongsTo(InvItem::class, 'itemId', 'id');
    }

    /**
     * Obtiene el almacén (store) al que pertenece este registro de stock.
     * Relación: Mucho a Uno (Belongs To) con 'inv_store'.
     */
    public function store(): BelongsTo
    {
        // Asume que el modelo del almacén se llama 'InvStore'
        return $this->belongsTo(InvStore::class, 'storeId', 'id');
    }
}