<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vnt_detail_petty_cash', function (Blueprint $table) {
            $table->id('id');
            $table->integer('status')->nullable()->default(1);
            $table->decimal('value', 10, 2);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('pettyCashId')->nullable();
            $table->integer('reasonPettyCashId')->nullable();
            $table->integer('methodPaymentId')->nullable();
            $table->integer('invoiceId')->nullable();
            $table->text('observations')->nullable();
            $table->index('pettyCashId');
            $table->index('reasonPettyCashId');
            $table->index('methodPaymentId');
            $table->index('invoiceId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_detail_petty_cash');
    }
};
