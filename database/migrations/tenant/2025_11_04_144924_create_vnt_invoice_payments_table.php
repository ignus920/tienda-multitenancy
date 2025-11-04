<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vnt_invoice_payments', function (Blueprint $table) {
            $table->id('id');
            $table->decimal('value', 10, 2);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('invoiceId')->nullable();
            $table->integer('methodPaymentId')->nullable();
            $table->index('invoiceId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_invoice_payments');
    }
};
