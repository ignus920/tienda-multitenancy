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
        Schema::table('inv_detail_inv_adjustments', function (Blueprint $table) {
            
            // 1. Es CRUCIAL cambiar el tipo de dato para que coincida con el id del padre (BigInt Unsigned)
            // Nota: Si ya tienes datos, asegúrate de que no haya IDs negativos o huérfanos antes de correr esto.
            $table->unsignedBigInteger('inventoryAdjustmentId')->nullable()->change();
            $table->unsignedBigInteger('itemId')->nullable()->change();
            
            // 2. Definir las llaves foráneas
            $table->foreign('inventoryAdjustmentId')
                  ->references('id')
                  ->on('inv_inventory_adjustments')
                  ->onDelete('cascade'); // Si borras el ajuste, se borran los detalles (opcional: puedes usar 'restrict' o 'set null')

            $table->foreign('itemId')
                  ->references('id')
                  ->on('inv_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_detail_inv_adjustments', function (Blueprint $table) {
            
            // 1. **ELIMINAR AMBAS LLAVES FORÁNEAS**
            // Se usa el nombre de la columna para eliminar la FK.
            // Si Laravel no generó el nombre por defecto (ej: table_column_foreign), 
            // usar el nombre de columna funciona cuando la definiste con ->foreign('columna').
            $table->dropForeign(['inventoryAdjustmentId']);
            $table->dropForeign(['itemId']); // <--- Faltaba esta

            // 2. **REVERTIR EL TIPO DE DATO PARA AMBAS COLUMNAS**
            // Revertir el cambio de tipo de dato (asumiendo que era 'integer' normal antes)
            $table->integer('inventoryAdjustmentId')->nullable()->change();
            $table->integer('itemId')->nullable()->change(); // <--- Faltaba esta
        });
    }
};