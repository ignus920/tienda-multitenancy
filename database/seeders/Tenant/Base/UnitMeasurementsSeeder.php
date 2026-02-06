<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitMeasurementsSeeder extends Seeder
{
    /**
     * Seed de unidades de medida por defecto para el tenant.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando UnitMeasurementsSeeder');

        // Verificar que existe la tabla inv_unit_measurements
        if (!$this->tableExists('inv_unit_measurements')) {
            Log::warning('⚠️ Tabla inv_unit_measurements no existe, saltando seeder');
            return;
        }

        $unitMeasurements = [
            ['id' => 1, 'description' => 'CAJA X 10', 'status' => 1, 'quantity' => 10, 'created_at' => '2025-11-12 16:30:00', 'updated_at' => null],
            ['id' => 2, 'description' => 'CAJA X 100', 'status' => 1, 'quantity' => 100, 'created_at' => '2025-11-12 16:30:00', 'updated_at' => null],
            ['id' => 3, 'description' => 'CAJA X 1000', 'status' => 1, 'quantity' => 1000, 'created_at' => '2025-11-12 16:30:00', 'updated_at' => null],
            ['id' => 4, 'description' => 'CAJA X 12', 'status' => 1, 'quantity' => 12, 'created_at' => '2025-11-12 16:30:00', 'updated_at' => null],
            ['id' => 5, 'description' => 'CAJA X 13', 'status' => 1, 'quantity' => 13, 'created_at' => '2025-11-12 16:30:01', 'updated_at' => null],
            ['id' => 6, 'description' => 'CAJA X 15', 'status' => 1, 'quantity' => 15, 'created_at' => '2025-11-12 16:30:01', 'updated_at' => null],
            ['id' => 7, 'description' => 'CAJA X 20', 'status' => 1, 'quantity' => 20, 'created_at' => '2025-11-12 16:30:01', 'updated_at' => null],
            ['id' => 8, 'description' => 'CAJA X 200', 'status' => 1, 'quantity' => 200, 'created_at' => '2025-11-12 16:30:01', 'updated_at' => null],
            ['id' => 9, 'description' => 'CAJA X 24', 'status' => 1, 'quantity' => 24, 'created_at' => '2025-11-12 16:30:02', 'updated_at' => null],
            ['id' => 10, 'description' => 'CAJA X 25', 'status' => 1, 'quantity' => 25, 'created_at' => '2025-11-12 16:30:02', 'updated_at' => null],
            ['id' => 11, 'description' => 'CAJA X 28', 'status' => 1, 'quantity' => 28, 'created_at' => '2025-11-12 16:30:02', 'updated_at' => null],
            ['id' => 12, 'description' => 'CAJA X 30', 'status' => 1, 'quantity' => 30, 'created_at' => '2025-11-12 16:30:03', 'updated_at' => null],
            ['id' => 13, 'description' => 'CAJA X 4', 'status' => 1, 'quantity' => 4, 'created_at' => '2025-11-12 16:30:03', 'updated_at' => null],
            ['id' => 14, 'description' => 'CAJA X 5', 'status' => 1, 'quantity' => 5, 'created_at' => '2025-11-12 16:30:03', 'updated_at' => null],
            ['id' => 15, 'description' => 'CAJA X 50', 'status' => 1, 'quantity' => 50, 'created_at' => '2025-11-12 16:30:03', 'updated_at' => null],
            ['id' => 16, 'description' => 'CAJA X 500', 'status' => 1, 'quantity' => 500, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 17, 'description' => 'CAJA X 6', 'status' => 1, 'quantity' => 6, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 18, 'description' => 'CAJA X 8', 'status' => 1, 'quantity' => 8, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 19, 'description' => 'CAJA X 9', 'status' => 1, 'quantity' => 9, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 20, 'description' => 'PAQUETE X 10', 'status' => 1, 'quantity' => 10, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 21, 'description' => 'PAQUETE X 100', 'status' => 1, 'quantity' => 100, 'created_at' => '2025-11-12 16:30:04', 'updated_at' => null],
            ['id' => 22, 'description' => 'PAQUETE X 12', 'status' => 1, 'quantity' => 12, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 23, 'description' => 'PAQUETE X 150', 'status' => 1, 'quantity' => 150, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 24, 'description' => 'PAQUETE X 16', 'status' => 1, 'quantity' => 16, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 25, 'description' => 'PAQUETE X 20', 'status' => 1, 'quantity' => 20, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 26, 'description' => 'PAQUETE X 25', 'status' => 1, 'quantity' => 25, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 27, 'description' => 'PAQUETE X 3', 'status' => 1, 'quantity' => 3, 'created_at' => '2025-11-12 16:30:05', 'updated_at' => null],
            ['id' => 28, 'description' => 'PAQUETE X 4', 'status' => 1, 'quantity' => 4, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => null],
            ['id' => 29, 'description' => 'PAQUETE X 4', 'status' => 0, 'quantity' => 4, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => '2025-11-18 15:39:05'],
            ['id' => 30, 'description' => 'PAQUETE X 40', 'status' => 1, 'quantity' => 40, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => null],
            ['id' => 31, 'description' => 'PAQUETE X 5', 'status' => 1, 'quantity' => 5, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => null],
            ['id' => 32, 'description' => 'PAQUETE X 50', 'status' => 1, 'quantity' => 50, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => null],
            ['id' => 33, 'description' => 'PAQUETE X 6', 'status' => 1, 'quantity' => 6, 'created_at' => '2025-11-12 16:30:06', 'updated_at' => null],
            ['id' => 34, 'description' => 'PAQUETE X 80', 'status' => 1, 'quantity' => 80, 'created_at' => '2025-11-12 16:30:07', 'updated_at' => null],
            ['id' => 35, 'description' => 'UNIDAD', 'status' => 1, 'quantity' => 1, 'created_at' => '2025-11-12 16:30:07', 'updated_at' => null],
            ['id' => 36, 'description' => 'BOTELLA 600 ML', 'status' => 1, 'quantity' => 600, 'created_at' => '2025-11-12 21:21:41', 'updated_at' => '2025-11-12 21:21:41'],
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($unitMeasurements as $unit) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('inv_unit_measurements')
                ->where('id', $unit['id'])
                ->exists();

            if ($exists) {
                // Actualizar
                DB::connection('tenant')
                    ->table('inv_unit_measurements')
                    ->where('id', $unit['id'])
                    ->update([
                        'description' => $unit['description'],
                        'status' => $unit['status'],
                        'quantity' => $unit['quantity'],
                        'updated_at' => $unit['updated_at'],
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('inv_unit_measurements')
                    ->insert($unit);
                $insertedCount++;
            }
        }

        Log::info('✅ Unidades de medida insertadas exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => count($unitMeasurements)
        ]);
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
