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
        Schema::table('customers', function (Blueprint $table) {
            // Remover el campo city string y agregar referencias geográficas
            $table->dropColumn('city');

            // Agregar campos de referencia geográfica
            $table->unsignedBigInteger('country_id')->nullable()->after('address');
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');

            // Nota: No agregamos foreign keys porque apuntan a la base central
            // y estamos en base de tenant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Restaurar campo city
            $table->string('city', 100)->nullable()->after('address');

            // Remover campos geográficos
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
        });
    }
};