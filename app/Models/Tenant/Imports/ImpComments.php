<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpComments extends Model
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
    protected $table = 'imp_comments';

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
        'import_id',
        'new_product_id',
        'comment',
        'user_id',
        'initiator',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_id' => 'integer',
        'new_product_id' => 'integer',
        'comment' => 'string',
        'user_id' => 'integer',
        'initiator' => 'integer',
    ];

    /**
     * Relación con el item
     */
    public function import()
    {
        return $this->belongsTo(ImpImports::class, 'import_id', 'id');
    }

    /**
     * Relación con el producto nuevo temporal
     */
    public function newProduct()
    {
        return $this->belongsTo(ImpNewProduct::class, 'new_product_id', 'id');
    }
}
