<?php

namespace Database\Seeders\Tenant\Modules\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventorySeeder extends Seeder
{
    /**
     * Seed de datos iniciales para el módulo de Inventario.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando InventorySeeder');

        // NOTA: Las unidades de medida ahora se insertan desde Base\UnitMeasurementsSeeder
        // No ejecutamos seedUnitMeasurements() aquí para evitar conflictos

        // Seed de categorías por defecto
        if ($this->tableExists('inv_categories')) {
            $this->seedCategories();
        }

        // Seed de marcas por defecto
        if ($this->tableExists('inv_brands')) {
            $this->seedBrands();
        }

        if ($this->tableExists('inv_reasons')) {
            $this->seedInvReason();
        }

        if ($this->tableExists('imp_status')) {
            $this->call(ImpStatusSeeder::class);
        }

        if ($this->tableExists('imp_labels')) {
            $this->call(ImpLabelsSeeder::class);
        }

        Log::info('✅ InventorySeeder completado');
    }

    protected function seedInvReason(): void
    {
        Log::info('📝 Insertando razones de inventario');

        // Lógica para seed de razones de inventario
        $reasons = [
            ['name' => 'Compras', 'type' => 'e', 'status' => 1],
            ['name' => 'Ajuste', 'type' => 'e', 'status' => 1],
            ['name' => 'Ajuste', 'type' => 's', 'status' => 1],
            ['name' => 'Deterioro', 'type' => 's', 'status' => 1],
            ['name' => 'Consumo Interno', 'type' => 'e', 'status' => 1],
            ['name' => 'Consumo Interno', 'type' => 's', 'status' => 1],
            ['name' => 'devolución nota crédito', 'type' => 'e', 'status' => 1]
        ];

        foreach ($reasons as $reason) {
            DB::connection('tenant')->table('inv_reasons')->updateOrInsert(
                ['name' => $reason['name'], 'type' => $reason['type']],
                $reason
            );
        }

        Log::info('✅ Razones de inventario insertadas', ['count' => count($reasons)]);
    }
    /**
     * Seed de unidades de medida
     * DEPRECADO: Ahora se usa Base\UnitMeasurementsSeeder
     */
    protected function seedUnitMeasurements(): void
    {
        // Este método ya no se usa
        // Las unidades de medida se insertan desde Base\UnitMeasurementsSeeder
        Log::info('ℹ️ seedUnitMeasurements deprecado - usar Base\UnitMeasurementsSeeder');
    }

    /**
     * Seed de categorías por defecto
     */
    protected function seedCategories(): void
    {
        // $categories = [
        //     ['name' => 'General', 'description' => 'Categoría general', 'status' => 1],
        //     ['name' => 'Productos', 'description' => 'Productos para venta', 'status' => 1],
        //     ['name' => 'Servicios', 'description' => 'Servicios ofrecidos', 'status' => 1],
        // ];

        // foreach ($categories as $category) {
        //     DB::connection('tenant')->table('inv_categories')->updateOrInsert(
        //         ['name' => $category['name']],
        //         $category
        //     );
        // }

        Log::info('✅ Categorías insertadas', ['count' => 0]);
    }

    /**
     * Seed de marcas por defecto
     */
    protected function seedBrands(): void
    {
        // $brands = [
        //     ['name' => 'Sin Marca', 'description' => 'Productos sin marca específica', 'status' => 1],
        //     ['name' => 'Genérico', 'description' => 'Productos genéricos', 'status' => 1],
        // ];

        // foreach ($brands as $brand) {
        //     DB::connection('tenant')->table('inv_brands')->updateOrInsert(
        //         ['name' => $brand['name']],
        //         $brand
        //     );
        // }

        Log::info('✅ Marcas insertadas', ['count' => 0]);
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
