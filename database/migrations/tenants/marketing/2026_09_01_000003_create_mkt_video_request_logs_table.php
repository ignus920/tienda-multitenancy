<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mkt_video_request_logs')) {
            Schema::create('mkt_video_request_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_request_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 50)->comment('creada|tarea_actualizada|enlace_actualizado|estado_recalculado|sync_youtube|sync_youtube_error');
                $table->string('channel', 20)->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->dateTime('created_at')->nullable();

                $table->index('video_request_id');
                $table->foreign('video_request_id')->references('id')->on('mkt_video_requests')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mkt_video_request_logs');
    }
};
