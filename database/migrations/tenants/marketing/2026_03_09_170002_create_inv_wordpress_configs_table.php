<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inv_wordpress_configs', function (Blueprint $table) {
            $table->id();
            $table->string('wp_url', 255)->comment('URL de la tienda WordPress');
            $table->string('wp_user', 100)->comment('Usuario de la API');
            $table->string('wp_password', 255)->comment('Application Password de WordPress');
            $table->boolean('use_wp_load')->default(false)->comment('¿Usar carga interna de wp-load.php?');
            $table->boolean('is_active')->default(true)->comment('Estado de la integración');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_wordpress_configs');
    }
};
