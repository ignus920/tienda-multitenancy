<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Items\ItemObservation;

Route::middleware(['tenant'])->prefix('tenant/observations')->group(function () {
    // Ruta para gestionar las observaciones de un ítem específico
    Route::get('/{itemId}', ItemObservation::class)->name('tenant.observations.manage');
});
