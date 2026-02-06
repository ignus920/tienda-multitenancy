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
        Schema::table('inv_inventory_adjustments', function (Blueprint $table) {
            $table->foreign('reasonId', 'inv_inventory_adjustments_ibfk_1')
                ->references('id')
                ->on('inv_reasons')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_inventory_adjustments', function (Blueprint $table) {
            $table->dropForeign('inv_inventory_adjustments_ibfk_1');
        });
    }
};
