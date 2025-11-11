<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_detail_transfer_requests', function (Blueprint $table) {
            $table->id('id');
            $table->integer('quantity')->default(0);
            $table->integer('quantitySend')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('transferRequestId')->nullable();
            $table->integer('itemId')->nullable();
            $table->index('transferRequestId');
            $table->index('itemId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_detail_transfer_requests');
    }
};
