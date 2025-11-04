<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class Command extends Model
{
    use HasFactory;

    protected $connection = 'company_12_d7c764d8_7800_4cd4_a8b3_202b5071c0de';

    protected $table = 'inv_command';

    protected $fillable = [
        'name',
        'print_path',
        'status',
    ];

}
