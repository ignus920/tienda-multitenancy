<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_fields_process')) {
            Schema::create('prd_fields_process', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->integer('processId')->default(0);
                $table->foreign('processId')->references('id')->on('prd_process');
                $table->string('name', 255)->default('Nombre');
                $table->string('label', 255)->nullable();
                $table->mediumText('type')->nullable();
                $table->mediumText('class')->nullable();
                $table->mediumText('options')->nullable();
                $table->tinyInteger('status')->nullable()->default(1);
                $table->string('parent_field', 200)->nullable()
                    ->comment('Nombre del campo que controla la visibilidad');
                $table->string('parent_value', 200)->nullable()
                    ->comment('Valor que debe tener ese campo para mostrar este');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_fields_process');
    }
};
