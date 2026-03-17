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
        Schema::create('inv_items_dimensions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
            $table->integer('item_id')->nullable();
            $table->foreign('item_id')->references('id')->on('inv_items');
            $table->decimal('high', 10, 2)->nullable()->default(0.00);
            $table->decimal('long', 10, 2)->nullable()->default(0.00);
            $table->decimal('width', 10, 2)->nullable()->default(0.00);
            $table->decimal('voltage', 10, 2)->nullable()->default(0.00);
            $table->decimal('power', 10, 2)->nullable()->default(0.00);
            $table->decimal('weight', 10, 2)->nullable()->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_items_dimensions');
    }
};
