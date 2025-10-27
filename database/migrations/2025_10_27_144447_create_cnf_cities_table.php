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
        Schema::create('cnf_cities', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('cons')->nullable();
            $table->string('cod_ciudad')->nullable();
            $table->string('cod_departamento')->nullable();
            $table->string('departamento', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_cities');
    }
};
