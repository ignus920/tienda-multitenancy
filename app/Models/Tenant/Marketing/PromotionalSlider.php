<?php

namespace App\Models\Tenant\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalSlider extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_promotional_sliders';

    protected $fillable = [
        'title',
        'image_path',
        'action_button_text',
        'action_url',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];
}
