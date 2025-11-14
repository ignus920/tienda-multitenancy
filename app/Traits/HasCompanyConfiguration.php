<?php

namespace App\Traits;

use App\Services\Configuration\CompanyConfigurationService;
use App\Services\Company\CompanyDataValidator;
use Illuminate\Support\Facades\Auth;

trait HasCompanyConfiguration
{
    /**
     * Instancia del servicio de configuración
     */
    protected ?CompanyConfigurationService $configService = null;

    /**
     * Datos de configuración cacheados en la instancia
     */
    protected array $cachedConfig = [];

    /**
     * ID de la empresa actual
     */
    protected ?int $currentCompanyId = null;

    /**
     * ID del plan actual
     */
    protected ?int $currentPlainId = null;

    /**
     * Inicializa la configuración de la empresa
     */
    protected function initializeCompanyConfiguration(): void
    {
        $this->configService = app(CompanyConfigurationService::class);

        // Obtener datos de la empresa actual usando el mismo validador que UpdateCompany
        $user = Auth::user();
        if ($user) {
            $validator = app(CompanyDataValidator::class);
            $company = $validator->getUserCompany($user);

            if ($company) {
                $this->currentCompanyId = $company->id;
                $this->currentPlainId = $this->getUserPlainId($user); // Por defecto plan 2 (Avanzado)
            }

            // DEBUG: Log información de la empresa encontrada
            \Log::info("🔍 DEBUG initializeCompanyConfiguration", [
                'user_id' => $user->id,
                'company_found' => $company ? $company->id : 'NO',
                'company_name' => $company->businessName ?? 'N/A',
                'currentCompanyId' => $this->currentCompanyId,
                'currentPlainId' => $this->currentPlainId
            ]);
        }

        // Precargar configuraciones comunes
        if ($this->currentCompanyId && $this->currentPlainId) {
            $this->configService->preloadCommonConfigurations(
                $this->currentCompanyId,
                $this->currentPlainId
            );
        }
    }

    /**
     * Verifica si un campo debe mostrarse según la configuración
     */
    protected function shouldShowField(string $modulName, string $optionName): bool
    {
        if (!$this->configService || !$this->currentCompanyId || !$this->currentPlainId) {
            return false;
        }

        return $this->configService->shouldShowField(
            $this->currentCompanyId,
            $this->currentPlainId,
            $modulName,
            $optionName
        );
    }

    /**
     * Obtiene valor de configuración
     */
    protected function getConfigValue(string $modulName, string $optionName, $default = null)
    {
        if (!$this->configService || !$this->currentCompanyId || !$this->currentPlainId) {
            return $default;
        }

        return $this->configService->getConfigValue(
            $this->currentCompanyId,
            $this->currentPlainId,
            $modulName,
            $optionName,
            $default
        );
    }

    /**
     * Obtiene la configuración completa del módulo
     */
    protected function getModuleConfig(string $modulName): array
    {
        if (!$this->configService || !$this->currentCompanyId || !$this->currentPlainId) {
            return [];
        }

        $cacheKey = "module_config_{$modulName}";

        if (!isset($this->cachedConfig[$cacheKey])) {
            $this->cachedConfig[$cacheKey] = $this->configService->getModuleConfiguration(
                $this->currentCompanyId,
                $this->currentPlainId,
                $modulName
            );

            // DEBUG: Log de la configuración obtenida
            \Log::info("🔍 DEBUG getModuleConfig para {$modulName}", [
                'companyId' => $this->currentCompanyId,
                'plainId' => $this->currentPlainId,
                'config' => $this->cachedConfig[$cacheKey]
            ]);
        }

        return $this->cachedConfig[$cacheKey];
    }

    /**
     * Valida campos según configuración antes de procesar formulario
     */
    protected function validateFormFields(string $modulName, array $rules): array
    {
        $validatedRules = [];
        $moduleConfig = $this->getModuleConfig($modulName);

        foreach ($rules as $field => $rule) {
            // Verificar si el campo debe mostrarse según configuración
            if ($this->shouldShowField($modulName, $field)) {
                $validatedRules[$field] = $rule;
            }
        }

        return $validatedRules;
    }

    /**
     * Filtra datos de un modelo según la configuración
     */
    protected function filterDataByConfiguration(string $modulName, array $data): array
    {
        $filteredData = [];

        foreach ($data as $field => $value) {
            if ($this->shouldShowField($modulName, $field)) {
                $filteredData[$field] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Obtiene el ID del plan del usuario
     * IMPLEMENTAR según tu lógica de negocio
     */
    protected function getUserPlainId($user): int
    {
        // Lógica para determinar el plan:
        // 1 = post
        // 2 = institucional
        // Implementar según como determines el plan del usuario

        return 2; // Por defecto institucional - CAMBIAR según tu lógica
    }

    /**
     * Limpia el caché de configuración
     */
    protected function clearConfigurationCache(): void
    {
        if ($this->configService && $this->currentCompanyId && $this->currentPlainId) {
            $this->configService->clearCache($this->currentCompanyId, $this->currentPlainId);
        }

        $this->cachedConfig = [];
    }
}