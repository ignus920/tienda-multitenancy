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
        Schema::create('cmp_campaign_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('cmp_campaigns')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cmp_campaign_customers');
    }
};
