<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_detail_inv_adjustments')) {
            Schema::create('inv_detail_inv_adjustments', function (Blueprint $table) {
                $table->unsignedInteger('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('quantity')->default(0);
                $table->double('cost')->nullable()->default(0);
                $table->integer('inventoryAdjustmentId')->nullable();
                $table->integer('itemId')->nullable();
                $table->integer('unitMeasurementId')->nullable();
                $table->index('inventoryAdjustmentId');
                $table->index('itemId');
                $table->timestamps();        // created_at, updated_at  
                $table->softDeletes();       // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_detail_inv_adjustments');
    }
};
