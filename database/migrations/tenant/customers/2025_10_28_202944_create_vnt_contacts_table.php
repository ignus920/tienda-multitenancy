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
        Schema::create('vnt_contacts', function (Blueprint $table) {
           // Clave Primaria y AUTO_INCREMENT (id: INT NOT NULL AUTO_INCREMENT)
            $table->id(); // Crea un INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            // Columnas de Texto
            // VARCHAR(255) NULL
            $table->string('firstName', 255)->nullable();
            $table->string('secondName', 255)->nullable();
            $table->string('lastName', 255)->nullable();
            $table->string('secondLastName', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('business_phone', 100)->nullable();
            $table->string('personal_phone', 100)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);

            // Claves Foráneas (Potenciales) / Índices
            // INT NULL
            $table->integer('api_data_id')->nullable();
            $table->integer('warehouseId')->nullable();
            $table->integer('positionId')->nullable()->comment('cargo del contacto');
            $table->index('positionId'); // Creamos un índice para mejorar las búsquedas o para una FK
            // Timestamps (createdAt, updatedAt, deletedAt)
            $table->timestamps(); // Crea las columnas 'created_at' y 'updated_at' (DATETIME NOT NULL)
            // deletedAt: DATETIME NULL (gestionado por softDeletes())
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnt_contacts');
    }
};
