<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_users_process')) {
            Schema::create('prd_users_process', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                // operator_user_id referencia a users en BD central, sin FK constraint
                $table->integer('operator_user_id')->nullable();
                $table->integer('process_id')->nullable();
                $table->foreign('process_id')->references('id')->on('prd_process');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_users_process');
    }
};
