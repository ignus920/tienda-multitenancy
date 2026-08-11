<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prd_material_items') && !Schema::hasColumn('prd_material_items', 'unit_measurement')) {
            Schema::table('prd_material_items', function (Blueprint $table) {
                $table->integer('unit_measurement')->nullable()->after('qty');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('prd_material_items') && Schema::hasColumn('prd_material_items', 'unit_measurement')) {
            Schema::table('prd_material_items', function (Blueprint $table) {
                $table->dropColumn('unit_measurement');
            });
        }
    }
};
