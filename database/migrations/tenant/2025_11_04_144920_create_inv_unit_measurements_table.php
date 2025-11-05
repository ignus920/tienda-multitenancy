<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_unit_measurements')) {
            Schema::create('inv_unit_measurements', function (Blueprint $table) {
                $table->id('id');
                $table->string('description', 255)->default(1);
                $table->integer('status')->nullable()->default(1);
                $table->integer('quantity')->default(0);
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_unit_measurements');
    }
};
