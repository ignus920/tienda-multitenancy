<?php

use Illuminate\Support\Facades\Route;
use App\Auth\Livewire\Verify2FA;
use App\Auth\Livewire\SelectTenant;
use App\Auth\Livewire\Enable2FA;
use App\Livewire\Tenant\Dashboard\Dashboard as TenantDashboard;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestController;
use Livewire\Volt\Volt;
use App\Livewire\Company\UpdateCompany;
use App\Auth\Middleware\SetTenantConnection;
use App\Livewire\Tenant\Customers\CustomerManager;
use App\Livewire\Tenant\Warehouses\WarehouseManager;




Route::view('/', 'welcome');

// Rutas de autenticación 2FA
Route::get('/verify-2fa', Verify2FA::class)
    ->name('verify.2fa');

// Selección de sucursal después del login (DESHABILITADA - Ahora se usa el modal LoginWarehouseSelector)
// Volt::route('/select-warehouse', 'pages.auth.select-warehouse')
//     ->middleware(['auth'])
//     ->name('select.warehouse');

// Configuración de empresa (requiere autenticación)
Route::get('/company/setup', UpdateCompany::class)
    ->middleware(['auth'])
    ->name('company.setup');

// Rutas de selección de tenant (requiere autenticación y datos completos)
Route::get('/select-tenant', SelectTenant::class)
    ->middleware(['auth', 'company.complete'])
    ->name('tenant.select');

// Dashboard del tenant (requiere autenticación, datos completos y tenant seleccionado)
Route::get('/tenant/dashboard', TenantDashboard::class)
    ->middleware(['auth', 'company.complete', SetTenantConnection::class])
    ->name('tenant.dashboard');




// Módulo de Clientes (requiere autenticación, datos completos y tenant seleccionado)
Route::get('/tenant/customers', CustomerManager::class)
    ->middleware('tenant')
    ->name('tenant.customers');

// Módulo de Sucursales (requiere autenticación, datos completos y tenant seleccionado)
Route::get('/tenant/warehouses', WarehouseManager::class)
    ->middleware('tenant')
    ->name('tenant.warehouses');




// Configuración de 2FA (requiere autenticación y datos completos)
Route::get('/settings/2fa', Enable2FA::class)
    ->middleware(['auth', 'company.complete'])
    ->name('settings.2fa');

// Dashboard original de Breeze (redirige al dashboard del tenant o a la selección de sucursal)
Route::get('dashboard', function () {
    if (session()->has('tenant_id')) {
        if (auth()->user() && auth()->user()->profile_id == 17) {
            return redirect()->route('imports.imports-orders');
        }
        if (auth()->user() && auth()->user()->profile_id == 18) {
            return redirect()->route('tenant.client.portal');
        }
        return redirect()->route('tenant.dashboard');
    }
    return redirect()->route('tenant.select');
})->middleware(['auth', 'verified', 'company.complete'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'company.complete'])
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

// CRUD de empresas (vnt_companies) - Base central
Route::get('/companies-manager', App\Livewire\Central\Companies\CompaniesManager::class)
    ->name('companies.manager');

// Dashboard Super Administrador - Gestión global del sistema
Route::get('/super-admin', App\Livewire\Central\SuperAdmin\GlobalDashboard::class)
    ->middleware(['auth', 'super.admin'])
    ->name('super.admin.dashboard');

// Rutas de prueba para establecer tenant (SOLO PARA DESARROLLO)
// Route::prefix('api/test')->group(function () {
//     Route::get('/tenants', [TestController::class, 'listTenants']);
//     Route::post('/set-tenant', [TestController::class, 'setTenant']);
//     Route::get('/session', [TestController::class, 'sessionInfo']);
// });




// Ejemplo de formulario configurable (SOLO PARA DESARROLLO/DEMO)
// Route::get('/ejemplo-configuracion', App\Livewire\Examples\ConfigurableFormExample::class)
//     ->middleware(['auth', 'company.complete', App\Auth\Middleware\SetTenantConnection::class])
//     ->name('ejemplo.configuracion');

// Rutas para gestión de permisos y roles (Catálogo de permisos)
Route::prefix('api/permissions')->middleware(['auth', 'company.complete'])->group(function () {
    Route::get('/catalog', [App\Http\Controllers\Auth\PermissionController::class, 'getCatalog'])
        ->name('api.permissions.catalog');

    Route::post('/assign', [App\Http\Controllers\Auth\PermissionController::class, 'assignPermissionToProfile'])
        ->name('api.permissions.assign');

    Route::post('/assign-multiple', [App\Http\Controllers\Auth\PermissionController::class, 'assignMultiplePermissions'])
        ->name('api.permissions.assign.multiple');

    Route::delete('/remove', [App\Http\Controllers\Auth\PermissionController::class, 'removePermissionFromProfile'])
        ->name('api.permissions.remove');

    Route::get('/profile/{profileId}', [App\Http\Controllers\Auth\PermissionController::class, 'getProfilePermissions'])
        ->name('api.permissions.profile');

    Route::post('/clone', [App\Http\Controllers\Auth\PermissionController::class, 'cloneProfilePermissions'])
        ->name('api.permissions.clone');
});



require __DIR__ . '/auth.php';
// Incluir rutas del módulo de parámetros del tenant
require __DIR__ . '/tenants/parameters.php';
// Incluir rutas del módulo de pagos de cotizacion 
require __DIR__ . '/tenants/payments.php';
// Incluir rutas del módulo de remisiones 
require __DIR__ . '/tenants/remissions.php';
//Incluir rutas del módelo de facturas
require __DIR__ . '/tenants/invoices.php';
// Incluir rutas del módulo de usuarios
require __DIR__ . '/tenants/users.php';
// Incluir rutas del módulo de solicitud de transferencia
require __DIR__ . '/tenants/transfer_request.php';
// Incluir rutas del módulo de importaciones
require __DIR__ . '/tenants/imports.php';
// Incluir rutas del módulo de producción
require __DIR__ . '/tenants/production.php';
// Incluir rutas del módulo de campañas (regalos)
require __DIR__ . '/tenants/campaigns.php';
// Incluir rutas del módulo de solicitudes/tickets
require __DIR__ . '/tenants/tickets.php';
// Incluir rutas del módulo de observacionesitems
require __DIR__ . '/tenants/ItemObservation.php';
// Incluir rutas del calculador de costos de importación
require __DIR__ . '/tenants/import-cost-calculator.php';
// Incluir rutas del calculador de potencia 
require __DIR__ . '/tenants/product-bundle-power-calculator.php';
// Incluir rutas del detalle de corte de items
require __DIR__ . '/tenants/inv-items-cut-details.php';
// Incluir rutas del módulo de cartera
require __DIR__ . '/tenants/cartera.php';
// Incluir rutas del módulo de empaque
require __DIR__ . '/tenants/packing.php';

// Incluir rutas del módulo de gestión
require __DIR__ . '/tenants/gestion.php';

// Incluir rutas del módulo de informes
require __DIR__ . '/tenants/reports.php';

// Incluir rutas del módulo de confirmaciones de inventario
require __DIR__ . '/tenants/inventory_confirmations.php';

// Incluir rutas del módulo de devoluciones
require __DIR__ . '/tenants/returns.php';

// Incluir rutas del módulo de despachos
require __DIR__ . '/tenants/dispatches.php';

// Incluir rutas de WordPress
require __DIR__ . '/tenants/wordpress.php';

// Incluir rutas de Catálogos
require __DIR__ . '/tenants/catalogs.php';

// Incluir rutas del portal de autogestión de clientes
require __DIR__ . '/tenants/portal.php';






