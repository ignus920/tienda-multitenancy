<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Components\ImportCostCalculator;

Route::middleware(['tenant'])->group(function () {
    Route::get('/tenant/items/import-cost-calculator', ImportCostCalculator::class)
        ->name('tenant.items.import-cost-calculator');
});
