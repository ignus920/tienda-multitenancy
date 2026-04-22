<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_remissions')) {
            Schema::create('inv_remissions', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('consecutive');
                $table->enum('status', ["REGISTRADO", "ALISTAMIENTO", "EN RECORRIDO", "ENTREGADO", "DEVUELTO", "ANULADO", "VENCIDO"])->default('REGISTRADO');
                $table->integer('quoteId')->nullable();
                $table->integer('warehouseId')->nullable();
                $table->integer('deliveryTypeId')->nullable();
                $table->integer('methodPaymentId')->nullable();
                $table->integer('userId');
                $table->dateTime('deliveryDate')->nullable();
                $table->integer('delivery_id')->nullable();
                $table->integer('expiration')->nullable();
                $table->integer('modify')->nullable();
                $table->text('observations_return')->nullable();
                $table->integer('flete')->nullable()->default(0);
                $table->index('quoteId');
                $table->index('warehouseId');
                $table->timestamps();        // created_at, updated_at
                $table->softDeletes();       // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_remissions');
    }
};
