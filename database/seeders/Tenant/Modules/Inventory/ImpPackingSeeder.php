<?php

namespace Database\Seeders\Tenant\Modules\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImpPackingSeeder extends Seeder
{
    /**
     * Seed de datos iniciales para el modulo de Packing
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando ImpPackingSeeder');

        // Verificar que existe la tabla imp_status
        if (!$this->tableExists('imp_packing')) {
            Log::warning('⚠️ Tabla imp_packing no existe, saltando seeder');
            return;
        }

        $packings = [
            [
                'id' => 1,
                'number_packing' => 'PACK1',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'number_packing' => 'PACK2',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'number_packing' => 'PACK3',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($packings as $pk) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('imp_packing')
                ->where('id', $pk['id'])
                ->exists();

            if ($exists) {
                //Actualizar
                DB::connection('tenant')
                    ->table('imp_packing')
                    ->where('id', $pk['id'])
                    ->update([
                        'number_packing' => $pk['number_packing'],
                        'created_at' => $pk['created_at'],
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('imp_packing')
                    ->insert($pk);
                $insertedCount++;
            }
        }
        Log::info('✅ Packings insertados exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => count($packings)
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
