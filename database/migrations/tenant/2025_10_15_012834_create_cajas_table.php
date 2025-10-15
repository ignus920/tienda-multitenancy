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
        Schema::disableForeignKeyConstraints();

        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->dateTime('opening_date');
            $table->dateTime('closing_date')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ["open","closed"])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
