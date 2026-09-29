<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Catalogs\ManageCatalogs;

Route::get('/fervicom/{filename}', function ($filename) {
    $path = storage_path('app/public/catalogs/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});


Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/tenant/catalogs', ManageCatalogs::class)->name('tenant.catalogs');
});
