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
        Schema::create('cmp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['activo', 'pausado', 'anulado'])->default('activo');
            $table->integer('gift_quantity')->default(0);
            $table->integer('gifts_sent')->default(0);
            $table->integer('max_per_order')->default(1)->nullable();
            $table->enum('assignment_type', ['todos', 'asesor', 'manual', 'todas_op', 'antiguos_frecuentes'])->default('todos');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cmp_campaigns');
    }
};
