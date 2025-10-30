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
        Schema::create('cnf_type_identifications', function (Blueprint $table) {
          $table->id(); // Laravel usa BIGINT UNSIGNED, que es compatible y estándar.
          $table->string('name', 255)->nullable();
          $table->string('acronym', 255)->nullable();
          $table->string('api_data_id', 255)->nullable();
          $table->tinyInteger('status')->nullable()->default(1);
          $table->dateTime('createdAt')->nullable(false)->useCurrent();
          $table->dateTime('updatedAt')->nullable();
          $table->softDeletes('deletedAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnf_type_identifications');
    }
};
