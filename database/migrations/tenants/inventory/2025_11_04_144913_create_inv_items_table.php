<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_items')) {
            Schema::create('inv_items', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('api_data_id')->nullable()->comment('id de integracion');
                $table->integer('categoryId')->nullable();
                $table->foreign('categoryId')->references('id')->on('inv_categories');
                $table->string('name', 255);
                $table->string('internal_code', 100);
                $table->string('sku', 255);
                $table->text('description')->nullable();
                $table->enum('type', ["COMBO", "COMPRA NACIONAL", "IMPORTADO", "PRODUCIDO"]);
                $table->integer('taxId')->default(2);
                $table->integer('commandId')->nullable();
                $table->foreign('commandId')->references('id')->on('inv_command');
                $table->integer('brandId')->nullable();
                $table->foreign('brandId')->references('id')->on('inv_item_brand');
                $table->integer('houseId')->nullable();
                $table->foreign('houseId')->references('id')->on('inv_item_house');
                $table->tinyInteger('inventoriable')->default(1)->comment('1=SI 0=NO');
                $table->integer('purchasing_unit')->nullable()->default(0);
                $table->foreign('purchasing_unit')->references('id')->on('inv_unit_measurements');
                $table->integer('consumption_unit')->nullable()->default(0);
                $table->foreign('consumption_unit')->references('id')->on('inv_unit_measurements');
                $table->tinyInteger('handles_serial')->default(0);
                $table->tinyInteger('status')->default(1); // tinyint, default 1
                $table->tinyInteger('generic')->default(0)->comment('1=SI 0=NO');
                $table->timestamps();        // created_at, updated_at
                $table->softDeletes();       // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_items');
    }
};
