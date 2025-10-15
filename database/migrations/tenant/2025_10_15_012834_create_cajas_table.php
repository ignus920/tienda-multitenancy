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
        Schema::disableForeignKeyConstraints();

        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('saldo_inicial', 10, 2)->default(0);
            $table->decimal('saldo_actual', 10, 2)->default(0);
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->foreignId('usuario_id')->constrained();
            $table->enum('estado', ["abierta","cerrada"])->default('abierta');
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
