<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_process_item')) {
            Schema::create('prd_process_item', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('processId')->nullable();
                $table->foreign('processId')->references('id')->on('prd_process');
                // itemId referencia inv_items (módulo externo, sin FK constraint)
                $table->integer('itemId')->nullable();
                $table->integer('process_route_order')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_process_item');
    }
};
