<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cmp_campaign_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('remission_id')->nullable()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('cmp_campaign_customers', function (Blueprint $table) {
            $table->dropColumn('remission_id');
        });
    }
};
