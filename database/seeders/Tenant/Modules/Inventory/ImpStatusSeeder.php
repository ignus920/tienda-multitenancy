<?php

namespace Database\Seeders\Tenant\Modules\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImpStatusSeeder extends Seeder
{
    /**
     * Seed de datos iniciales para el modulo de Importaciones
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando ImpStatusSeeder');

        // Verificar que existe la tabla imp_status
        if (!$this->tableExists('imp_status')) {
            Log::warning('⚠️ Tabla imp_status no existe, saltando seeder');
            return;
        }

        $status = [
            [
                'id' => 1,
                'name' => 'Solicitado',
                'translated_name' => 'Requested',
                'in_progress' => 1,
                'function' => '0',
                'supplier' => 0,
                'edition' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Cotizado',
                'translated_name' => 'Quoted',
                'in_progress' => 1,
                'function' => '0',
                'supplier' => 1,
                'edition' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Aprobado',
                'translated_name' => 'Approved',
                'in_progress' => 1,
                'function' => '0',
                'supplier' => 0,
                'edition' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'name' => 'Produccion',
                'translated_name' => 'Production',
                'in_progress' => 1,
                'function' => '0',
                'supplier' => 1,
                'edition' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 6,
                'name' => 'Packing',
                'translated_name' => 'Packing',
                'in_progress' => 1,
                'function' => 'en listar',
                'supplier' => 0,
                'edition' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 7,
                'name' => 'En transito',
                'translated_name' => 'In transit',
                'in_progress' => 1,
                'function' => 'datos de envio',
                'supplier' => 1,
                'edition' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 8,
                'name' => 'Recibido',
                'translated_name' => 'Received',
                'in_progress' => 0,
                'function' => 'datos recibido',
                'supplier' => 0,
                'edition' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 9,
                'name' => 'Retrasado',
                'translated_name' => 'Delayed',
                'in_progress' => 1,
                'function' => 'en listar',
                'supplier' => 1,
                'edition' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($status as $st) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('imp_status')
                ->where('id', $st['id'])
                ->exists();

            if ($exists) {
                //Actualizar
                DB::connection('tenant')
                    ->table('imp_status')
                    ->where('id', $st['id'])
                    ->update([
                        'name' => $st['name'],
                        'translated_name' => $st['translated_name'],
                        'in_progress' => $st['in_progress'],
                        'function' => $st['function'],
                        'supplier' => $st['supplier'],
                        'edition' => $st['edition'],
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('imp_status')
                    ->insert($st);
                $insertedCount++;
            }
        }
        Log::info('✅ Estados insertados exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => count($status)
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
