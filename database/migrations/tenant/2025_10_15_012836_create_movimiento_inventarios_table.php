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

        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained();
            $table->enum('tipo', ["entrada","salida","ajuste"]);
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo', 255);
            $table->dateTime('fecha');
            $table->foreignId('usuario_id')->constrained();
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
        Schema::dropIfExists('movimiento_inventarios');
    }
};
