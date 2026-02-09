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
        if (!Schema::hasTable('vnt_companies_routes')) {
            Schema::create('vnt_companies_routes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->comment('id del cliente');
                $table->unsignedBigInteger('route_id')->nullable()->comment('ruta');
                $table->integer('sales_order')->nullable()->comment('orden en el que se hace el recorrido de ventas');
                $table->integer('delivery_order')->nullable()->comment('orden en el que se hace el recorrido de entregas');
                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('company_id')->references('id')->on('vnt_companies')->onDelete('cascade');
                $table->foreign('route_id')->references('id')->on('vnt_routes')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnt_companies_routes');
    }
};
