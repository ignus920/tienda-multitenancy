<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tick_department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('tick_departments')->onDelete('cascade');
            $table->unsignedBigInteger('user_id'); // Referencia a la tabla users central, no se puede hacer foreign key directa si están en BDs distintas
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tick_department_user');
    }
};
