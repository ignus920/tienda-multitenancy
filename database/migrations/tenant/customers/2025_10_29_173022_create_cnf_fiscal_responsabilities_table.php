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
        Schema::create('cnf_fiscal_responsabilities', function (Blueprint $table) {
          // 1. id
            $table->id(); // Equivale a bigIncrements() y es la clave primaria.
            $table->string('description', 255)->nullable();
            $table->integer('integrationDataId')->nullable();
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt')->nullable();
            $table->softDeletes('deletedAt'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_fiscal_responsabilities');
    }
};
