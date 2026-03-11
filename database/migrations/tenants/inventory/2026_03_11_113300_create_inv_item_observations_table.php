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
        if (!Schema::hasTable('inv_item_observations')) {
            Schema::create('inv_item_observations', function (Blueprint $table) {
                // Según el SQL proporcionado por el usuario
                $table->id(); // bigint UNSIGNED, PK
                $table->unsignedBigInteger('item_id');
                $table->text('observations')->nullable();
                $table->text('technical_specifications')->nullable();
                $table->text('commercial_observations')->nullable();
                $table->integer('status')->default(1);
                
                // datetime NOT NULL, etc (Laravel asume timestamps con precision)
                $table->timestamps(); // created_at, updated_at
                $table->softDeletes(); // deleted_at
                
                // Nota: El usuario puede preferir una relación formal si inv_items.id fuera bigint,
                // Pero como es INT en el archivo visto, lo dejamos como unsignedBigInteger según el SQL solicitado.
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_item_observations');
    }
};
