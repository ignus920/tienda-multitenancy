<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización diaria de stock con WordPress a la 1:00 AM vía Jobs en segundo plano
Schedule::call(function () {
    $tenants = \App\Models\Auth\Tenant::where('is_active', true)->get();
    foreach ($tenants as $tenant) {
        \App\Jobs\Tenant\WordPress\SyncWordPressStockJob::dispatch($tenant);
    }
})->dailyAt('01:00')
  ->name('sync-wordpress-stock-all-tenants')
  ->withoutOverlapping();

