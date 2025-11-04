<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vnt_detail_reconciliations', function (Blueprint $table) {
            $table->id('id');
            $table->integer('value');
            $table->integer('valueSystem');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('methodPaymentId')->nullable();
            $table->integer('reconciliationId');
            $table->index('methodPaymentId');
            $table->index('reconciliationId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_detail_reconciliations');
    }
};
