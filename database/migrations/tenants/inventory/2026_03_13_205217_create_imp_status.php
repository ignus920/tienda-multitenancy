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
        if (!Schema::hasTable('imp_status')) {
            Schema::create('imp_status', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->string('name', 50)->nullable();
                $table->string('translated_name', 50)->nullable();
                $table->tinyInteger('in_progress')->nullable()->default(1);
                $table->string('function', 50)->nullable()->default(0);
                $table->tinyInteger('supplier')->nullable()->default(0);
                $table->tinyInteger('edition')->nullable()->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_status');
    }
};
