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
        Schema::table('tenants', function (Blueprint $table) {
            // Asegurar que data sea nullable y tenga un valor por defecto
            if (Schema::hasColumn('tenants', 'data')) {
                $table->json('data')->nullable()->default(null)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir
    }
};
