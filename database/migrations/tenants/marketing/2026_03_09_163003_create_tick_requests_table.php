<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tick_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('tick_departments');
            $table->foreignId('status_id')->constrained('tick_statuses');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('detail');
            $table->text('image_path')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tick_requests');
    }
};
