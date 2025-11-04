<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $connection = 'company_12_d7c764d8_7800_4cd4_a8b3_202b5071c0de';

    protected $table = 'inv_item_house';

    protected $fillable = [
        'name',
        'status',
    ];
}
