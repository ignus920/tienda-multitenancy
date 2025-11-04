<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cnf_taxes', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 255);
            $table->decimal('percentage', 10, 2);
            $table->integer('status')->default(1);
            $table->integer('api_data_id')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('inventoryAccount');
            $table->integer('inventariablePurchaseAccount');
            $table->integer('categoryAccount');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnf_taxes');
    }
};
