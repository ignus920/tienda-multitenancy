<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_values')) {
            Schema::create('inv_values', function (Blueprint $table) {
                $table->id('id');
                $table->dateTime('date')->useCurrent();
                $table->decimal('values', 10, 2)->default(0);
                $table->string('type')->nullable();
                $table->integer('itemId')->nullable();
                $table->integer('warehouseId')->nullable();
                $table->string('label')->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
                $table->index('itemId');
                $table->index('warehouseId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_values');
    }
};
