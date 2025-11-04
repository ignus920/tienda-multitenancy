<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Items extends Model
{
    use HasFactory;

    protected $connection = 'company_12_d7c764d8_7800_4cd4_a8b3_202b5071c0de';

    protected $table = 'inv_items';

    protected $fillable = [
        'name',
        'description',
        'sku',
        'type',
        'categoryId',
        'command_id',
        'internalCode',
        'brandId',
        'houseId',
        'inventoriable',
        'purchasing_unit',
        'consumption_unit',
        'status',
    ];

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId', 'id');
    }

    public function command()
    {
        return $this->belongsTo(Command::class, 'commandId', 'id');
    }
}
