<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_purchase_request_details', function (Blueprint $table) {
            $table->id('id');
            $table->integer('purchase_requestsId');
            $table->integer('itemId')->nullable();
            $table->integer('quantity_requested')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index('purchase_requestsId');
            $table->index('itemId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_purchase_request_details');
    }
};
