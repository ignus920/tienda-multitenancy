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
        Schema::create('vnt_warehouses', function (Blueprint $table) {
           $table->id(); // Crea un BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            // Columna de Clave Foránea (companyId: INT NOT NULL DEFAULT 0)
            $table->integer('companyId')->default(0);
            $table->index('companyId'); // Índice para la clave foránea
            // Columnas de Texto Requeridas (VARCHAR(255) NOT NULL)
            $table->string('name', 255)->nullable(false); // NOT NULL
            $table->string('address', 255)->nullable(false); // NOT NULL
            // Columna de Código Postal (VARCHAR(10) NULL)
            $table->string('postcode', 10)->nullable();
            // Claves Foráneas (cityId: INT NULL)
            $table->integer('cityId')->nullable();
            $table->index('cityId'); // Índice para potencial Foreign Key
            // Configuración de Facturación y Crédito
            // billingFormat: INT NOT NULL DEFAULT 16
            $table->integer('billingFormat')->nullable(false)->default(16);
            // is_credit: INT NOT NULL DEFAULT 0
            $table->integer('is_credit')->nullable(false)->default(0);
            // termId: INT NOT NULL DEFAULT 0 con Comentario
            $table->integer('termId')->nullable(false)->default(0)->comment('forma de pago');
            $table->index('termId'); // Índice para potencial Foreign Key
            // creditLimit: VARCHAR(20) NOT NULL DEFAULT 0 con Comentario
            $table->string('creditLimit', 20)->nullable(false)->default('0')->comment('cupo de credito');
            // priceList: INT NOT NULL DEFAULT 1 con Comentario
            $table->integer('priceList')->nullable(false)->default(1)->comment('lista de precio asignada');
            $table->index('priceList'); // Índice para potencial Foreign Key
            // Estado y API
            // status: TINYINT NULL DEFAULT 1
            $table->tinyInteger('status')->nullable()->default(1);
            // api_data_id: INT NULL
            $table->integer('api_data_id')->nullable();
            // main: TINYINT NULL DEFAULT 1
            $table->tinyInteger('main')->nullable()->default(1);
            // Columna de Tipo de Sucursal (ENUM)
            // branch_type: ENUM NOT NULL DEFAULT 'FIJA' con Comentario
            $table->enum('branch_type', ['FIJA', 'DESPACHO'])
                  ->nullable(false)
                  ->default('FIJA')
                  ->comment('DESPACHO = se crea en un pedido');

            // Timestamps
            // createdAt: DATETIME NOT NULL
            $table->dateTime('createdAt')->nullable(false)->useCurrent(); // DATETIME NOT NULL
            
            // updatedAt: DATETIME NULL
            $table->dateTime('updatedAt')->nullable(); // DATETIME NULL
            
            // deletedAt: DATETIME NULL (borrado lógico)
            $table->softDeletes('deletedAt'); // DATETIME NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vnt_warehouses');
    }
};
