<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_purchase_orders')) {
            Schema::create('inv_purchase_orders', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->string('consecutive', 50);
                $table->integer('providerId')->nullable();
                $table->integer('user')->nullable();
                $table->enum('status', ["PENDIENTE", "EN PROCESO", "RECIBIDO", "CANCELADO"]);
                $table->decimal('total', 11, 2);
                $table->text('observations');
                $table->dateTime('expected_date');
                $table->timestamps();        // created_at, updated_at
                $table->softDeletes();       // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_orders');
    }
};
