<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusSeeder extends Seeder
{
    /**
     * Seed de estados comunes para el tenant.
     * Estos son valores base que todos los tenants necesitan.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando StatusSeeder');

        // Ejemplo: Insertar estados comunes si la tabla existe
        // Ajusta según tus tablas reales

        // Verificar si existe tabla de estados (ejemplo)
        if ($this->tableExists('cnf_status')) {
            $this->seedStatusTable();
        }

        Log::info('✅ StatusSeeder completado');
    }

    /**
     * Seed para tabla de estados
     */
    protected function seedStatusTable(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'Activo', 'code' => 'active'],
            ['id' => 2, 'name' => 'Inactivo', 'code' => 'inactive'],
            ['id' => 3, 'name' => 'Pendiente', 'code' => 'pending'],
            ['id' => 4, 'name' => 'Cancelado', 'code' => 'cancelled'],
        ];

        foreach ($statuses as $status) {
            DB::connection('tenant')->table('cnf_status')->updateOrInsert(
                ['id' => $status['id']],
                $status
            );
        }

        Log::info('✅ Estados base insertados', ['count' => count($statuses)]);
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
