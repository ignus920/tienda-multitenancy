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
        if (!Schema::hasTable('imp_status_history')) {
            Schema::create('imp_status_history', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('import_id')->nullable();
                $table->foreign('import_id')->references('id')->on('imp_imports');
                $table->integer('previous_state')->nullable();
                $table->foreign('previous_state')->references('id')->on('imp_status');
                $table->integer('new_state')->nullable();
                $table->foreign('new_state')->references('id')->on('imp_status');
                $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('imp_status_history');
    }
};
