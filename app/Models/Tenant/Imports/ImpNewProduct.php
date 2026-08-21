<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Tenant\Items\Items;

class ImpNewProduct extends Model
{
    use SoftDeletes;

    protected $table = 'imp_new_products';

    protected $fillable = [
        'code',
        'description',
        'porcentaje',
        'min_qty_supplier',
        'factor',
        'supplier_id',
        'factory_ref',
        'image_path',
        'exw',
        'incr_fletes',
        'factor_pvp1',
        'factor_pvp_min',
        'status',
        'real_item_id',
        'created_by'
    ];

    /**
     * Relación con el Proveedor
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Relación con el creador del producto temporal
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el ítem real/definitivo del ERP (cuando es convertido)
     */
    public function realItem()
    {
        return $this->belongsTo(Items::class, 'real_item_id');
    }

    /**
     * Scope para buscar registros por SKU/Código o descripción
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%')
              ->orWhere('factory_ref', 'like', '%' . $term . '%');
        });
    }

    /**
     * Helper para obtener la URL de la imagen
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/no-image.png'); // fallback
    }
}
