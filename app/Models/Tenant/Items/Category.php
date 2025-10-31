<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class Category extends Model

{
    use HasFactory;

    protected $connection = 'company_1_b2c3a9df_44bf_4f62_8ff7_7fbfdbc5464e';

    protected $table = 'inv_categories';

    protected $fillable = ['id','name', 'status'];

    public function items()
    {
        return $this->hasMany(Items::class, 'categoryId', 'id');
    }
}
