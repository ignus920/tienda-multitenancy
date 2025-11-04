<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cnf_templates', function (Blueprint $table) {
            $table->id('id');
            $table->string('quote', 1)->nullable()->default('N');
            $table->string('remission', 1)->nullable()->default('N');
            $table->text('text');
            $table->integer('status')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnf_templates');
    }
};
