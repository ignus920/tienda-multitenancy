<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_image_gallery')) {
            Schema::create('inv_image_gallery', function (Blueprint $table) {
                $table->id('id');
                $table->integer('itemId')->nullable();
                $table->text('img_path')->nullable();
                $table->string('type')->default('PRINCIPAL');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
                $table->index('itemId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_image_gallery');
    }
};
