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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_type')->default('email')->after('two_factor_enabled'); // email, whatsapp, totp
            $table->text('two_factor_secret')->nullable()->after('two_factor_type'); // Para Google Authenticator
            $table->string('phone')->nullable()->after('two_factor_secret'); // Para WhatsApp
            $table->integer('two_factor_failed_attempts')->default(0)->after('phone');
            $table->timestamp('two_factor_locked_until')->nullable()->after('two_factor_failed_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_type',
                'two_factor_secret',
                'phone',
                'two_factor_failed_attempts',
                'two_factor_locked_until',
            ]);
        });
    }
};
