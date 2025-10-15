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

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->dateTime('fecha');
            $table->foreignId('cliente_id')->nullable()->constrained();
            $table->foreignId('usuario_id')->constrained();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ["pendiente","pagada","anulada"])->default('pendiente');
            $table->enum('tipo_pago', ["efectivo","tarjeta","transferencia"]);
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
        Schema::dropIfExists('ventas');
    }
};
