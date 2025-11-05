<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id('id');
            $table->integer('api_data_id')->nullable();
            $table->integer('categoryId')->nullable();
            $table->string('name', 255);
            $table->string('internal_code', 100);
            $table->string('sku', 255);
            $table->text('description')->nullable();
            $table->string('type');
            $table->integer('commandId')->nullable();
            $table->integer('brandId')->nullable();
            $table->integer('houseId')->nullable();
            $table->integer('inventoriable')->default(1);
            $table->integer('purchasing_unit')->nullable()->default(0);
            $table->integer('consumption_unit')->nullable()->default(0);
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('categoryId');
            $table->index('commandId');
            $table->index('brandId');
            $table->index('houseId');
            $table->index('purchasing_unit');
            $table->index('consumption_unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_items');
    }
};
