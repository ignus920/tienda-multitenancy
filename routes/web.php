<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Verify2FA;
use App\Livewire\Auth\SelectTenant;
use App\Livewire\Auth\Enable2FA;
use App\Livewire\Tenant\Dashboard as TenantDashboard;

Route::view('/', 'welcome');

// Rutas de autenticación 2FA
Route::get('/verify-2fa', Verify2FA::class)
    ->name('verify.2fa');

// Rutas de selección de tenant (requiere autenticación)
Route::get('/select-tenant', SelectTenant::class)
    ->middleware(['auth'])
    ->name('tenant.select');

// Dashboard del tenant (requiere autenticación y tenant seleccionado)
Route::get('/tenant/dashboard', TenantDashboard::class)
    ->middleware(['auth', \App\Http\Middleware\SetTenantConnection::class])
    ->name('tenant.dashboard');

// Configuración de 2FA (requiere autenticación)
Route::get('/settings/2fa', Enable2FA::class)
    ->middleware(['auth'])
    ->name('settings.2fa');

// Dashboard original de Breeze (redirige a selección de tenant)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
