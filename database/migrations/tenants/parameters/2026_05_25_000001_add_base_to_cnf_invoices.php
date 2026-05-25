<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'base' a cnf_invoices para determinar el entorno de la API.
     * Valores: 'Produccion' → https://api.alegra.com/api/v1/
     *          'sandbox'    → https://sandbox.alegra.com:26967/api/v1
     */
    public function up(): void
    {
        Schema::table('cnf_invoices', function (Blueprint $table) {
            $table->string('base', 20)->default('Produccion')->after('facturador');
        });
    }

    public function down(): void
    {
        Schema::table('cnf_invoices', function (Blueprint $table) {
            $table->dropColumn('base');
        });
    }
};
