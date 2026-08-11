<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Packing\PackingTerminal;

Route::middleware(['tenant'])->group(function () {
    Route::get('/empaque', PackingTerminal::class)->name('tenant.packing.terminal');
});
