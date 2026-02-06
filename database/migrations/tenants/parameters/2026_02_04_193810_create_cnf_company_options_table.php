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
        Schema::create('cnf_company_options', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable()->comment('ID de empresa (sin FK)');
            $table->integer('option_id')->nullable()->comment('ID de opción (sin FK)');
            $table->integer('value')->default(0)->comment('1=habilitado, 0=deshabilitado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_company_options');
    }
};
