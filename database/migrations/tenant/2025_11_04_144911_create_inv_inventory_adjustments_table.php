<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_inventory_adjustments', function (Blueprint $table) {
            $table->id('id');
            $table->dateTime('date');
            $table->text('observations');
            $table->string('type', 255);
            $table->integer('status')->nullable()->default(1);
            $table->string('api_data_id', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('warehouseId')->nullable()->default(1);
            $table->integer('reasonId')->nullable();
            $table->integer('consecutive');
            $table->integer('userId');
            $table->index('reasonId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_inventory_adjustments');
    }
};
