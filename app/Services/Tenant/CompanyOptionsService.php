<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

/**
 * Servicio para gestionar opciones de empresa desde cnf_company_options
 * 
 * Este servicio consulta directamente la base de datos tenant sin caché
 * para garantizar datos actualizados en tiempo real.
 */
class CompanyOptionsService
{
    /**
     * Asegurar que estamos conectados a la BD tenant correcta
     */
    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            Log::warning('⚠️ No hay tenant_id en sesión');
            return;
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            Log::warning('⚠️ Tenant no encontrado', ['tenant_id' => $tenantId]);
            return;
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
        
        Log::debug('✅ Conexión tenant establecida', [
            'tenant_id' => $tenantId,
            'database' => DB::connection()->getDatabaseName()
        ]);
    }
    /**
     * Verifica si una opción específica está habilitada para una empresa
     * 
     * @param int $companyId ID de la empresa
     * @param int $optionId ID de la opción a verificar
     * @return bool true si la opción está habilitada (value=1), false en caso contrario
     */
    public function isOptionEnabled(int $companyId, int $optionId): bool
    {
        try {
            Log::debug('🔍 CompanyOptionsService::isOptionEnabled', [
                'company_id' => $companyId,
                'option_id' => $optionId
            ]);

            $optionValue = $this->getOptionValue($companyId, $optionId);
            $enabled = $optionValue === 1;

            Log::debug('✅ Resultado de validación de opción', [
                'company_id' => $companyId,
                'option_id' => $optionId,
                'option_value' => $optionValue,
                'enabled' => $enabled
            ]);

            return $enabled;

        } catch (\Exception $e) {
            Log::error('❌ Error verificando opción de empresa', [
                'company_id' => $companyId,
                'option_id' => $optionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtiene el valor de una opción específica para una empresa
     * 
     * @param int $companyId ID de la empresa
     * @param int $optionId ID de la opción
     * @return int|null Valor de la opción o null si no existe
     */
    public function getOptionValue(int $companyId, int $optionId): ?int
    {
        try {
            // Asegurar conexión a BD tenant correcta
            $this->ensureTenantConnection();
            
           // Consulta DIRECTA usando la conexión 'tenant' EXPLÍCITAMENTE
            $option = DB::connection('tenant')
                ->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->where('option_id', $optionId)
                ->whereNull('deleted_at')
                ->first(['value', 'id', 'company_id', 'option_id']);

            if (!$option) {
                Log::warning('⚠️ Opción no encontrada en cnf_company_options', [
                    'company_id' => $companyId,
                    'option_id' => $optionId,
                    'connection' => 'tenant',
                    'database' => DB::connection('tenant')->getDatabaseName()
                ]);
                return null;
            }

            $valueInt = (int) $option->value;
            


            return $valueInt;

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo valor de opción', [
                'company_id' => $companyId,
                'option_id' => $optionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Verifica si la facturación electrónica está habilitada para una empresa
     * 
     * Este es un método helper específico para la opción de facturación electrónica (option_id=8)
     * 
     * @param int $companyId ID de la empresa
     * @return bool true si la facturación electrónica está habilitada, false en caso contrario
     */
    public function isElectronicInvoicingEnabled(int $companyId): bool
    {
        Log::info('🔍 Validando facturación electrónica', [
            'company_id' => $companyId,
            'option_id' => 8,
            'service' => 'CompanyOptionsService'
        ]);

        $enabled = $this->isOptionEnabled($companyId, 8);

        Log::info('✅ Resultado validación facturación electrónica', [
            'company_id' => $companyId,
            'enabled' => $enabled
        ]);

        return $enabled;
    }

    /**
     * Obtiene todas las opciones habilitadas para una empresa
     * 
     * @param int $companyId ID de la empresa
     * @return array Array de option_ids habilitados
     */
    public function getEnabledOptions(int $companyId): array
    {
        try {
            $this->ensureTenantConnection();
            
            $options = DB::connection('tenant')
                ->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->where('value', 1)
                ->whereNull('deleted_at')
                ->pluck('option_id')
                ->toArray();

            Log::debug('📋 Opciones habilitadas obtenidas', [
                'company_id' => $companyId,
                'connection' => 'tenant',
                'database' => DB::connection('tenant')->getDatabaseName(),
                'enabled_options' => $options
            ]);

            return $options;

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo opciones habilitadas', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Verifica múltiples opciones de una vez
     * 
     * @param int $companyId ID de la empresa
     * @param array $optionIds Array de IDs de opciones a verificar
     * @return array Array asociativo [option_id => bool]
     */
    public function areOptionsEnabled(int $companyId, array $optionIds): array
    {
        $results = [];

        foreach ($optionIds as $optionId) {
            $results[$optionId] = $this->isOptionEnabled($companyId, $optionId);
        }

        return $results;
    }
}
