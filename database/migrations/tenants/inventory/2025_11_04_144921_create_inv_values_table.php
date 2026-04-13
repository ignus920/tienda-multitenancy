<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_values')) {
            Schema::create('inv_values', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->dateTime('date')->useCurrent(); // datetime, default CURRENT_TIMESTAMP
                $table->double('values')->default(0);
                $table->enum('type', ["costo", "precio"]);
                $table->integer('itemId');
                $table->foreign('itemId')->references('id')->on('inv_items');
                $table->integer('warehouseId')->nullable();
                $table->enum('label', ["Costo Inicial", "Costo", "Precio Base", "Precio Regular", "Precio Crédito", "Precio unitario x caja"])->nullable();
                $table->timestamps();    // created_at, updated_at
                $table->softDeletes();   // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_values');
    }
};
