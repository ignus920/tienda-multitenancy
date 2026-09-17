<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\CostCalculation\ManageCostCalculations;
use App\Livewire\Tenant\CostCalculation\CostCalculationForm;

/*
|--------------------------------------------------------------------------
| Cálculo de Costos Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/tenant/cost-calculations', ManageCostCalculations::class)
        ->name('tenant.cost-calculations');

    Route::get('/tenant/cost-calculations/crear', CostCalculationForm::class)
        ->name('tenant.cost-calculations.create');

    Route::get('/tenant/cost-calculations/{calculationId}/editar', CostCalculationForm::class)
        ->name('tenant.cost-calculations.edit');
});
