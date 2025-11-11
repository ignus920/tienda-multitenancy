<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vnt_reconciliations')) {
            Schema::create('vnt_reconciliations', function (Blueprint $table) {
                $table->id('id');
                $table->integer('reconciliation');
                $table->string('observations', 255)->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
                $table->integer('pettyCashId')->nullable();
                $table->integer('userId')->nullable();
                $table->index('pettyCashId');
                $table->index('userId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vnt_reconciliations');
    }
};
