<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_transfers', function (Blueprint $table) {
            $table->id('id');
            $table->dateTime('date')->useCurrent();
            $table->text('observations');
            $table->string('status')->default('REGISTRADO');
            $table->integer('api_data_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('warehouseFromId')->nullable();
            $table->integer('warehouseToId')->nullable();
            $table->integer('consecutive');
            $table->integer('userId');
            $table->string('packing', 100)->nullable();
            $table->index('warehouseFromId');
            $table->index('warehouseToId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_transfers');
    }
};
