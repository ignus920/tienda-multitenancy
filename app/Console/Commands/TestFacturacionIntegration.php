<?php

namespace App\Console\Commands;

use App\Models\Auth\Tenant;
use App\Services\Facturacion\TenantConfigManager;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use Illuminate\Console\Command;

class TestFacturacionIntegration extends Command
{
    protected $signature = 'test:facturacion-integration {tenant_id?}';
    protected $description = 'Probar la integración de configuración de facturación (settings + BD)';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');

        if ($tenantId) {
            $this->testSpecificTenant($tenantId);
        } else {
            $this->testAllTenants();
        }
    }

    private function testSpecificTenant(string $tenantId)
    {
        $this->info("🧪 Probando configuración de facturación para tenant: {$tenantId}");
        $this->newLine();

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("❌ Tenant no encontrado: {$tenantId}");
            return;
        }

        $this->displayTenantInfo($tenant);
        $this->testConfigurationSources($tenant);
        $this->testApiClientIntegration($tenant);
    }

    private function testAllTenants()
    {
        $this->info("🧪 Probando configuración de facturación para todos los tenants");
        $this->newLine();

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn("⚠️ No se encontraron tenants para probar");
            return;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Tenant: {$tenant->id} ({$tenant->name}) ---");
            $this->testConfigurationSources($tenant);
            $this->newLine();
        }
    }

    private function displayTenantInfo(Tenant $tenant)
    {
        $this->info("📋 Información del Tenant:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $tenant->id],
                ['Nombre', $tenant->name],
                ['Company ID', $tenant->company_id ?? 'No asignado'],
                ['Email', $tenant->email],
                ['Activo', $tenant->is_active ? 'Sí' : 'No'],
            ]
        );
        $this->newLine();
    }

    private function testConfigurationSources(Tenant $tenant)
    {
        // 1. Verificar configuración en settings
        $settingsConfig = $tenant->settings['facturacion'] ?? null;
        $hasSettings = $settingsConfig && !empty($settingsConfig['token']) && ($settingsConfig['enabled'] ?? false);

        $this->info("🔧 Configuración desde Settings:");
        if ($hasSettings) {
            $this->line("✅ Configuración válida encontrada en settings");
            $this->line("   - Base URL: " . ($settingsConfig['base_url'] ?? 'No definida'));
            $this->line("   - Token: " . (strlen($settingsConfig['token']) > 10 ? substr($settingsConfig['token'], 0, 10) . '...' : $settingsConfig['token']));
            $this->line("   - Username: " . ($settingsConfig['username'] ?? 'No definido'));
            $this->line("   - Enabled: " . ($settingsConfig['enabled'] ? 'Sí' : 'No'));
        } else {
            $this->line("❌ No se encontró configuración válida en settings");
        }

        // 2. Verificar configuración en base de datos
        $this->newLine();
        $this->info("🗄️ Configuración desde Base de Datos:");

        $databaseConfig = DatabaseConfigService::getFacturacionConfigFromDatabase($tenant);
        if ($databaseConfig) {
            $this->line("✅ Configuración válida encontrada en BD");
            $this->line("   - Base URL: " . ($databaseConfig['base_url'] ?? 'No definida'));
            $this->line("   - Token: " . (strlen($databaseConfig['token']) > 10 ? substr($databaseConfig['token'], 0, 10) . '...' : $databaseConfig['token']));
            $this->line("   - Warehouse ID: " . ($databaseConfig['warehouse_id'] ?? 'No definido'));
            $this->line("   - Numeración: " . ($databaseConfig['numeracion'] ?? 'No definida'));
            $this->line("   - Facturador: " . ($databaseConfig['facturador'] ?? 'No definido'));

            // Mostrar todos los warehouses
            $allWarehouses = $databaseConfig['all_warehouses'] ?? [];
            if (!empty($allWarehouses)) {
                $this->line("   - Total warehouses configurados: " . count($allWarehouses));
                foreach ($allWarehouses as $wh) {
                    $this->line("     * {$wh['warehouse_name']} (ID: {$wh['warehouse_id']})");
                }
            }
        } else {
            $this->line("❌ No se encontró configuración válida en BD");
            if ($tenant->company_id) {
                $this->line("   - Company ID disponible: {$tenant->company_id}");
                $this->line("   - Verificar si existen warehouses y configuraciones cnf_invoices");
            } else {
                $this->line("   - Company ID no asignado al tenant");
            }
        }

        // 3. Verificar configuración final (prioridad)
        $this->newLine();
        $this->info("⚙️ Configuración Final (TenantConfigManager):");

        $finalConfig = TenantConfigManager::getFacturacionConfig($tenant);
        if ($finalConfig) {
            $source = $finalConfig['source'] ?? 'unknown';
            $this->line("✅ Configuración activa (Fuente: {$source})");
            $this->line("   - Base URL: " . ($finalConfig['base_url'] ?? 'No definida'));
            $this->line("   - Token: " . (strlen($finalConfig['token']) > 10 ? substr($finalConfig['token'], 0, 10) . '...' : $finalConfig['token']));
            $this->line("   - Enabled: " . ($finalConfig['enabled'] ? 'Sí' : 'No'));
        } else {
            $this->line("❌ No hay configuración activa para este tenant");
        }
    }

    private function testApiClientIntegration(Tenant $tenant)
    {
        $this->newLine();
        $this->info("🚀 Prueba de ApiClient:");

        try {
            $apiClient = ApiClient::forTenant($tenant);

            if ($apiClient) {
                $this->line("✅ ApiClient creado exitosamente");

                // Intentar hacer una petición de prueba (solo mostrar URL)
                $reflection = new \ReflectionClass($apiClient);
                $baseUrlProperty = $reflection->getProperty('baseUrl');
                $baseUrlProperty->setAccessible(true);
                $baseUrl = $baseUrlProperty->getValue($apiClient);

                $tokenProperty = $reflection->getProperty('token');
                $tokenProperty->setAccessible(true);
                $token = $tokenProperty->getValue($apiClient);

                $this->line("   - Base URL configurada: " . ($baseUrl ?? 'No definida'));
                $this->line("   - Token configurado: " . ($token ? 'Sí' : 'No'));

                if ($baseUrl && $token) {
                    $this->line("✅ ApiClient está listo para realizar peticiones");
                } else {
                    $this->line("⚠️ ApiClient creado pero falta configuración");
                }
            } else {
                $this->line("❌ No se pudo crear ApiClient");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error al crear ApiClient: " . $e->getMessage());
        }
    }
}