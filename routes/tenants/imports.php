<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Imports\Imports;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/imports', Imports::class)->name('imports.imports');
});
?>