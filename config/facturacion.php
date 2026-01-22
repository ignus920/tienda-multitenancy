<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración Global de Facturación
    |--------------------------------------------------------------------------
    |
    | Esta configuración se usa como fallback cuando un tenant no tiene
    | configuración específica de facturación.
    |
    */

    'enabled' => env('FACTURACION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API de Facturación por Defecto
    |--------------------------------------------------------------------------
    */

    'default_api_url' => env('FACTURACION_API_URL', 'http://127.0.0.1:8000/api'),

    'default_timeout' => env('FACTURACION_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Alegra (Compatibilidad)
    |--------------------------------------------------------------------------
    |
    | Mantiene compatibilidad con configuraciones existentes
    |
    */

    'alegra' => [
        'enabled' => env('ALEGRA_ENABLED', true),
        'api_url' => env('ALEGRA_API_URL', 'http://127.0.0.1:8000/api'),
        'base_url' => env('FACTURACION_BASE_URL', 'https://app.alegra.com/api/v1/'),
        'username' => env('FACTURACION_USERNAME', ''),
        'token' => env('FACTURACION_TOKEN', ''),
        'timeout' => env('FACTURACION_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('FACTURACION_LOG_ENABLED', true),
        'level' => env('FACTURACION_LOG_LEVEL', 'info'), // debug, info, warning, error
        'channel' => env('FACTURACION_LOG_CHANNEL', 'single'), // Usar canal de log específico
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Cache
    |--------------------------------------------------------------------------
    |
    | Para optimizar las peticiones a la API de facturación
    |
    */

    'cache' => [
        'enabled' => env('FACTURACION_CACHE_ENABLED', true),
        'ttl' => env('FACTURACION_CACHE_TTL', 300), // 5 minutos
        'prefix' => 'facturacion_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    */

    'validation' => [
        // Validar automáticamente la configuración al guardarla
        'auto_validate_on_save' => true,

        // Timeout para pruebas de conexión
        'connection_test_timeout' => 10,

        // Reintentos automáticos en caso de fallo
        'max_retries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Sincronización
    |--------------------------------------------------------------------------
    */

    'sync' => [
        // Sincronizar automáticamente al crear/actualizar entidades
        'auto_sync_categories' => env('FACTURACION_AUTO_SYNC_CATEGORIES', false),
        'auto_sync_products' => env('FACTURACION_AUTO_SYNC_PRODUCTS', false),
        'auto_sync_customers' => env('FACTURACION_AUTO_SYNC_CUSTOMERS', false),

        // Batch size para sincronización masiva
        'batch_size' => env('FACTURACION_SYNC_BATCH_SIZE', 50),

        // Configuración de cola para sincronización asíncrona
        'use_queue' => env('FACTURACION_USE_QUEUE', false),
        'queue_name' => env('FACTURACION_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de Campos
    |--------------------------------------------------------------------------
    |
    | Define cómo mapear los campos locales a los campos de la API
    |
    */

    'field_mapping' => [
        'categories' => [
            'local_name' => 'api_name',
            'local_description' => 'api_description',
        ],
        'products' => [
            'local_name' => 'api_name',
            'local_description' => 'api_description',
            'local_price' => 'api_price',
            'local_reference' => 'api_reference',
            'local_category_id' => 'api_category',
        ],
        'customers' => [
            'local_name' => 'api_name',
            'local_document' => 'api_identification',
            'local_email' => 'api_email',
            'local_phone' => 'api_phone',
            'local_address' => 'api_address',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Errores
    |--------------------------------------------------------------------------
    */

    'error_handling' => [
        // Lanzar excepción en caso de error de API
        'throw_on_api_error' => false,

        // Códigos de error que se consideran recuperables
        'recoverable_error_codes' => [408, 429, 500, 502, 503, 504],

        // Tiempo de espera entre reintentos (segundos)
        'retry_delay' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs de Endpoints Específicos
    |--------------------------------------------------------------------------
    |
    | Permite personalizar las rutas de los endpoints si la API usa
    | nombres diferentes a los estándar
    |
    */

    'endpoints' => [
        'categories' => 'categories', // Cambiado para usar tu API
        'items' => 'items',
        'contacts' => 'contacts',
        'invoices' => 'invoices',
        'health_check' => 'health-check',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Desarrollo
    |--------------------------------------------------------------------------
    */

    'development' => [
        // Modo debug para más información en logs
        'debug_mode' => env('FACTURACION_DEBUG', false),

        // Simular respuestas exitosas (para testing)
        'mock_responses' => env('FACTURACION_MOCK', false),

        // Usar datos de prueba
        'use_test_data' => env('FACTURACION_USE_TEST_DATA', false),
    ],

];