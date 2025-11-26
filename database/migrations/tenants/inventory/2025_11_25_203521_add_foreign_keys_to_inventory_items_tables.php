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
        // 1. Corregir y relacionar 'inv_items_store'
        Schema::table('inv_items_store', function (Blueprint $table) {
            // PASO CRÍTICO: Convertir las columnas para que coincidan con los IDs de Laravel (BigInt Unsigned)
            // Usamos ->change() para modificar la columna existente.
            $table->unsignedBigInteger('itemId')->change();
            $table->unsignedBigInteger('storeId')->change();

            // AHORA SÍ creamos las foráneas
            $table->foreign('itemId')
                  ->references('id')
                  ->on('inv_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('storeId')
                  ->references('id')
                  ->on('inv_store')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        // 2. Corregir y relacionar 'inv_items_locations'
        Schema::table('inv_items_locations', function (Blueprint $table) {
            // PASO CRÍTICO: Conversión de tipos
            $table->unsignedBigInteger('itemId')->change();
            $table->unsignedBigInteger('storeId')->change();

            // Creación de foráneas
            $table->foreign('itemId')
                  ->references('id')
                  ->on('inv_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('storeId')
                  ->references('id')
                  ->on('inv_store')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_items_store', function (Blueprint $table) {
            $table->dropForeign(['itemId']);
            $table->dropForeign(['storeId']);
            // Opcional: Revertir el tipo de dato si fuera estrictamente necesario,
            // pero usualmente se deja como unsignedBigInteger.
        });

        Schema::table('inv_items_locations', function (Blueprint $table) {
            $table->dropForeign(['itemId']);
            $table->dropForeign(['storeId']);
        });
    }
};