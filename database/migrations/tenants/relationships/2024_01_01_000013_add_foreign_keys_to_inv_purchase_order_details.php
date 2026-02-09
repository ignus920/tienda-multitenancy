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
        Schema::table('inv_purchase_order_details', function (Blueprint $table) {
            $table->foreign('purchase_ordersId', 'inv_purchase_order_details_ibfk_1')
                ->references('id')
                ->on('inv_purchase_orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_purchase_order_details', function (Blueprint $table) {
            $table->dropForeign('inv_purchase_order_details_ibfk_1');
        });
    }
};
