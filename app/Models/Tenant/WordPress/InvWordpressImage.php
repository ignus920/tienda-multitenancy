<?php

namespace App\Models\Tenant\WordPress;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Tenant\Items\Items;

class InvWordpressImage extends Model
{
    protected $connection = 'tenant';
    protected $table = 'inv_wordpress_images';

    protected $fillable = [
        'image_id',
        'itemId',
        'wp_media_id',
        'sync_to_wp',
    ];

    protected $casts = [
        'sync_to_wp' => 'boolean',
    ];

    /**
     * Relación con la imagen de la galería.
     */
    public function galleryImage()
    {
        return $this->belongsTo(ImageGallery::class, 'image_id', 'id');
    }

    /**
     * Relación con el producto.
     */
    public function item()
    {
        return $this->belongsTo(Items::class, 'itemId', 'id');
    }
}
