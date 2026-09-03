<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mkt_video_request_tasks')) {
            Schema::create('mkt_video_request_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('video_request_id');
                $table->enum('channel', ['celular', 'youtube', 'web', 'tiktok', 'instagram']);
                $table->enum('status', ['pendiente', 'en_proceso', 'listo'])->default('pendiente');
                $table->string('link', 500)->nullable();
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->dateTime('completed_at')->nullable();
                $table->unsignedBigInteger('completed_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['video_request_id', 'channel']);
                $table->foreign('video_request_id')->references('id')->on('mkt_video_requests')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mkt_video_request_tasks');
    }
};
