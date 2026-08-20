<?php

use App\Livewire\Tenant\Warranties\WarrantiesList;
use App\Livewire\Tenant\Warranties\WarrantyCreate;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/warranties', WarrantiesList::class)->name('tenant.warranties');
    Route::get('/warranties/create/{id}', WarrantyCreate::class)->name('tenant.warranties.create');
});
