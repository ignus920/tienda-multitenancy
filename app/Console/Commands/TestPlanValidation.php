<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tenant\Inventory\CategoriesService;
use App\Models\Tenant\Items\Category;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class TestPlanValidation extends Command
{
    protected $signature = 'test:plan-validation {tenant_id?}';
    protected $description = 'Test plan validation for electronic invoicing';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id') ?? session('tenant_id');

        if (!$tenantId) {
            $this->error('❌ Debe especificar un tenant_id o tener una sesión activa');
            return;
        }

        $this->info('🔍 Probando validación de planes para facturación electrónica');
        $this->line('');

        try {
            // Configurar tenant
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("❌ Tenant con ID {$tenantId} no encontrado");
                return;
            }

            $this->info("🏢 Tenant: {$tenant->name} (ID: {$tenantId})");

            // Establecer conexión tenant
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);
            tenancy()->initialize($tenant);

            $this->line('');

            // Instanciar el servicio que usa HasCompanyConfiguration
            $categoriesService = new CategoriesService();

            // Usar reflection para acceder al método privado
            $reflection = new \ReflectionClass($categoriesService);
            $method = $reflection->getMethod('canUseElectronicInvoicing');
            $method->setAccessible(true);

            // Ejecutar la validación
            $result = $method->invoke($categoriesService);

            $this->displayValidationResult($result);

            // Mostrar información adicional
            $this->showAdditionalInfo($categoriesService);

        } catch (\Exception $e) {
            $this->error('❌ Error durante la prueba: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }
    }

    private function displayValidationResult($result)
    {
        $this->line('📋 Resultado de la validación:');
        $this->line('');

        if ($result['allowed']) {
            $this->info('✅ ACCESO PERMITIDO');
            $this->line("   Razón: {$result['reason']}");
            $this->line("   Mensaje: {$result['message']}");

            if (isset($result['option_value'])) {
                $this->line("   Valor de opción: {$result['option_value']}");
            }
        } else {
            $this->error('❌ ACCESO DENEGADO');
            $this->line("   Razón: {$result['reason']}");
            $this->line("   Mensaje: {$result['message']}");
        }

        $this->line('');
    }

    private function showAdditionalInfo($categoriesService)
    {
        try {
            // Usar reflection para acceder a propiedades protegidas
            $reflection = new \ReflectionClass($categoriesService);

            $companyIdProperty = $reflection->getProperty('currentCompanyId');
            $companyIdProperty->setAccessible(true);
            $companyId = $companyIdProperty->getValue($categoriesService);

            $plainIdProperty = $reflection->getProperty('currentPlainId');
            $plainIdProperty->setAccessible(true);
            $plainId = $plainIdProperty->getValue($categoriesService);

            $this->line('📊 Información adicional:');
            $this->line("   Company ID: " . ($companyId ?? 'NULL'));
            $this->line("   Plan ID: " . ($plainId ?? 'NULL'));

            if ($companyId) {
                // Obtener valor directo de la opción 8
                $getOptionValueMethod = $reflection->getMethod('getOptionValue');
                $getOptionValueMethod->setAccessible(true);
                $optionValue = $getOptionValueMethod->invoke($categoriesService, 8);

                $this->line("   Valor directo option_id 8: " . ($optionValue ?? 'NULL'));

                // Obtener todas las opciones habilitadas
                $getEnabledOptionsMethod = $reflection->getMethod('getEnabledOptions');
                $getEnabledOptionsMethod->setAccessible(true);
                $enabledOptions = $getEnabledOptionsMethod->invoke($categoriesService);

                $this->line("   Opciones habilitadas: " . implode(', ', $enabledOptions));
                $this->line("   ¿Opción 8 en lista?: " . (in_array(8, $enabledOptions) ? 'SÍ' : 'NO'));
            }

        } catch (\Exception $e) {
            $this->line('⚠️  No se pudo obtener información adicional: ' . $e->getMessage());
        }

        $this->line('');
    }
}