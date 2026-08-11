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
        if (!Schema::hasTable('imp_imports')) {
            Schema::create('imp_imports', function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->primary(); // INT, auto-increment, PK
                $table->integer('item_id')->nullable();
                $table->integer('user_id')->nullable();
                $table->integer('label_id')->nullable();
                $table->foreign('label_id')->references('id')->on('imp_labels');
                $table->integer('qty_requested')->nullable()->default(0);
                $table->integer('qty_shipped')->nullable()->default(0);
                $table->double('price')->nullable();
                $table->tinyInteger('status')->nullable()->default(1);
                $table->integer('packing_id')->nullable();
                $table->foreign('packing_id')->references('id')->on('imp_packing');
                $table->tinyInteger('news')->default(0)->comment('novedades');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imp_imports');
    }
};
