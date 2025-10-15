<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Información de la empresa
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            // Configuración de la base de datos del tenant
            $table->string('db_name');
            $table->string('db_user')->nullable();
            $table->string('db_password')->nullable();
            $table->string('db_host')->default('127.0.0.1');
            $table->integer('db_port')->default(3306);

            // Estado y configuración
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
