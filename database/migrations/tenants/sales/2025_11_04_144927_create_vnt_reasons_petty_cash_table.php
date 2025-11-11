<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vnt_reasons_petty_cash', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 255);
            $table->integer('status')->nullable()->default(1);
            $table->string('type', 255);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_reasons_petty_cash');
    }
};
