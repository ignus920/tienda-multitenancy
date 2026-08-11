<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'tenant']], function () {
    Route::get('/campaigns', App\Livewire\Tenant\Campaigns\CampaignManager::class)->name('tenant.campaigns.index');
});

