<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_production_order')) {
            Schema::create('prd_production_order', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->dateTime('date')->nullable();
                // item_id referencia inv_items (módulo externo, sin FK constraint)
                $table->integer('item_id')->nullable()->default(0);
                $table->double('requested_qty')->nullable()->default(0);
                // warehouse_customer_id referencia vnt_contacts (módulo externo, sin FK constraint)
                $table->integer('warehouse_customer_id')->nullable()->default(0);
                $table->string('customer_order', 255)->nullable()->default('# de orden');
                $table->dateTime('delivery_date')->nullable();
                // productive_stage apuntará a prd_process_item una vez creada esa tabla
                $table->integer('productive_stage')->nullable();
                $table->integer('status')->default(1);
                $table->string('sales_order', 100)->nullable()->default('# de pedido');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_production_order');
    }
};
