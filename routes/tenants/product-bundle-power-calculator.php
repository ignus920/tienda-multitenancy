<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Components\ProductBundlePowerCalculator;

Route::middleware(['tenant'])->group(function () {
    Route::get('/tenant/components/product-bundle-power-calculator', ProductBundlePowerCalculator::class)
        ->name('tenant.components.product-bundle-power-calculator');
});
