<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_purchase_order_details', function (Blueprint $table) {
            $table->id('id');
            $table->integer('purchase_ordersId');
            $table->integer('itemId')->nullable();
            $table->integer('quantity_ordered')->nullable();
            $table->integer('tax')->nullable();
            $table->index('purchase_ordersId');
            $table->index('itemId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_order_details');
    }
};
