<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_material_items')) {
            Schema::create('prd_material_items', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->double('qty')->nullable();
                $table->integer('unit_measurement')->nullable();
                // material_itemId referencia inv_items (módulo externo, sin FK constraint)
                $table->integer('material_itemId')->nullable();
                $table->integer('production_order_id')->nullable();
                $table->foreign('production_order_id')->references('id')->on('prd_production_order');
                $table->integer('process_id')->nullable();
                $table->foreign('process_id')->references('id')->on('prd_process');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_material_items');
    }
};
