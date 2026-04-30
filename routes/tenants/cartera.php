<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Cartera\CarteraList;

Route::middleware(['auth'])->group(function () {
    Route::get('/cartera', CarteraList::class)->name('tenant.cartera.index');
});
