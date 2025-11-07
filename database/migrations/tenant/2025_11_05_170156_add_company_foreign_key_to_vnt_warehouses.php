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
        // Solo agregar la clave foránea si no existe
        // La columna companyId ya es unsignedBigInteger
        Schema::table('vnt_warehouses', function (Blueprint $table) {
            $table->foreign('companyId')
                  ->references('id')
                  ->on('vnt_companies')
                  ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vnt_warehouses', function (Blueprint $table) {
            $table->dropForeign(['companyId']);
        });
    }
};
