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
        Schema::table('inv_detail_transfer_requests', function (Blueprint $table) {
            $table->foreign('transferRequestId', 'inv_detail_transfer_requests_ibfk_1')
                ->references('id')
                ->on('inv_transfer_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_detail_transfer_requests', function (Blueprint $table) {
            $table->dropForeign('inv_detail_transfer_requests_ibfk_1');
        });
    }
};
