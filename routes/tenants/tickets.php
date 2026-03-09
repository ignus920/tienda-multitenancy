<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Tickets\DepartmentManager;
use App\Livewire\Tenant\Tickets\RequestList;

Route::middleware(['auth', 'company.complete', \App\Auth\Middleware\SetTenantConnection::class])->group(function () {
    // Gestión de Departamentos para Solicitudes
    Route::get('/tenant/tickets/departments', DepartmentManager::class)
        ->name('tenant.tickets.departments');

    // Listado de Solicitudes
    Route::get('/tenant/tickets', RequestList::class)
        ->name('tenant.tickets');
});
