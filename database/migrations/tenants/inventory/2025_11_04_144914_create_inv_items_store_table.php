<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_items_store')) {
            Schema::create('inv_items_store', function (Blueprint $table) {
                $table->id('id');
                $table->integer('itemId')->nullable();
                $table->integer('storeId')->nullable();
                $table->decimal('initial_stock', 10, 2)->nullable()->default(0.00);
                $table->decimal('stock_items_store', 10, 2)->nullable()->default(0.00);
                $table->decimal('stock_min', 10, 2)->default(0.00);
                $table->decimal('stock_max', 10, 2)->default(0.00);
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
                $table->index('itemId');
                $table->index('storeId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_items_store');
    }
};
