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
        Schema::create('vnt_companies', function (Blueprint $table) {
            $table->id();
            // Columnas de Nombre y Contacto (VARCHAR(255) NULL)
            $table->string('businessName', 255)->nullable();
            $table->string('billingEmail', 255)->nullable();
            $table->string('firstName', 255)->nullable();
            $table->string('lastName', 255)->nullable();
            $table->string('secondLastName', 255)->nullable();
            $table->string('secondName', 255)->nullable();
            // Columna de Identificación
            $table->string('identification', 15)->unique()->nullable();
            $table->tinyInteger('checkDigit')->nullable()->comment('digito de verificacion');
            // Columna de Estado (status: TINYINT NULL DEFAULT 1)
            $table->tinyInteger('status')->nullable()->default(1);
            // Columnas de Claves (INT NULL)
            $table->integer('integrationDataId')->nullable();
            $table->integer('typePerson')->nullable();
            // -------------------------------------------------------------------
            // CLAVE FORÁNEA: Tipo de Identificación
            // Usa unsignedBigInteger para coincidir con el 'id' de cnf_type_identifications
            $table->unsignedBigInteger('typeIdentificationId')->nullable();
            $table->foreign('typeIdentificationId')
                  ->references('id')
                  ->on('cnf_type_identifications')
                  ->onDelete('set null'); // Si se borra la identificación, se pone NULL
            // INT NULL con Índice (Si 'regimeId' es una FK, debería ser unsignedBigInteger también)
            $table->integer('regimeId')->nullable()->index(); 
            // CLAVE FORÁNEA: Responsabilidad Fiscal
            // Usa unsignedBigInteger para coincidir con el 'id' de cnf_fiscal_responsabilities
            $table->unsignedBigInteger('fiscalResponsibilityId')->nullable();
            $table->foreign('fiscalResponsibilityId')
                  ->references('id')
                  ->on('cnf_fiscal_responsabilities')
                  ->onDelete('set null'); // Si se borra la responsabilidad, se pone NULL
            // -------------------------------------------------------------------
            // Columna de Código CIIU (VARCHAR(255) NULL)
            $table->string('code_ciiu', 255)->nullable();
            // Timestamps
            $table->dateTime('createdAt')->nullable(false)->useCurrent();
            $table->dateTime('updatedAt')->nullable();
            $table->softDeletes('deletedAt');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnt_companies');
    }
};
