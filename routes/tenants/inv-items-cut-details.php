<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Components\InvItemsCutDetails;

Route::middleware(['tenant'])->group(function () {
    Route::get('/tenant/components/inv-items-cut-details', InvItemsCutDetails::class)
        ->name('tenant.components.inv-items-cut-details');
});
