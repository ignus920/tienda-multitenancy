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
        Schema::create('user_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Rol principal del usuario en este tenant (opcional)
            $table->string('role')->nullable();

            // Estado del acceso
            $table->boolean('is_active')->default(true);

            // Último acceso al tenant
            $table->timestamp('last_accessed_at')->nullable();

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['user_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tenants');
    }
};
