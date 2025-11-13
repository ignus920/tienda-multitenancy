<?php

use App\Http\Controllers\Quoter\QuoterController;
use App\Livewire\Tenant\Quoter\DesktopProductQuoter;
use App\Livewire\Tenant\Quoter\MobileProductQuoter;

/*
|--------------------------------------------------------------------------
| Quoter Routes
|--------------------------------------------------------------------------
|
| Aqu� est�n todas las rutas relacionadas con el cotizador (Quoter)
| Estas rutas est�n espec�ficamente para el tenant
|
*/

// Ruta principal del cotizador
Route::get('/tenant/quoter', [QuoterController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter');

// Ruta de productos del cotizador
Route::get('/tenant/quoter/products', [QuoterController::class, 'products'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter.products');

// Ruta de cotizador desktop
Route::get('/tenant/quoter/desktop', [QuoterController::class, 'desktop'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter.desktop');

// Ruta de cotizador mobile
Route::get('/tenant/quoter/mobile', [QuoterController::class, 'mobile'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter.mobile');

// Componente Livewire para cotizador desktop
Route::get('/tenant/quoter/products/desktop', DesktopProductQuoter::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter.products.desktop');

// Componente Livewire para cotizador mobile
Route::get('/tenant/quoter/products/mobile', MobileProductQuoter::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.quoter.products.mobile');