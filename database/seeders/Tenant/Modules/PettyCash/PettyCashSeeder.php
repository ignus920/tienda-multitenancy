<?php

namespace Database\Seeders\Tenant\Modules\PettyCash;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PettyCashSeeder extends Seeder
{
    /**
     * Seed de datos iniciales para el módulo de Caja Menor.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando PettyCashSeeder');

        $this->seedReasonsPettyCash();

        Log::info('✅ PettyCashSeeder completado');
    }

    /**
     * Seed de razones de caja menor
     */
    protected function seedReasonsPettyCash(): void
    {
        if (!$this->tableExists('vnt_reasons_petty_cash')) {
            Log::warning('⚠️ Tabla vnt_reasons_petty_cash no existe');
            return;
        }

        Log::info('📝 Insertando razones de caja menor');

        $reasons = [
            [
                'id' => 1,
                'name' => 'Ventas',
                'status' => 1,
                'type' => 'i',
                'created_at' => '2025-11-16 18:14:47',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Devolucion de vale empleados',
                'status' => 1,
                'type' => 'i',
                'created_at' => '2025-11-16 18:15:02',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Pago de factura',
                'status' => 1,
                'type' => 'e',
                'created_at' => '2025-11-16 18:15:10',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Vale empleado',
                'status' => 1,
                'type' => 'e',
                'created_at' => '2025-11-16 18:15:57',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'name' => 'Apertura',
                'status' => 1,
                'type' => 'i',
                'created_at' => '2025-11-20 21:07:25',
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'name' => 'Anticipo',
                'status' => 1,
                'type' => 'i',
                'created_at' => '2025-11-26 14:06:53',
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ];

        foreach ($reasons as $reason) {
            DB::connection('tenant')->table('vnt_reasons_petty_cash')->updateOrInsert(
                ['id' => $reason['id']],
                $reason
            );
        }

        Log::info('✅ Razones de caja menor insertadas correctamente');
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
