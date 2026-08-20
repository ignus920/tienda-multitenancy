<?php

use App\Livewire\Tenant\Warranties\WarrantiesList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/warranties', WarrantiesList::class)->name('tenant.warranties');
});
