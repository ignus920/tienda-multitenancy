<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_items_accessories')) {
            Schema::create('inv_items_accessories', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('item');
                $table->foreign('item')->references('id')->on('inv_items');
                $table->integer('insumo');
                $table->foreign('insumo')->references('id')->on('inv_items');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_items_accessories');
    }
};
