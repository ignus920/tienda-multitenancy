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
        if (!Schema::hasTable('vnt_routes')) {
        Schema::create('vnt_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->string('sale_day')->nullable(); // Día de venta (lunes, martes, etc.)
            $table->string('delivery_day')->nullable(); // Día de entrega
            $table->timestamps();

            // Foreign keys
            $table->foreign('zone_id')->references('id')->on('vnt_zones')->onDelete('set null');
            // salesman_id referencia a la tabla users en la BD central, no se puede crear FK aquí
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnt_routes');
    }
};
