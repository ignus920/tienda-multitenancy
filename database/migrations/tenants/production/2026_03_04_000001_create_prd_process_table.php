<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prd_process')) {
            Schema::create('prd_process', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary();
                $table->string('name', 255);
                $table->mediumText('previous_notes')->nullable()
                    ->comment('Notas que se muestran al operario para recordar tareas previas del proceso');
                $table->tinyInteger('inventory_consumption')->default(0);
                $table->mediumText('documents_generates')->nullable()
                    ->comment('JSON con documentos que podría generar el proceso');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_process');
    }
};
