<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Brand;
use App\Models\Central\CnfTaxes;

class Items extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'inv_items';

    protected $fillable = [
        'api_data_id',
        'categoryId',
        'name',
        'internal_code',
        'sku',
        'description',
        'type',
        'taxId',
        'commandId',
        'brandId',
        'houseId',
        'inventoriable',
        'purchasing_unit',
        'consumption_unit',
        'generic',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brandId', 'id');
    }

    public function purchasingUnit()
    {
        return $this->belongsTo(UnitMeasurements::class, 'purchasing_unit', 'id');
    }

    public function consumptionUnit()
    {
        return $this->belongsTo(UnitMeasurements::class, 'consumption_unit', 'id');
    }

    public function tax(){
        return $this->belongsTo(CnfTaxes::class, 'taxId', 'id');
    }

    public function invValues()
    {
        return $this->hasMany(InvValues::class, 'itemId', 'id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'api_data_id' => 'integer',
            'categoryId' => 'integer',
            'commandId' => 'integer',
            'brandId' => 'integer',
            'houseId' => 'integer',
            'inventoriable' => 'integer',
            'purchasing_unit' => 'integer',
            'consumption_unit' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    public function scopeInventoriable($query)
    {
        return $query->where('inventoriable', 1);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('categoryId', $categoryId);
    }

    // Accessors
    public function getPriceAttribute()
    {
        // Por ahora retornamos un precio fijo, después puedes implementar la lógica de precios
        return 15000; // $15.000 como en la imagen
    }

    public function getFormattedPriceAttribute()
    {
        return '$ ' . number_format($this->price);
    }

    public function getDisplayNameAttribute()
    {
        return strtoupper($this->attributes['name']);
    }
}
