<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_items_locations', function (Blueprint $table) {
            $table->id('id');
            $table->integer('itemId')->nullable();
            $table->integer('storeId')->nullable();
            $table->integer('locationId')->nullable();
            $table->decimal('stock_item_location', 10, 2)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('itemId');
            $table->index('storeId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_items_locations');
    }
};
