<?php

namespace Database\Seeders\Tenant\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyOptionsSeeder extends Seeder
{
    /**
     * Seed de opciones de configuración de la compañía.
     * Copia las opciones desde la BD central según el plain_id del tenant.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando CompanyOptionsSeeder');

        // Verificar que existe la tabla cnf_company_options
        if (!$this->tableExists('cnf_company_options')) {
            Log::warning('⚠️ Tabla cnf_company_options no existe, saltando seeder');
            return;
        }

        // Obtener el tenant_id y company_id del tenant actual
        $tenantId = config('tenant.current_tenant_id');
        $companyId = config('tenant.current_company_id');
        
        if (!$tenantId || !$companyId) {
            Log::warning('⚠️ No se encontró tenant_id o company_id en configuración', [
                'tenant_id' => $tenantId,
                'company_id' => $companyId
            ]);
            return;
        }

        Log::info('📦 Datos del tenant obtenidos', [
            'tenant_id' => $tenantId,
            'company_id' => $companyId
        ]);

        // Obtener el plain_id del tenant
        $tenant = DB::connection('mysql')
            ->table('tenants')
            ->where('id', $tenantId)
            ->first();

        if (!$tenant || !$tenant->plain_id) {
            Log::warning('⚠️ No se encontró tenant o plain_id', [
                'tenant_id' => $tenantId,
                'plain_id' => $tenant->plain_id ?? null
            ]);
            return;
        }

        $plainId = $tenant->plain_id;

        Log::info('🏢 Plain ID obtenido del tenant', [
            'plain_id' => $plainId,
            'tenant_name' => $tenant->name
        ]);

        // Ejecutar la consulta para obtener las opciones de configuración desde vnt_options_plains
        $companyOptions = DB::connection('mysql')
            ->table('vnt_options_plains as vop')
            ->join('vnt_plains as vp', 'vp.id', '=', 'vop.plain_id')
            ->join('vnt_merchant_types as vmt', 'vmt.id', '=', 'vp.merchantTypeId')
            ->join('vnt_options_params as op', 'op.id', '=', 'vop.option_id')
            ->where('vop.plain_id', $plainId)
            ->select(
                'vop.option_id',
                'vop.value',
                'op.name as option_name',
                'vp.name as plain_name',
                'vmt.name as merchant_type_name'
            )
            ->get();

        if ($companyOptions->isEmpty()) {
            Log::info('ℹ️ No se encontraron opciones de configuración para este plain_id', [
                'plain_id' => $plainId,
                'company_id' => $companyId
            ]);
            return;
        }

        Log::info('📋 Opciones de configuración encontradas', [
            'count' => $companyOptions->count(),
            'plain_id' => $plainId,
            'company_id' => $companyId,
            'options' => $companyOptions->pluck('option_name')->toArray()
        ]);

        // Insertar las opciones en la BD del tenant
        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($companyOptions as $option) {
            // Verificar si ya existe
            $exists = DB::connection('tenant')
                ->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->where('option_id', $option->option_id)
                ->exists();

            if ($exists) {
                // Actualizar
                DB::connection('tenant')
                    ->table('cnf_company_options')
                    ->where('company_id', $companyId)
                    ->where('option_id', $option->option_id)
                    ->update([
                        'value' => $option->value,
                        'updated_at' => now()
                    ]);
                $updatedCount++;
            } else {
                // Insertar
                DB::connection('tenant')
                    ->table('cnf_company_options')
                    ->insert([
                        'company_id' => $companyId,
                        'option_id' => $option->option_id,
                        'value' => $option->value,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                $insertedCount++;
            }
        }

        Log::info('✅ Opciones de configuración procesadas exitosamente', [
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'total' => $companyOptions->count(),
            'plain_id' => $plainId,
            'company_id' => $companyId
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

