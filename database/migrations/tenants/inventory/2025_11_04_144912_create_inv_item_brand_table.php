<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_item_brand')) {
            Schema::create('inv_item_brand', function (Blueprint $table) {
                $table->id('id');
                $table->string('name', 100)->default(1);
                $table->integer('status')->default(1);
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_item_brand');
    }
};
