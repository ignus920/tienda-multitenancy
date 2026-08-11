<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_items_locations', function (Blueprint $table) {
            $table->string('locationId', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inv_items_locations', function (Blueprint $table) {
            $table->integer('locationId')->nullable()->change();
        });
    }
};
