<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_detail_transfers', function (Blueprint $table) {
            $table->id('id');
            $table->integer('quantity')->nullable()->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('transferId')->nullable();
            $table->integer('itemId')->nullable();
            $table->integer('amount_received')->nullable();
            $table->index('transferId');
            $table->index('itemId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_detail_transfers');
    }
};
