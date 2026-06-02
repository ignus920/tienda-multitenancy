<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_items_store', function (Blueprint $table) {
            $table->decimal('wp_stock_percentage', 5, 2)
                ->default(100.00)
                ->after('stock_max')
                ->comment('% del stock disponible a publicar en WordPress (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('inv_items_store', function (Blueprint $table) {
            $table->dropColumn('wp_stock_percentage');
        });
    }
};
