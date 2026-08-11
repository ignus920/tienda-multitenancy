<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\WordPress\WordPressStockSync;

Route::prefix('/wordpress')->group(function () {
    Route::get('/stock-sync', WordPressStockSync::class)->name('tenant.wordpress.stock-sync');
});
