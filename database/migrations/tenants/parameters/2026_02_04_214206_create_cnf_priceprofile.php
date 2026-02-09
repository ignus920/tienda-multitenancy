<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cnf_priceprofile', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('price');
            $table->integer('profile');
            $table->timestamps();        // created_at, updated_at
            $table->softDeletes();       // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_priceprofile');
    }
};
