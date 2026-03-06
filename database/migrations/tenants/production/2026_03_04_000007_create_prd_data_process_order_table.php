<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_data_process_order')) {
            Schema::create('prd_data_process_order', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('production_order_id')->default(0);
                $table->foreign('production_order_id')->references('id')->on('prd_production_order');
                $table->integer('field_processId')->default(0);
                $table->foreign('field_processId')->references('id')->on('prd_fields_process');
                $table->string('field_value', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_data_process_order');
    }
};
