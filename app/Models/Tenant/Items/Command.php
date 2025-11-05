<?php

namespace App\Models\Tenant\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class Command extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'inv_command';

    protected $fillable = [
        'name',
        'print_path',
        'status',
    ];

}
