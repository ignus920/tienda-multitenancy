<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefaultStoreSeeder extends Seeder
{
    /**
     * Seed de bodega principal por defecto para el tenant.
     * Crea una bodega "PRINCIPAL" asociada al warehouse del usuario
     * y actualiza el campo store del contacto en la BD central.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando DefaultStoreSeeder');

        // Verificar que existe la tabla inv_store
        if (!$this->tableExists('inv_store')) {
            Log::warning('⚠️ Tabla inv_store no existe, saltando seeder');
            return;
        }

        // Obtener el company_id del tenant actual
        $companyId = config('tenant.current_company_id');
        
        if (!$companyId) {
            Log::warning('⚠️ No se encontró company_id en configuración, saltando seeder');
            return;
        }

        Log::info('📦 Company ID obtenido', ['company_id' => $companyId]);

        // Obtener el warehouse asociado a esta compañía desde la BD central
        $warehouse = DB::connection('central')
            ->table('vnt_warehouses')
            ->where('companyId', $companyId)
            ->where('status', 1)
            ->first();

        if (!$warehouse) {
            Log::warning('⚠️ No se encontró warehouse activo para company_id', ['company_id' => $companyId]);
            return;
        }

        Log::info('🏢 Warehouse encontrado', [
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name
        ]);

        // Verificar si ya existe una bodega con este warehouseId
        $existingStore = DB::connection('tenant')
            ->table('inv_store')
            ->where('warehouseId', $warehouse->id)
            ->where('name', 'PRINCIPAL')
            ->first();

        if ($existingStore) {
            Log::info('ℹ️ Ya existe la bodega PRINCIPAL para este warehouse', [
                'store_id' => $existingStore->id,
                'store_name' => $existingStore->name
            ]);
            
            // Actualizar contactos con esta bodega
            $this->updateContactsStore($warehouse->id, $existingStore->id);
            return;
        }

        // Crear la bodega principal
        $storeData = [
            'name' => 'PRINCIPAL',
            'warehouseId' => $warehouse->id,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Agregar descripción si la columna existe
        if ($this->columnExists('inv_store', 'description')) {
            $storeData['description'] = 'Bodega principal por defecto';
        }

        $storeId = DB::connection('tenant')->table('inv_store')->insertGetId($storeData);

        Log::info('✅ Bodega PRINCIPAL creada exitosamente', [
            'store_id' => $storeId,
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name
        ]);

        // Actualizar el campo store de los contactos asociados a este warehouse
        $this->updateContactsStore($warehouse->id, $storeId);
    }

    /**
     * Actualiza el campo store de los contactos asociados al warehouse
     */
    protected function updateContactsStore(int $warehouseId, int $storeId): void
    {
        Log::info('🔄 Actualizando campo store en contactos', [
            'warehouse_id' => $warehouseId,
            'store_id' => $storeId
        ]);

        // Buscar todos los contactos asociados a este warehouse que no tengan store asignado
        $contacts = DB::connection('central')
            ->table('vnt_contacts')
            ->where('warehouseId', $warehouseId)
            ->whereNull('store')
            ->get();

        if ($contacts->isEmpty()) {
            Log::info('ℹ️ No hay contactos sin bodega asignada para este warehouse');
            return;
        }

        Log::info('📋 Contactos encontrados sin bodega', ['count' => $contacts->count()]);

        // Actualizar cada contacto
        $updatedCount = 0;
        foreach ($contacts as $contact) {
            DB::connection('central')
                ->table('vnt_contacts')
                ->where('id', $contact->id)
                ->update([
                    'store' => $storeId,
                    'updated_at' => now()
                ]);

            $updatedCount++;
            
            Log::info('✅ Contacto actualizado', [
                'contact_id' => $contact->id,
                'contact_email' => $contact->email ?? 'N/A',
                'store_id' => $storeId
            ]);
        }

        Log::info('✅ Todos los contactos actualizados', [
            'updated_count' => $updatedCount,
            'warehouse_id' => $warehouseId,
            'store_id' => $storeId
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

    /**
     * Verifica si una columna existe en una tabla
     */
    protected function columnExists(string $tableName, string $columnName): bool
    {
        try {
            return DB::connection('tenant')
                ->getSchemaBuilder()
                ->hasColumn($tableName, $columnName);
        } catch (\Exception $e) {
            return false;
        }
    }
}
