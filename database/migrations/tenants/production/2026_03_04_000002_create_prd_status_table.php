<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_status')) {
            Schema::create('prd_status', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->string('name', 100)->nullable();
                $table->integer('application')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->mediumText('detail_application')->nullable()
                    ->comment('Detalle de la aplicación del estado');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_status');
    }
};
