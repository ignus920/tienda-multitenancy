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
        Schema::table('inv_inventory_adjustments', function (Blueprint $table) {
            $table->foreign('reasonId')->references('id')->on('inv_reasons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_inventory_adjustments', function (Blueprint $table) {
            //
        });
    }
};
