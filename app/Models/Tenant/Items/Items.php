<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Items extends Model
{
    use HasFactory;

    protected $connection = 'company_1_b2c3a9df_44bf_4f62_8ff7_7fbfdbc5464e';

    protected $table = 'inv_items';

    protected $fillable = [
        'name',
        'description',
        'sku',
        'type',
        'category_id',
        'command_id',
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
