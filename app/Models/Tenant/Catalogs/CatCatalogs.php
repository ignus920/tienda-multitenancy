<?php

namespace App\Models\Tenant\Catalogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatCatalogs extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'cat_catalogs';

    protected $fillable = [
        'family',
        'title',
        'file_name',
        'link',
        'login',
    ];
}
