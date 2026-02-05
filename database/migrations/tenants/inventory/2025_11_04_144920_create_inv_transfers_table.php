<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_transfers')) {
            Schema::create('inv_transfers', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->dateTime('date')->useCurrent();
                $table->text('observations')->nullable();
                $table->enum('status', ['REGISTRADO', 'ENTREGADO', 'ANULADO', 'EN TRANSITO'])->default('REGISTRADO');
                $table->integer('api_data_id')->nullable();
                $table->integer('storeFromId')->comment('ID del warehouse de origen (tabla central)');
                $table->integer('storeToId')->comment('ID del warehouse de destino (tabla central)');
                $table->integer('consecutive');
                $table->integer('userId')->comment('ID del usuario (tabla central)');
                $table->tinyInteger('packing')->default(0)->comment('0=No empacado, 1=Empacado');
                $table->index('storeFromId');
                $table->index('storeToId');
                $table->index('userId');
                $table->index('consecutive');
                $table->timestamps();  // created_at, updated_at
                $table->softDeletes(); // deleted_at
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_transfers');
    }
};
