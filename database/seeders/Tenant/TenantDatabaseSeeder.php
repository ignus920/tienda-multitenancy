<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders para una base de datos tenant.
     * Este seeder se ejecuta automáticamente después de las migraciones.
     */
    public function run(): void
    {
        Log::info('🌱 Iniciando seeders para tenant');

        // Verificar que estamos en la conexión correcta
        $currentDb = DB::connection('tenant')->getDatabaseName();
        Log::info('📊 Base de datos tenant actual', ['database' => $currentDb]);

        try {
            // 1. Ejecutar seeders base (comunes a todos los tenants)
            $this->seedBaseData();

            // 2. Ejecutar seeders específicos por módulo
            $this->seedModuleData();

            Log::info('✅ Seeders completados exitosamente para tenant', ['database' => $currentDb]);

        } catch (\Exception $e) {
            Log::error('❌ Error ejecutando seeders para tenant', [
                'database' => $currentDb,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Ejecuta seeders base comunes a todos los tenants
     */
    protected function seedBaseData(): void
    {
        Log::info('📦 Ejecutando seeders base');

        $baseSeederClasses = [
            Base\StatusSeeder::class,
            Base\DefaultConfigSeeder::class,
            Base\DefaultStoreSeeder::class,  // ← Bodega PRINCIPAL
            Base\CompanyOptionsSeeder::class,  // ← Opciones de configuración
            Base\TaxesSeeder::class,  // ← Impuestos por defecto
            Base\UnitMeasurementsSeeder::class,  // ← Unidades de medida por defecto
            // Agregar más seeders base aquí
        ];

        foreach ($baseSeederClasses as $seederClass) {
            if (class_exists($seederClass)) {
                Log::info("🔄 Ejecutando seeder base: {$seederClass}");
                $this->call($seederClass);
            } else {
                Log::warning("⚠️ Seeder base no encontrado: {$seederClass}");
            }
        }
    }

    /**
     * Ejecuta seeders específicos según los módulos activos del tenant
     */
    protected function seedModuleData(): void
    {
        Log::info('📦 Ejecutando seeders por módulo');

        // Obtener módulos activos del tenant desde la configuración
        // Esto se puede hacer consultando la BD central o desde configuración
        $activeModules = $this->getActiveModules();

        foreach ($activeModules as $module) {
            $this->seedModule($module);
        }
    }

    /**
     * Ejecuta seeders para un módulo específico
     */
    protected function seedModule(string $moduleName): void
    {
        $seederClass = "Database\\Seeders\\Tenant\\Modules\\{$moduleName}\\{$moduleName}Seeder";

        if (class_exists($seederClass)) {
            Log::info("🔄 Ejecutando seeder de módulo: {$moduleName}");
            $this->call($seederClass);
        } else {
            Log::info("ℹ️ No hay seeder para el módulo: {$moduleName}");
        }
    }

    /**
     * Obtiene los módulos activos para el tenant actual
     */
    protected function getActiveModules(): array
    {
        // Por ahora retornamos módulos comunes
        // Esto se puede mejorar consultando la BD central
        return [
            'Inventory',
            'Sales',
            'Customers',
            'PettyCash',
            // Agregar más módulos según necesidad
        ];
    }
}
