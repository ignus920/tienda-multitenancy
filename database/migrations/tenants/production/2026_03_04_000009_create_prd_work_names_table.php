<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_work_names')) {
            Schema::create('prd_work_names', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('prd_order_id')->nullable();
                $table->foreign('prd_order_id')->references('id')->on('prd_production_order');
                $table->string('work_name', 255)->default('Razón social');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_work_names');
    }
};
