<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Movements\MaterialOutboundPdfController;

Route::get('/movements', function () {
    return view('livewire.tenant.movements.movements');
})->name('movements.movements');

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    Route::get('/movements/{adjustmentId}/orden-alistamiento', [MaterialOutboundPdfController::class, 'print'])
        ->whereNumber('adjustmentId')
        ->name('movements.material-outbound-order.pdf');
});
