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
        Schema::create('imp_labels', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
            $table->string('name', 200)->nullable()->default(50);
            $table->tinyInteger('asap')->nullable()->default(0);
            $table->date('estimated_date')->nullable();
            $table->mediumText('description')->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->integer('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_labels');
    }
};
