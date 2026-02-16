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
        Schema::table('inv_purchase_request_details', function (Blueprint $table) {
            $table->foreign('purchase_requestsId', 'inv_purchase_request_details_ibfk_1')
                ->references('id')
                ->on('inv_purchase_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_purchase_request_details', function (Blueprint $table) {
            $table->dropForeign('inv_purchase_request_details_ibfk_1');
        });
    }
};
