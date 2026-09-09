<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Portal\CustomerPortal;
use App\Livewire\Tenant\Portal\ClientDashboard;
use App\Livewire\Tenant\Portal\ClientOrders;
use App\Livewire\Tenant\Portal\ClientOrderDetail;
use App\Livewire\Tenant\Portal\ClientInvoices;
use App\Http\Controllers\Tenant\Portal\ClientPortalController;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    // Catálogo / armado de pedido (existente)
    Route::get('/client/portal', CustomerPortal::class)->name('tenant.client.portal');

    // Panel del cliente ("Mi cuenta")
    Route::get('/client/dashboard', ClientDashboard::class)->name('tenant.client.dashboard');
    Route::get('/client/pedidos', ClientOrders::class)->name('tenant.client.orders');
    Route::get('/client/pedidos/{remission}', ClientOrderDetail::class)
        ->whereNumber('remission')
        ->name('tenant.client.orders.show');
    Route::get('/client/facturas', ClientInvoices::class)->name('tenant.client.invoices');

    // Descarga del PDF oficial de la factura (Alegra)
    Route::get('/client/factura/{invoice}/pdf', [ClientPortalController::class, 'invoicePdf'])
        ->whereNumber('invoice')
        ->name('tenant.client.invoice.pdf');
});
