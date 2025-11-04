<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cnf_audit_status_log', function (Blueprint $table) {
            $table->id('id');
            $table->integer('warehouseId')->default(1);
            $table->integer('docId')->default(0);
            $table->text('event');
            $table->text('campo1')->nullable();
            $table->text('campo2')->nullable();
            $table->text('campo3')->nullable();
            $table->dateTime('fecha_cambio')->nullable();
            $table->string('user', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnf_audit_status_log');
    }
};
