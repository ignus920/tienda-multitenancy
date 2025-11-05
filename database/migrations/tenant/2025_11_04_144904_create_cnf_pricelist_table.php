<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cnf_pricelist')) {
            Schema::create('cnf_pricelist', function (Blueprint $table) {
            $table->id('id');
            $table->string('title', 10);
            $table->string('value');
            $table->dateTime('createAd');
            $table->dateTime('updateAd')->nullable();
            $table->integer('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cnf_pricelist');
    }
};
