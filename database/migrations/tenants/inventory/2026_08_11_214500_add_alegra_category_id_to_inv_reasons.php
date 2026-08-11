<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_reasons')) {
            Schema::table('inv_reasons', function (Blueprint $table) {
                if (!Schema::hasColumn('inv_reasons', 'alegra_category_id')) {
                    $table->string('alegra_category_id', 150)->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_reasons')) {
            Schema::table('inv_reasons', function (Blueprint $table) {
                if (Schema::hasColumn('inv_reasons', 'alegra_category_id')) {
                    $table->dropColumn('alegra_category_id');
                }
            });
        }
    }
};
