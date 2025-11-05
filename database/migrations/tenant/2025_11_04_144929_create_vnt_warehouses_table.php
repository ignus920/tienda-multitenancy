<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vnt_warehouses')) {
            Schema::create('vnt_warehouses', function (Blueprint $table) {
                $table->id('id');
                $table->bigInteger('companyId')->default(0);
                $table->string('name', 255);
                $table->string('address', 255);
                $table->string('postcode', 10)->nullable();
                $table->integer('cityId')->nullable();
                $table->integer('billingFormat')->default(16);
                $table->integer('is_credit')->default(0);
                $table->integer('termId')->default(1);
                $table->string('creditLimit', 20)->default(0);
                $table->integer('priceList')->default(1);
                $table->integer('status')->nullable()->default(1);
                $table->integer('api_data_id')->nullable();
                $table->integer('main')->nullable()->default(1);
                $table->string('branch_type')->nullable()->default('FIJA');
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_warehouses');
    }
};
