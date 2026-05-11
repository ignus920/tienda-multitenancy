<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Gestion\GestionVentas;

Route::middleware(['tenant'])->group(function () {
    Route::get('/tenant/gestion-ventas', GestionVentas::class)->name('tenant.gestion');
});
