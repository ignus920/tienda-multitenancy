<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Portal\CustomerPortal;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/client/portal', CustomerPortal::class)->name('tenant.client.portal');
});
