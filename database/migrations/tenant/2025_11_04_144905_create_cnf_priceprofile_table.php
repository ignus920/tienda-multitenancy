<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cnf_priceprofile', function (Blueprint $table) {
            $table->id('id');
            $table->integer('price');
            $table->integer('profile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnf_priceprofile');
    }
};
