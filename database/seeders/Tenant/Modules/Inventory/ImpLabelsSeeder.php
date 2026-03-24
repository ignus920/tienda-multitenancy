<?php

namespace Database\Seeders\Tenant\Modules\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImpLabelsSeeder extends Seeder
{
    /**
     * Seed de dato inicial para el modulo de Etiquetas
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando ImpLabelsSeeder');

        // Verificar que existe la tabla imp_status
        if (!$this->tableExists('imp_labels')) {
            Log::warning('⚠️ Tabla imp_labels no existe, saltando seeder');
            return;
        }

        $label = [
            [
                'id' => 1,
                'name' => 'ASAP',
                'asap' => 1,
                'description' => 'ASAP',
                'status' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($label as $lb) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('imp_labels')
                ->where('id', $lb['id'])
                ->exists();

            if ($exists) {
                //Actualizar
                DB::connection('tenant')
                    ->table('imp_labels')
                    ->where('id', $lb['id'])
                    ->update([
                        'name' => $lb['name'],
                        'asap' => $lb['asap'],
                        'description' => $lb['description'],
                        'status' => $lb['status'],
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('imp_labels')
                    ->insert($lb);
                $insertedCount++;
            }
        }
        Log::info('✅ Etiqueta insertada exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => count($label)
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
