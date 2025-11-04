<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vnt_contacts', function (Blueprint $table) {
            $table->id('id');
            $table->string('firstName', 255)->nullable();
            $table->string('secondName', 255)->nullable();
            $table->string('lastName', 255)->nullable();
            $table->string('secondLastName', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('business_phone', 100)->nullable();
            $table->string('personal_phone', 100)->nullable();
            $table->integer('status')->nullable()->default(1);
            $table->integer('api_data_id')->nullable();
            $table->integer('warehouseId')->nullable();
            $table->integer('positionId')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_contacts');
    }
};
