<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_item_applications')) {
            Schema::create('inv_item_applications', function (Blueprint $table) {
                $table->id('id');
                $table->integer('itemId');
                $table->integer('applicationsId');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
                $table->index('itemId');
                $table->index('applicationsId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_item_applications');
    }
};
