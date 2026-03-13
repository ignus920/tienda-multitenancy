<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vnt_credit_notes')) {
            Schema::create('vnt_credit_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('reason', 100);
                $table->string('payment_method', 100)->nullable();
                $table->text('observations')->nullable();
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status', 50)->default('REGISTRADO');
                $table->string('api_data_id', 100)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('vnt_credit_note_items')) {
            Schema::create('vnt_credit_note_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('credit_note_id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name', 255);
                $table->string('item_code', 100)->nullable();
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('tax', 5, 2)->default(0);
                $table->decimal('subtotal', 15, 2);
                $table->decimal('total', 15, 2);
                $table->string('detail_type', 20)->default('remission'); // remission | quote
                $table->unsignedBigInteger('detail_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_credit_note_items');
        Schema::dropIfExists('vnt_credit_notes');
    }
};
