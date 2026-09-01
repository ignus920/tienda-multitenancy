<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mkt_video_requests')) {
            Schema::create('mkt_video_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->string('request_number', 30)->unique();
                $table->unsignedInteger('item_id')->comment('ref inv_items.id, sin FK (módulo externo)');
                $table->string('product_code', 100)->nullable()->comment('snapshot informativo al crear');
                $table->string('product_name', 255)->nullable()->comment('snapshot informativo al crear');
                $table->unsignedBigInteger('requested_by')->comment('ref users.id (central)');
                $table->unsignedBigInteger('gestor_id')->nullable()->comment('gestor de videos asignado');
                $table->text('instructions')->nullable();
                $table->enum('status', ['pendiente', 'en_proceso', 'terminado'])->default('pendiente');
                $table->unsignedTinyInteger('progress_done')->default(0);
                $table->unsignedTinyInteger('progress_total')->default(5);
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->string('youtube_url', 500)->nullable()->comment('enlace YouTube vigente de esta solicitud');
                $table->string('youtube_synced_url', 500)->nullable()->comment('último enlace escrito en la ficha del producto');
                $table->dateTime('youtube_synced_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('item_id');
                $table->index('status');
                $table->index('gestor_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mkt_video_requests');
    }
};
