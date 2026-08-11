<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Catalogs\ManageCatalogs;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/tenant/catalogs', ManageCatalogs::class)->name('tenant.catalogs');
});
