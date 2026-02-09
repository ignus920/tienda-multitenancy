<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefaultConfigSeeder extends Seeder
{
    /**
     * Seed de configuraciones por defecto para el tenant.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando DefaultConfigSeeder');

        // NOTA: Este seeder ya no inserta configuraciones por defecto
        // Las configuraciones se obtienen desde vnt_options_plains según el plan seleccionado
        // Ver CompanyOptionsSeeder para más detalles

        Log::info('✅ DefaultConfigSeeder completado (sin acciones - configuraciones vienen de CompanyOptionsSeeder)');
    }

    /**
     * Seed para configuraciones de empresa
     * DEPRECADO: Las configuraciones ahora vienen de vnt_options_plains
     */
    protected function seedCompanyOptions(): void
    {
        // Este método ya no se usa
        // Las configuraciones se obtienen desde CompanyOptionsSeeder
        // que consulta vnt_options_plains según el plain_id del tenant
        Log::info('ℹ️ seedCompanyOptions deprecado - usar CompanyOptionsSeeder');
    }

    /**
     * Verifica si una tabla existe en la BD tenant
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            return DB::connection('tenant')
                ->getSchemaBuilder()
                ->hasTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }
}
