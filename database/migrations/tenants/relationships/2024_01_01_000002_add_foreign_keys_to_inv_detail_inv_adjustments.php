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
        if (!Schema::hasTable('inv_detail_inv_adjustments')) {
            return;
        }
        Schema::table('inv_detail_inv_adjustments', function (Blueprint $table) {
            $table->foreign('inventoryAdjustmentId', 'inv_detail_inv_adjustments_ibfk_1')
                ->references('id')
                ->on('inv_inventory_adjustments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('inv_detail_inv_adjustments')) {
            return;
        }
        Schema::table('inv_detail_inv_adjustments', function (Blueprint $table) {
            $table->dropForeign('inv_detail_inv_adjustments_ibfk_1');
        });
    }
};
