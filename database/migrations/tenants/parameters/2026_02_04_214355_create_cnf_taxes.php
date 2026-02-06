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
        Schema::create('cnf_taxes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 255);
            $table->double('percentage');
            $table->tinyInteger('status')->default(1);
            $table->integer('api_data_id')->nullable();
            $table->integer('inventoryAccount');
            $table->integer('inventariablePurchaseAccount');
            $table->integer('categoryAccount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_taxes');
    }
};
