<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'tenant']], function () {
    Route::get('/marketing/sliders', App\Livewire\Tenant\Marketing\PromotionalSlidersManager::class)->name('tenant.sliders.index');
    Route::get('/marketing/videos', App\Livewire\Tenant\Marketing\VideoRequestManager::class)->name('tenant.marketing.videos.index');
});
