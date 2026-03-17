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
        if (!Schema::hasTable('imp_items_setup')) {
            Schema::create('imp_items_setup', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('itemId')->nullable();
                $table->double('percentage')->default(0);
                $table->integer('cantidad_min')->default(0);
                $table->integer('supplier_id')->nullable()->comment('Proveedor');
                $table->string('factory_ref', 200)->nullable()->comment('Referencia del proveedor');
                $table->decimal('exw', 10, 0)->nullable()->default(0)->comment('Precio dado por el proveedor');
                $table->integer('purchase_unit')->nullable()->comment('Unidad correspondiente al precio exw	');
                $table->decimal('freight_increase', 10, 0)->nullable()->default(0)->comment('Incremento por fletes');
                $table->decimal('pvp_factor', 10, 0)->nullable()->default(0);
                $table->decimal('pvp_min_factor', 10, 0)->nullable()->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_items_setup');
    }
};
