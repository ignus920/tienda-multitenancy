<?php

use Illuminate\Support\Facades\Route;
use App\Auth\Livewire\Verify2FA;
use App\Auth\Livewire\SelectTenant;
use App\Auth\Livewire\Enable2FA;
use App\Http\Livewire\Tenant\Dashboard as TenantDashboard;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestController;

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
    ->middleware(['auth', \App\Auth\Middleware\SetTenantConnection::class])
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

// Rutas API para Laravel World (accesibles desde cualquier tenant)
Route::prefix('api/world')->group(function () {
    Route::get('/countries', [WorldController::class, 'getCountries'])->name('api.world.countries');
    Route::get('/countries/search', [WorldController::class, 'searchCountries'])->name('api.world.countries.search');
    Route::get('/countries/{countryCode}/complete', [WorldController::class, 'getCountryComplete'])->name('api.world.countries.complete');
    Route::get('/countries/{countryId}/states', [WorldController::class, 'getStates'])->name('api.world.states');
    Route::get('/states/{stateId}/cities', [WorldController::class, 'getCities'])->name('api.world.cities');
});

// Rutas API para productos (requiere tenant activo)
Route::prefix('api/products')->middleware(\App\Auth\Middleware\SetTenantConnection::class)->group(function () {
    // CRUD básico
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

    // Métodos adicionales
    Route::post('/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/{id}/force', [ProductController::class, 'forceDelete']);
    Route::patch('/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::get('/list/trashed', [ProductController::class, 'trashed']);

    // Estadísticas
    Route::get('/stats/summary', [ProductController::class, 'stats']);
});

// Rutas de prueba para establecer tenant (SOLO PARA DESARROLLO)
Route::prefix('api/test')->group(function () {
    Route::get('/tenants', [TestController::class, 'listTenants']);
    Route::post('/set-tenant', [TestController::class, 'setTenant']);
    Route::get('/session', [TestController::class, 'sessionInfo']);
});





require __DIR__.'/auth.php';
