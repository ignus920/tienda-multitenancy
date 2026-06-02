<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización diaria de stock con WordPress a la 1:00 AM
Schedule::command('wp:sync-stock')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('❌ [WP-Stock] El cron wp:sync-stock falló');
    });
