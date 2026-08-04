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
        'subtitle',
        'badge_text',
        'overlay_color',
        'text_position',
        'button_color',
        'button_text_color',
        'text_color',
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
