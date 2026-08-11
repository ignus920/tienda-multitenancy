<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/customers.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/items.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/petty_cash.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/quoter.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/users.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/movements.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/transfers.php'));

            Route::middleware(['web', 'tenant'])
                ->group(base_path('routes/tenants/inventory_confirmations.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Auth\Middleware\SetTenantConnection::class,
            'company.complete' => \App\Http\Middleware\EnsureCompanyDataComplete::class,
            'super.admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'warehouse.selected' => \App\Http\Middleware\EnsureWarehouseSelected::class,
            'access.control' => \App\Http\Middleware\CheckTenantAccessControl::class,
        ]);

        // Aplicar middleware tenant a rutas de Livewire cuando sea necesario
        $middleware->group('tenant', [
            'auth',
            'company.complete',
            \App\Auth\Middleware\SetTenantConnection::class,
            \App\Http\Middleware\CheckTenantAccessControl::class,
        ]);

        // DESHABILITADO TEMPORALMENTE - Causaba loop de redirección
        // $middleware->web(append: [
        //     \App\Http\Middleware\EnsureWarehouseSelected::class,
        // ]);

        // Excluir rutas de test del CSRF (SOLO PARA DESARROLLO)
        $middleware->validateCsrfTokens(except: [
            'api/test/*',
            'api/products/*',
            'api/customers/*',
            'api/sgsst/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
