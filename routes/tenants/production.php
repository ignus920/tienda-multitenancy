<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Production\ManageProcess;
use App\Livewire\Tenant\Production\ManageProductionOrders;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/production/processes', ManageProcess::class)->name('production.processes');
    Route::get('/production/orders', ManageProductionOrders::class)->name('production.orders');
});
