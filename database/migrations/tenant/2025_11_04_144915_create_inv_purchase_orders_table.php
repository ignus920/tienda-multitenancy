<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_purchase_orders', function (Blueprint $table) {
            $table->id('id');
            $table->string('consecutive', 50);
            $table->integer('providerId')->nullable();
            $table->integer('user')->nullable();
            $table->string('status');
            $table->decimal('total', 10, 2);
            $table->text('observations');
            $table->dateTime('expected_date');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_orders');
    }
};
