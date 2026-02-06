<?php

namespace App\Services\Facturacion;

use App\Models\Auth\Tenant;
use App\Models\Central\VntWarehouse;
use App\Models\Tenant\CnfInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConfigService
{
    /**
     * Obtener configuración de facturación desde la base de datos
     */
    public static function getFacturacionConfigFromDatabase(Tenant $tenant): ?array
    {
        try {
            // Obtener el company_id del tenant
            $companyId = self::getCompanyIdFromTenant($tenant);

            Log::info('🔍 INICIO getFacturacionConfigFromDatabase', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'company_id' => $companyId
            ]);

            if (!$companyId) {
                Log::error('❌ No se pudo obtener company_id del tenant', [
                    'tenant_id' => $tenant->id,
                    'tenant_company_id' => $tenant->company_id,
                    'tenant_settings' => $tenant->settings
                ]);
                return null;
            }

            // Obtener warehouses de la BD Central (RAP) por company_id
            $warehouses = self::getWarehousesByCompanyId($companyId);

            Log::info('🏪 Warehouses obtenidos de RAP', [
                'tenant_id' => $tenant->id,
                'company_id' => $companyId,
                'warehouses_count' => $warehouses->count(),
                'warehouse_ids' => $warehouses->pluck('id')->toArray()
            ]);

            if ($warehouses->isEmpty()) {
                Log::error('❌ No se encontraron warehouses para company_id', [
                    'tenant_id' => $tenant->id,
                    'company_id' => $companyId
                ]);
                return null;
            }

            // Obtener configuraciones de facturación desde cnf_invoices
            $configs = [];
            foreach ($warehouses as $warehouse) {
                Log::info('🔍 Buscando config para warehouse', [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name
                ]);

                $invoiceConfig = self::getInvoiceConfigByWarehouse($warehouse->id);
                if ($invoiceConfig) {
                    Log::info('✅ Config encontrada para warehouse', [
                        'warehouse_id' => $warehouse->id,
                        'config_id' => $invoiceConfig['id']
                    ]);
                    $configs[] = [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'config' => $invoiceConfig
                    ];
                } else {
                    Log::warning('❌ No config para warehouse', [
                        'warehouse_id' => $warehouse->id
                    ]);
                }
            }

            Log::info('📊 Resumen configuraciones encontradas', [
                'tenant_id' => $tenant->id,
                'company_id' => $companyId,
                'total_configs' => count($configs),
                'configs_summary' => array_map(fn($c) => ['warehouse_id' => $c['warehouse_id'], 'has_token' => !empty($c['config']['token'])], $configs)
            ]);

            if (empty($configs)) {
                Log::error('❌ No se encontraron configuraciones de facturación en cnf_invoices', [
                    'tenant_id' => $tenant->id,
                    'company_id' => $companyId,
                    'warehouse_ids_searched' => $warehouses->pluck('id')->toArray()
                ]);
                return null;
            }

            // Retornar la primera configuración encontrada como principal
            // O implementar lógica para seleccionar la configuración adecuada
            $primaryConfig = $configs[0]['config'];

            return [
                'base_url' => $primaryConfig['facturador'], // El campo facturador contiene la base_url
                'token' => $primaryConfig['token'],
                'username' => '', // No disponible en BD, usar valor por defecto
                'timeout' => 30, // No disponible en BD, usar valor por defecto
                'enabled' => true,
                'source' => 'database',
                'warehouse_id' => $primaryConfig['id_warehouses'],
                'numeracion' => $primaryConfig['numeracion'],
                'facturador' => $primaryConfig['facturador'], // Mantener campo original
                'all_warehouses' => $configs, // Para acceso completo a todas las configuraciones
                'updated_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo configuración desde BD', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Obtener company_id del tenant
     */
    private static function getCompanyIdFromTenant(Tenant $tenant): ?int
    {
        // Prioridad 1: Campo directo en el modelo tenant
        if ($tenant->company_id) {
            Log::debug('company_id obtenido desde campo directo', [
                'tenant_id' => $tenant->id,
                'company_id' => $tenant->company_id
            ]);
            return $tenant->company_id;
        }

        // Prioridad 2: Configuración en settings
        if (isset($tenant->settings['company_id'])) {
            Log::debug('company_id obtenido desde settings', [
                'tenant_id' => $tenant->id,
                'company_id' => $tenant->settings['company_id']
            ]);
            return $tenant->settings['company_id'];
        }

        Log::warning('company_id no encontrado en tenant', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'has_company_id_field' => !is_null($tenant->company_id),
            'has_settings_company_id' => isset($tenant->settings['company_id'])
        ]);

        return null;
    }

    /**
     * Obtener warehouses por company_id desde BD Central (RAP)
     * Usa conexión 'central' explícitamente para acceder a RAP
     */
    private static function getWarehousesByCompanyId(int $companyId)
    {
        try {
            // Forzar uso de conexión central (RAP) desde contexto tenant
            $warehouses = VntWarehouse::on('central')
                ->where('companyId', $companyId)
                ->where('status', true)
                ->get();

            Log::debug('Consultando warehouses desde BD Central (RAP)', [
                'company_id' => $companyId,
                'connection' => 'central',
                'warehouses_found' => $warehouses->count(),
                'warehouse_ids' => $warehouses->pluck('id')->toArray()
            ]);

            return $warehouses;

        } catch (\Exception $e) {
            Log::error('Error consultando warehouses desde BD Central', [
                'company_id' => $companyId,
                'connection' => 'central',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect(); // Retornar colección vacía en caso de error
        }
    }

    /**
     * Obtener configuración de invoice por warehouse_id desde BD Central
     */
    private static function getInvoiceConfigByWarehouse(int $warehouseId): ?array
    {
        try {
            // Usar conexión tenant para cnf_invoices
            $config = CnfInvoice::on('tenant')
                ->byWarehouse($warehouseId)
                ->first();

            if (!$config) {
                Log::debug('No se encontró configuración cnf_invoices para warehouse', [
                    'warehouse_id' => $warehouseId,
                    'connection' => 'tenant'
                ]);
                return null;
            }

            Log::debug('Configuración cnf_invoices encontrada', [
                'warehouse_id' => $warehouseId,
                'config_id' => $config->id,
                'has_token' => !empty($config->token),
                'connection' => 'tenant'
            ]);

            return [
                'id' => $config->id,
                'token' => $config->token,
                'id_warehouses' => $config->id_warehouses,
                'numeracion' => $config->numeracion,
                'facturador' => $config->facturador
            ];

        } catch (\Exception $e) {
            Log::error('Error consultando cnf_invoices desde BD Tenant', [
                'warehouse_id' => $warehouseId,
                'connection' => 'tenant',
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Verificar si un tenant tiene configuración en BD
     */
    public static function hasConfigurationInDatabase(Tenant $tenant): bool
    {
        return self::getFacturacionConfigFromDatabase($tenant) !== null;
    }

    /**
     * Obtener todas las configuraciones de warehouses para un tenant
     */
    public static function getAllWarehouseConfigs(Tenant $tenant): array
    {
        $config = self::getFacturacionConfigFromDatabase($tenant);
        return $config['all_warehouses'] ?? [];
    }

    /**
     * Obtener configuración específica por warehouse_id
     */
    public static function getConfigByWarehouseId(int $warehouseId): ?array
    {
        return self::getInvoiceConfigByWarehouse($warehouseId);
    }

    /**
     * MÉTODO OPTIMIZADO: Obtener configuración por user_id
     * Flujo: users → vnt_contacts → warehouseId → cnf_invoices
     * Mucho más eficiente que buscar por company_id
     */
    public static function getFacturacionConfigByUser(int $userId): ?array
    {
        try {
            Log::info('🚀 INICIO getFacturacionConfigByUser (OPTIMIZADO)', [
                'user_id' => $userId
            ]);

            // 1. Buscar contact_id del usuario en tabla users (RAP)
            $user = \DB::connection('central')
                ->table('users')
                ->where('id', $userId)
                ->first(['contact_id', 'email']);

            if (!$user || !$user->contact_id) {
                Log::warning('❌ Usuario no tiene contact_id', [
                    'user_id' => $userId,
                    'user_found' => !is_null($user),
                    'contact_id' => $user->contact_id ?? null
                ]);
                return null;
            }

            Log::info('👤 Usuario encontrado en RAP', [
                'user_id' => $userId,
                'email' => $user->email,
                'contact_id' => $user->contact_id
            ]);

            // 2. Buscar warehouseId en vnt_contacts (RAP)
            $contact = \DB::connection('central')
                ->table('vnt_contacts')
                ->where('id', $user->contact_id)
                ->first(['warehouseId', 'firstName', 'lastName']);

            if (!$contact || !$contact->warehouseId) {
                Log::warning('❌ Contacto no tiene warehouse asignado', [
                    'user_id' => $userId,
                    'contact_id' => $user->contact_id,
                    'contact_found' => !is_null($contact),
                    'warehouse_id' => $contact->warehouseId ?? null
                ]);
                return null;
            }

            Log::info('🏪 Warehouse encontrado via contacto', [
                'user_id' => $userId,
                'contact_id' => $user->contact_id,
                'warehouse_id' => $contact->warehouseId,
                'contact_name' => trim($contact->firstName . ' ' . $contact->lastName)
            ]);

            // 3. Buscar configuración en cnf_invoices (Tenant)
            $invoiceConfig = self::getInvoiceConfigByWarehouse($contact->warehouseId);

            if (!$invoiceConfig) {
                Log::warning('❌ No hay configuración cnf_invoices para warehouse', [
                    'user_id' => $userId,
                    'warehouse_id' => $contact->warehouseId
                ]);
                return null;
            }

            Log::info('✅ CONFIGURACIÓN ENCONTRADA (método optimizado)', [
                'user_id' => $userId,
                'warehouse_id' => $contact->warehouseId,
                'config_id' => $invoiceConfig['id'],
                'has_token' => !empty($invoiceConfig['token'])
            ]);

            // 4. Retornar configuración formateada
            return [
                'base_url' => $invoiceConfig['facturador'], // facturador = base_url
                'token' => $invoiceConfig['token'],
                'username' => '',
                'timeout' => 30,
                'enabled' => true,
                'source' => 'database_optimized',
                'warehouse_id' => $invoiceConfig['id_warehouses'],
                'numeracion' => $invoiceConfig['numeracion'],
                'facturador' => $invoiceConfig['facturador'],
                'user_id' => $userId,
                'contact_id' => $user->contact_id,
                'contact_name' => trim($contact->firstName . ' ' . $contact->lastName),
                'updated_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error en método optimizado getFacturacionConfigByUser', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}