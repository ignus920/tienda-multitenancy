<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxesSeeder extends Seeder
{
    /**
     * Seed de impuestos por defecto para el tenant.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando TaxesSeeder');

        // Verificar que existe la tabla cnf_taxes
        if (!$this->tableExists('cnf_taxes')) {
            Log::warning('⚠️ Tabla cnf_taxes no existe, saltando seeder');
            return;
        }

        $taxes = [
            [
                'id' => 2,
                'name' => 'Iva 5%',
                'percentage' => 5,
                'status' => 1,
                'api_data_id' => null,
                'inventoryAccount' => 5023,
                'inventariablePurchaseAccount' => 5098,
                'categoryAccount' => 5063,
                'created_at' => '2024-05-25 07:54:30',
                'updated_at' => '2024-05-25 07:54:00',
            ],
            [
                'id' => 3,
                'name' => 'Iva 19%',
                'percentage' => 19,
                'status' => 1,
                'api_data_id' => null,
                'inventoryAccount' => 5023,
                'inventariablePurchaseAccount' => 5098,
                'categoryAccount' => 5063,
                'created_at' => '2024-05-25 07:54:33',
                'updated_at' => '2024-05-25 07:54:18',
            ],
            [
                'id' => 6,
                'name' => 'Exento',
                'percentage' => 0,
                'status' => 1,
                'api_data_id' => null,
                'inventoryAccount' => 5023,
                'inventariablePurchaseAccount' => 5098,
                'categoryAccount' => 5063,
                'created_at' => '2024-05-25 07:54:37',
                'updated_at' => '2024-05-25 07:54:22',
            ],
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($taxes as $tax) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('cnf_taxes')
                ->where('id', $tax['id'])
                ->exists();

            if ($exists) {
                // Actualizar
                DB::connection('tenant')
                    ->table('cnf_taxes')
                    ->where('id', $tax['id'])
                    ->update([
                        'name' => $tax['name'],
                        'percentage' => $tax['percentage'],
                        'status' => $tax['status'],
                        'api_data_id' => $tax['api_data_id'],
                        'inventoryAccount' => $tax['inventoryAccount'],
                        'inventariablePurchaseAccount' => $tax['inventariablePurchaseAccount'],
                        'categoryAccount' => $tax['categoryAccount'],
                        'updated_at' => now(),
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('cnf_taxes')
                    ->insert($tax);
                $insertedCount++;
            }
        }

        Log::info('✅ Impuestos insertados exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => count($taxes)
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
