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
        Schema::create('cnf_invoices', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('token', 150);
            $table->integer('id_warehouses');
            $table->integer('numeracion');
            $table->string('facturador', 50);
            $table->timestamps();        // created_at, updated_at
            $table->softDeletes();       // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_invoices');
    }
};
