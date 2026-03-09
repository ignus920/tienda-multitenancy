<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tick_request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('tick_requests')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreignId('from_status_id')->nullable()->constrained('tick_statuses');
            $table->foreignId('to_status_id')->nullable()->constrained('tick_statuses');
            $table->text('message')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tick_request_history');
    }
};
