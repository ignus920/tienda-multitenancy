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
        Schema::table('inv_inventory_count', function (Blueprint $table) {
            $table->foreign('itemId', 'inv_inventory_count_ibfk_1')
                ->references('id')
                ->on('inv_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_inventory_count', function (Blueprint $table) {
            $table->dropForeign('inv_inventory_count_ibfk_1');
        });
    }
};
