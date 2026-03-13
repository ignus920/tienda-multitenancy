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
        Schema::create('imp_shippments', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
            $table->integer('consecutive')->nullable();
            $table->date('etd')->nullable();
            $table->string('operation_number', 100)->nullable();
            $table->enum('way', ["Aerea", "Maritima"])->nullable();
            $table->mediumText('conveyor')->nullable()->comment('transportador');
            $table->mediumText('obs')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_shippments');
    }
};
