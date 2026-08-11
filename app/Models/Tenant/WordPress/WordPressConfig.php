<?php

namespace App\Models\Tenant\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WordPressConfig extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'inv_wordpress_configs';

    protected $fillable = [
        'wp_url',
        'wp_user',
        'wp_password',
        'use_wp_load',
        'is_active'
    ];

    protected $casts = [
        'use_wp_load' => 'boolean',
        'is_active' => 'boolean',
    ];
}
