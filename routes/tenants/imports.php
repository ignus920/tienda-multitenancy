<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Imports\Imports;
use App\Livewire\Tenant\Imports\ImportLabels;
use App\Livewire\Tenant\Imports\Orders;


Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/imports', Imports::class)->name('imports.imports');
});

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/imports-labels', ImportLabels::class)->name('imports.imports-labels');
});

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/imports-orders', Orders::class)->name('imports.imports-orders');
});
