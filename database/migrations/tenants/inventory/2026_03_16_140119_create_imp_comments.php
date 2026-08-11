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
        if (!Schema::hasTable('imp_comments')) {
            Schema::create('imp_comments', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('import_id')->nullable();
                $table->foreign('import_id')->references('id')->on('imp_imports');
                $table->mediumText('comment')->nullable();
                $table->integer('user_id')->nullable();
                $table->tinyInteger('initiator')->nullable();
                $table->timestamps();
                $table->index(['import_id', 'created_at']);
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_comments');
    }
};
