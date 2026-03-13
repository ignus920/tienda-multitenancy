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
        Schema::create('imp_packing', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
            $table->string('number_packing', 100);
            $table->integer('shipping_id')->nullable()->comment();
            $table->foreign('shipping_id')->references('id')->on('imp_shippments');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_packing');
    }
};
