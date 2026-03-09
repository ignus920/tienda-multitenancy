<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations on the tenant database.
     */
    public function up(): void
    {
        // 1. Crear la nueva tabla para sincronización con WordPress (Módulo Marketing)
        Schema::create('inv_wordpress_images', function (Blueprint $table) {
            $table->id();
            $table->integer('image_id')->unsigned()->comment('ID de la imagen en inv_image_gallery');
            $table->integer('itemId')->unsigned()->comment('ID del producto en inv_items');
            $table->bigInteger('wp_media_id')->unsigned()->nullable()->comment('ID de la imagen en WordPress');
            $table->boolean('sync_to_wp')->default(false)->comment('¿Sincronizar esta imagen con WordPress?');
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index('image_id');
            $table->index('itemId');
        });

        // NOTA: No eliminamos las columnas de inv_image_gallery aquí automáticamente 
        // para dar tiempo a migrar los datos si el usuario lo desea.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_wordpress_images');
    }
};
