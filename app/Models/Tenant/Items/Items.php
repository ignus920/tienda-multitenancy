<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Brand;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\CnfTaxes;
use App\Models\Tenant\Items\CnfPricelist;
use App\Traits\HasCompanyConfiguration;

class Items extends Model
{
    use HasFactory, SoftDeletes, HasCompanyConfiguration;

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
        'handles_serial',
    ];

    /**
     * Variable estática para controlar si la configuración ya fue inicializada
     */
    private static $configurationInitialized = false;

    /**
     * Inicializar configuración cuando se carga el modelo (solo una vez por request)
     */
    protected static function booted()
    {
        static::retrieved(function ($item) {
            if (!self::$configurationInitialized) {
                $item->initializeCompanyConfiguration();
                self::$configurationInitialized = true;
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brandId', 'id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'houseId', 'id');
    }

    public function purchasingUnit()
    {
        return $this->belongsTo(UnitMeasurements::class, 'purchasing_unit', 'id');
    }

    public function consumptionUnit()
    {
        return $this->belongsTo(UnitMeasurements::class, 'consumption_unit', 'id');
    }

    public function tax()
    {
        return $this->belongsTo(CnfTaxes::class, 'taxId', 'id');
    }

    public function invValues()
    {
        return $this->hasMany(InvValues::class, 'itemId', 'id');
    }
    public function invItemsStore()
    {
        return $this->hasMany(InvItemsStore::class, 'itemId', 'id');
    }

    /**
     * Relación con la configuración de importación
     */
    public function importSetup()
    {
        return $this->hasOne(\App\Models\Tenant\Imports\ImpItemsSetup::class, 'item_id', 'id');
    }

    /**
     * Relación con los detalles de ajustes de inventario
     */
    public function inventoryAdjustmentDetails()
    {
        return $this->hasMany(\App\Models\Tenant\Movements\InvDetailInventoryAdjustment::class, 'itemId', 'id');
    }

    /**
     * Relación con cantidades no confirmadas
     */
    public function unconfirmedQuantities()
    {
        return $this->hasMany(\App\Models\Tenant\Imports\InvUnconfirmedQty::class, 'item_id', 'id');
    }

    /**
     * Relación con la galería de imágenes
     * Un item puede tener múltiples imágenes
     */
    public function imageGallery()
    {
        return $this->hasMany(ImageGallery::class, 'itemId', 'id');
    }

    /**
     * Obtener solo imágenes activas (no eliminadas)
     */
    public function activeImages()
    {
        return $this->hasMany(ImageGallery::class, 'itemId', 'id')
            ->whereNull('deleted_at');
    }

    /**
     * Obtener la imagen principal del item
     */
    public function principalImage()
    {
        return $this->hasOne(ImageGallery::class, 'itemId', 'id')
            ->where('type', 'PRINCIPAL')
            ->whereNull('deleted_at');
    }

    /**
     * Obtener URL de la imagen principal
     * 
     * @return string URL de la imagen o placeholder
     */
    public function getPrincipalImageUrl()
    {
        $principalImage = $this->principalImage;

        if ($principalImage) {
            return $principalImage->getImageUrl();
        }

        return asset('images/placeholder-item.png');
    }

    /**
     * Obtener URL del thumbnail de la imagen principal
     * 
     * @return string URL del thumbnail o placeholder
     */
    public function getPrincipalThumbnailUrl()
    {
        $principalImage = $this->principalImage;

        if ($principalImage) {
            return $principalImage->getThumbnailUrl();
        }

        return asset('images/placeholder-item.png');
    }

    /**
     * Obtener todas las imágenes de la galería (sin la principal)
     */
    public function getGalleryImages()
    {
        return $this->imageGallery()
            ->where('type', 'GALERIA')
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * Contar imágenes activas (sin contar la principal)
     * 
     * @return int Número de imágenes en galería
     */
    public function getGalleryImagesCount()
    {
        return $this->imageGallery()
            ->where('type', 'GALERIA')
            ->whereNull('deleted_at')
            ->count();
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
        // Verificar si usa lista de precios (opción 4)
        $usePriceList = $this->getOptionValue(4) == 1;

        if ($usePriceList) {
            // MODO LISTA DE PRECIOS: Precio Base × multiplicador
            return $this->getPriceWithPriceList();
        } else {
            // MODO PRECIOS FIJOS: Usar precios directos de inv_values
            return $this->getPriceFromInventory();
        }
    }

    /**
     * Obtiene el precio usando lista de precios (Precio Base × multiplicador)
     */
    private function getPriceWithPriceList()
    {
        // Usar la relación cargada (colección) en lugar de disparar una nueva consulta
        $basePriceRecord = $this->invValues
            ->where('type', 'precio')
            ->where('label', 'Precio Base')
            ->sortByDesc('date')
            ->sortByDesc('created_at')
            ->first();

        if (!$basePriceRecord) {
            return 0;
        }

        // Obtener la lista de precios activa (Cacheada estáticamente por request)
        static $cachedPriceList = null;
        if ($cachedPriceList === null) {
            $cachedPriceList = CnfPricelist::active()->first() ?: false;
        }
        $priceList = $cachedPriceList === false ? null : $cachedPriceList;

        if (!$priceList) {
            // Sin multiplicador, pero aún aplicar IVA si existe
            $taxPercentage = 0;
            if ($this->tax) {
                $taxPercentage = $this->tax->percentage / 100;
            }
            return $basePriceRecord->values * (1 + $taxPercentage);
        }

        // Obtener el porcentaje de IVA del item
        $taxPercentage = 0;
        if ($this->tax) {
            $taxPercentage = $this->tax->percentage / 100; // Convertir a decimal (19% = 0.19)
        }

        // Aplicar fórmula: precio_base * factor_lista * (1 + porcentaje_iva)
        $priceWithoutIva = $basePriceRecord->values * $priceList->value;
        $priceWithIva = $priceWithoutIva * (1 + $taxPercentage);

        return $priceWithIva;
    }

    /**
     * Obtiene el precio directamente de inv_values
     */
    private function getPriceFromInventory()
    {
        // Buscar el precio más reciente usando la colección cargada
        $priceRecord = $this->invValues
            ->where('type', 'precio')
            ->sortByDesc('date')
            ->sortByDesc('created_at')
            ->first();

        return $priceRecord ? $priceRecord->values : 0;
    }

    public function getFormattedPriceAttribute()
    {
        return '$ ' . number_format($this->price);
    }

    /**
     * Obtiene TODOS los precios disponibles del producto
     * Retorna un array con los precios según la configuración
     */
    public function getAllPricesAttribute()
    {
        // Verificar si usa lista de precios (opción 4)
        $usePriceList = $this->getOptionValue(4) == 1;

        if ($usePriceList) {
            // MODO LISTA DE PRECIOS: Precio Base × cada multiplicador
            return $this->getAllPricesWithPriceList();
        } else {
            // MODO PRECIOS FIJOS: Todos los precios de inv_values
            return $this->getAllPricesFromInventory();
        }
    }

    /**
     * Obtiene todos los precios usando listas de precios
     * Retorna: ['P1' => 100000, 'P2' => 90000, 'Precio Regular' => 95000, 'Precio Crédito' => 98000, ...]
     */
    private function getAllPricesWithPriceList()
    {
        // Usar la relación cargada (colección)
        $basePriceRecord = $this->invValues
            ->where('type', 'precio')
            ->where('label', 'Precio Base')
            ->sortByDesc('date')
            ->sortByDesc('created_at')
            ->first();

        if (!$basePriceRecord) {
            return [];
        }

        $basePrice = $basePriceRecord->values;
        $prices = [];

        // Obtener el porcentaje de IVA del item
        $taxPercentage = 0;
        if ($this->tax) {
            $taxPercentage = $this->tax->percentage / 100; // Convertir a decimal (19% = 0.19)
        }

        // Obtener TODAS las listas de precios activas (Cacheada estáticamente por request)
        static $cachedActiveLists = null;
        if ($cachedActiveLists === null) {
            $cachedActiveLists = CnfPricelist::active()->get();
        }
        $priceLists = $cachedActiveLists;

        foreach ($priceLists as $priceList) {
            // Aplicar fórmula: precio_base * factor_lista * (1 + porcentaje_iva)
            $priceWithoutIva = $basePrice * $priceList->value;
            $priceWithIva = $priceWithoutIva * (1 + $taxPercentage);
            $prices[$priceList->title] = $priceWithIva;
        }

        // Obtener Regular y Crédito desde la colección cargada
        $precioRegular = $this->invValues
            ->where('type', 'precio')
            ->where('label', 'Precio Regular')
            ->sortByDesc('date')
            ->sortByDesc('created_at')
            ->first();

        $regularPriceValue = null;
        if ($precioRegular) {
            $regularPriceValue = $precioRegular->values;
            $prices['Precio Regular'] = $regularPriceValue;
        }

        $precioCredito = $this->invValues
            ->where('type', 'precio')
            ->where('label', 'Precio Crédito')
            ->sortByDesc('date')
            ->sortByDesc('created_at')
            ->first();

        if ($precioCredito) {
            $prices['Precio Crédito'] = $precioCredito->values;
        }

        // Aplicar filtros: remover precios con valor 0 y precios menores al precio regular
        $filteredPrices = [];
        foreach ($prices as $label => $value) {
            // Excluir precios con valor 0
            if ($value <= 0) {
                continue;
            }

            // Si hay precio regular definido y no es el precio regular mismo,
            // excluir precios menores al precio regular
            if ($regularPriceValue !== null && $label !== 'Precio Regular' && $value < $regularPriceValue) {
                continue;
            }

            $filteredPrices[$label] = $value;
        }

        return $filteredPrices;
    }

    /**
     * Obtiene todos los precios directamente de inv_values
     * Retorna: ['Precio Base' => 100000, 'Precio Regular' => 95000, ...]
     */
    private function getAllPricesFromInventory()
    {
        // Obtener TODOS los precios desde la colección cargada
        $priceRecords = $this->invValues
            ->where('type', 'precio')
            ->sortByDesc('date')
            ->sortByDesc('created_at');

        $prices = [];

        // Agrupar por label y tomar solo el primero (más reciente) de cada grupo
        foreach ($priceRecords->groupBy('label') as $label => $records) {
            $prices[$label] = $records->first()->values;
        }

        // Encontrar el precio regular para usar como referencia
        $regularPriceValue = $prices['Precio Regular'] ?? null;

        // Aplicar filtros: remover precios con valor 0 y precios menores al precio regular
        $filteredPrices = [];
        foreach ($prices as $label => $value) {
            // Excluir precios con valor 0
            if ($value <= 0) {
                continue;
            }

            // Si hay precio regular definido y no es el precio regular mismo,
            // excluir precios menores al precio regular
            if ($regularPriceValue !== null && $label !== 'Precio Regular' && $value < $regularPriceValue) {
                continue;
            }

            $filteredPrices[$label] = $value;
        }

        return $filteredPrices;
    }

    public function getDisplayNameAttribute()
    {
        return strtoupper($this->attributes['name']);
    }
}
