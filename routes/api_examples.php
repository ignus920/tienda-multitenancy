<?php

// ========================================
// EJEMPLOS DE USO DEL SISTEMA DE FACTURACIÓN
// ========================================

use App\Http\Controllers\Api\FacturacionController;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Models\Auth\Tenant;

/**
 * RUTAS DE API PARA FACTURACIÓN
 *
 * Agregar estas rutas a tu archivo routes/api.php dentro del middleware de tenant:
 *
 * Route::middleware(['tenant'])->prefix('facturacion')->group(function () {
 *     Route::post('/configure', [FacturacionController::class, 'configure']);
 *     Route::get('/configuration', [FacturacionController::class, 'getConfiguration']);
 *     Route::post('/test-connection', [FacturacionController::class, 'testConnection']);
 *     Route::post('/categories', [FacturacionController::class, 'createCategory']);
 *     Route::post('/products', [FacturacionController::class, 'createProduct']);
 *     Route::post('/customers', [FacturacionController::class, 'createCustomer']);
 *     Route::post('/invoices', [FacturacionController::class, 'createInvoice']);
 * });
 */

/**
 * EJEMPLOS DE USO EN CÓDIGO PHP
 */

function ejemploConfiguracionPorTenant()
{
    // Obtener un tenant específico
    $tenant = Tenant::find('tu-tenant-uuid');

    // Configurar facturación para el tenant
    $configExitosa = TenantConfigManager::setFacturacionConfig($tenant, [
        'base_url' => 'http://127.0.0.1:8000/api',
        'token' => 'dGljc2lhK2FsZWdyYUBhbGVncmEuY29tOmY3ODQyMTViNTgzYjk5NzU1MzBk',
        'username' => 'admin@empresa.com', // Opcional
        'timeout' => 30,
        'enabled' => true
    ]);

    if ($configExitosa) {
        echo "✅ Configuración guardada exitosamente\n";
    } else {
        echo "❌ Error guardando configuración\n";
    }

    // Verificar si el tenant tiene configuración
    $tieneConfig = TenantConfigManager::hasFacturacionConfig($tenant);
    echo $tieneConfig ? "✅ Tenant configurado\n" : "❌ Tenant NO configurado\n";
}

function ejemploUsoDelServicio()
{
    // Obtener tenant actual (dentro de contexto tenant)
    $tenant = tenancy()->tenant;

    // Crear servicio de facturación para el tenant
    $facturacionService = FacturacionService::forTenant($tenant);

    // Crear una categoría
    $categoriaResult = $facturacionService->syncCategory([
        'name' => 'Productos de Limpieza',
        'description' => 'Productos para la limpieza del hogar'
    ]);

    if ($categoriaResult['success']) {
        $categoriaId = $categoriaResult['data']['id'];
        echo "✅ Categoría creada con ID: {$categoriaId}\n";

        // Crear un producto en esa categoría
        $productoResult = $facturacionService->syncProduct([
            'name' => 'Detergente Ariel',
            'description' => 'Detergente en polvo para ropa',
            'price' => 15000,
            'category' => $categoriaId,
            'reference' => 'DET-001'
        ]);

        if ($productoResult['success']) {
            echo "✅ Producto creado con ID: {$productoResult['data']['id']}\n";
        }
    }

    // Crear un cliente
    $clienteResult = $facturacionService->syncCustomer([
        'name' => 'Juan Pérez',
        'identification' => '12345678',
        'email' => 'juan.perez@email.com',
        'phone' => '3001234567',
        'address' => 'Calle 123 #45-67'
    ]);

    if ($clienteResult['success']) {
        $clienteId = $clienteResult['data']['id'];
        echo "✅ Cliente creado con ID: {$clienteId}\n";

        // Crear una factura
        $facturaResult = $facturacionService->createInvoice([
            'contact' => $clienteId,
            'date' => now()->format('Y-m-d'),
            'dueDate' => now()->addDays(30)->format('Y-m-d'),
            'observations' => 'Factura de prueba desde sistema multitenancy',
            'items' => [
                [
                    'id' => $productoResult['data']['id'] ?? 1,
                    'price' => 15000,
                    'quantity' => 2
                ]
            ]
        ]);

        if ($facturaResult['success']) {
            echo "✅ Factura creada con ID: {$facturaResult['data']['id']}\n";
        }
    }
}

function ejemploUsoDirectoDelAPIClient()
{
    // Para casos donde necesites usar directamente el cliente API
    $tenant = tenancy()->tenant;
    $facturacionService = FacturacionService::forTenant($tenant);
    $apiClient = $facturacionService->getApiClient();

    // Usar métodos específicos
    $categorias = $apiClient->getCategories(['limit' => 10]);
    $productos = $apiClient->getItems(['category' => 1]);

    // Usar métodos genéricos
    $customEndpoint = $apiClient->get('custom-endpoint', ['param1' => 'value1']);
    $postData = $apiClient->post('another-endpoint', ['data' => 'value']);
}

function ejemploValidacionDeConfiguracion()
{
    $tenant = tenancy()->tenant;
    $facturacionService = FacturacionService::forTenant($tenant);

    // Validar configuración actual
    $validacion = $facturacionService->validateConfiguration();

    if ($validacion['valid']) {
        echo "✅ Configuración válida: {$validacion['message']}\n";
    } else {
        echo "❌ Configuración inválida: {$validacion['message']}\n";
        if (isset($validacion['errors'])) {
            foreach ($validacion['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
    }
}

/**
 * EJEMPLOS DE PETICIONES HTTP/API
 */

function ejemplosPeticionesHTTP()
{
    /*
    // 1. Configurar facturación para un tenant
    POST /api/facturacion/configure
    Headers:
        Content-Type: application/json
        Authorization: Bearer {tu-token-del-sistema}
    Body:
    {
        "base_url": "http://127.0.0.1:8000/api",
        "token": "dGljc2lhK2FsZWdyYUBhbGVncmEuY29tOmY3ODQyMTViNTgzYjk5NzU1MzBk",
        "username": "admin@empresa.com",
        "timeout": 30,
        "enabled": true
    }

    // 2. Obtener configuración actual
    GET /api/facturacion/configuration
    Headers:
        Authorization: Bearer {tu-token-del-sistema}

    // 3. Probar conexión
    POST /api/facturacion/test-connection
    Headers:
        Authorization: Bearer {tu-token-del-sistema}

    // 4. Crear categoría
    POST /api/facturacion/categories
    Headers:
        Content-Type: application/json
        Authorization: Bearer {tu-token-del-sistema}
    Body:
    {
        "name": "Productos de Tecnología",
        "description": "Dispositivos electrónicos y accesorios"
    }

    // 5. Crear producto
    POST /api/facturacion/products
    Headers:
        Content-Type: application/json
        Authorization: Bearer {tu-token-del-sistema}
    Body:
    {
        "name": "iPhone 13",
        "description": "Smartphone Apple iPhone 13 128GB",
        "price": 2500000,
        "category": 1,
        "reference": "IPH13-128"
    }

    // 6. Crear cliente
    POST /api/facturacion/customers
    Headers:
        Content-Type: application/json
        Authorization: Bearer {tu-token-del-sistema}
    Body:
    {
        "name": "María García",
        "identification": "87654321",
        "email": "maria.garcia@email.com",
        "phone": "3109876543",
        "address": "Carrera 50 #20-30"
    }

    // 7. Crear factura
    POST /api/facturacion/invoices
    Headers:
        Content-Type: application/json
        Authorization: Bearer {tu-token-del-sistema}
    Body:
    {
        "contact": 1,
        "date": "2024-01-15",
        "dueDate": "2024-02-15",
        "observations": "Venta de productos electrónicos",
        "items": [
            {
                "id": 1,
                "price": 2500000,
                "quantity": 1
            },
            {
                "id": 2,
                "price": 50000,
                "quantity": 2
            }
        ]
    }
    */
}

/**
 * EJEMPLO DE MANEJO DE ERRORES
 */

function ejemploManejoDeErrores()
{
    try {
        $tenant = tenancy()->tenant;

        if (!TenantConfigManager::hasFacturacionConfig($tenant)) {
            // Manejar caso donde no hay configuración
            return response()->json([
                'error' => 'FACTURACION_NOT_CONFIGURED',
                'message' => 'Debe configurar la facturación antes de usarla',
                'action' => 'Vaya a configuración y agregue los datos de facturación'
            ], 400);
        }

        $facturacionService = FacturacionService::forTenant($tenant);
        $result = $facturacionService->createInvoice($invoiceData);

        if (!$result['success']) {
            // Manejar error de la API de facturación
            return response()->json([
                'error' => 'FACTURACION_API_ERROR',
                'message' => $result['message'],
                'details' => $result
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $result['data']]);

    } catch (\Exception $e) {
        // Manejar errores de sistema
        Log::error('Error en facturación', [
            'tenant_id' => $tenant->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'SYSTEM_ERROR',
            'message' => 'Error interno del sistema'
        ], 500);
    }
}