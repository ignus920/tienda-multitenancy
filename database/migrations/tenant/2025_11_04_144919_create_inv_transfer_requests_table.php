<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_transfer_requests', function (Blueprint $table) {
            $table->id('id');
            $table->string('status')->nullable()->default('REGISTRADO');
            $table->string('date', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('quoteId')->nullable();
            $table->integer('warehouseId')->nullable()->default(0);
            $table->string('observations', 255)->nullable();
            $table->index('quoteId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_transfer_requests');
    }
};
