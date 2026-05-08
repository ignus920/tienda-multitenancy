<?php

use App\Livewire\Tenant\Returns\ReturnsList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/returns', ReturnsList::class)->name('tenant.returns');
});
