<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_store')) {
            Schema::create('inv_store', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->string('name', 100)->default(1);
                $table->integer('warehouseId')->nullable();
                $table->integer('store_manager')->nullable();
                $table->tinyInteger('status')->default(1); // tinyint, default 1
                $table->string('api_data_id', 150)->nullable();
                $table->timestamps();        // created_at, updated_at
                $table->softDeletes();       // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_store');
    }
};
