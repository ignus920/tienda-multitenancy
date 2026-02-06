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
        Schema::table('vnt_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('vnt_routes', 'zone_id')) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('vnt_routes', 'salesman_id')) {
                $table->unsignedBigInteger('salesman_id')->nullable()->after('zone_id');
            }
            if (!Schema::hasColumn('vnt_routes', 'sale_day')) {
                $table->string('sale_day')->nullable()->after('salesman_id');
            }
            if (!Schema::hasColumn('vnt_routes', 'delivery_day')) {
                $table->string('delivery_day')->nullable()->after('sale_day');
            }
        });

        // Agregar foreign key si la tabla vnt_zones existe
        if (Schema::hasTable('vnt_zones') && Schema::hasColumn('vnt_routes', 'zone_id')) {
            Schema::table('vnt_routes', function (Blueprint $table) {
                $table->foreign('zone_id')->references('id')->on('vnt_zones')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vnt_routes', function (Blueprint $table) {
            // Eliminar foreign key primero
            if (Schema::hasColumn('vnt_routes', 'zone_id')) {
                $table->dropForeign(['zone_id']);
            }

            // Eliminar columnas
            $columns = ['zone_id', 'salesman_id', 'sale_day', 'delivery_day'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('vnt_routes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
