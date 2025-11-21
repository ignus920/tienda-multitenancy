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
        Schema::table('vnt_contacts', function (Blueprint $table) {
            // Cambiar positionId a int (con signo) para que coincida con cfg_positions.id
            $table->integer('positionId')->nullable()->default(1)->change();
        });

        // Ahora agregar la clave foránea para positionId
        Schema::table('vnt_contacts', function (Blueprint $table) {
            $table->foreign('positionId')
                ->references('id')
                ->on('cnf_positions')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vnt_contacts', function (Blueprint $table) {
            $table->dropForeign(['positionId']);
            // Revertir a unsignedInteger
            $table->unsignedInteger('positionId')->nullable()->default(1)->change();
        });
    }
};