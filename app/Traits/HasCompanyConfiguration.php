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
     * Verifica si una opción específica está habilitada (método principal)
     */
    protected function isOptionEnabled(int $optionId): bool
    {
        if (!$this->configService || !$this->currentCompanyId) {
            return false;
        }

        return $this->configService->isOptionEnabled($this->currentCompanyId, $optionId);
    }

    /**
     * Verifica múltiples opciones de una vez
     */
    protected function areOptionsEnabled(array $optionIds): array
    {
        if (!$this->configService || !$this->currentCompanyId) {
            return array_fill_keys($optionIds, false);
        }

        return $this->configService->areOptionsEnabled($this->currentCompanyId, $optionIds);
    }

    /**
     * Obtiene todas las opciones habilitadas para la empresa actual
     */
    protected function getEnabledOptions(): array
    {
        if (!$this->configService || !$this->currentCompanyId) {
            return [];
        }

        return $this->configService->getEnabledOptions($this->currentCompanyId);
    }

    /**
     * Obtiene valor de configuración (método legacy mantenido por compatibilidad)
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
     * Métodos de conveniencia para opciones específicas comunes
     */

    /**
     * Verifica si se permiten múltiples usuarios
     */
    protected function allowsMultipleUsers(): bool
    {
        return $this->isOptionEnabled(1); // Asumiendo que option_id 1 = múltiples usuarios
    }

    /**
     * Verifica si está habilitada la funcionalidad de reportes avanzados
     */
    protected function hasAdvancedReports(): bool
    {
        return $this->isOptionEnabled(2); // Asumiendo que option_id 2 = reportes avanzados
    }

    /**
     * Verifica si está habilitada la funcionalidad de inventario
     */
    protected function hasInventoryFeature(): bool
    {
        return $this->isOptionEnabled(3); // Asumiendo que option_id 3 = inventario
    }

    /**
     * Obtiene el límite de usuarios permitidos
     */
    protected function getUserLimit(): int
    {
        if ($this->isOptionEnabled(1)) { // Si permite múltiples usuarios
            return $this->isOptionEnabled(5) ? 10 : 5; // Ejemplo: option_id 5 = plan premium
        }

        return 1; // Solo un usuario por defecto
    }

    /**
     * Verifica si tiene acceso a funcionalidades premium
     */
    protected function hasPremiumFeatures(): bool
    {
        $premiumOptions = [5, 6, 7, 8]; // IDs de opciones premium
        $enabledOptions = $this->areOptionsEnabled($premiumOptions);

        return in_array(true, $enabledOptions); // Al menos una opción premium habilitada
    }

    /**
     * Genera array de configuración para frontend (JavaScript)
     */
    protected function getConfigForFrontend(): array
    {
        return [
            'allowsMultipleUsers' => $this->allowsMultipleUsers(),
            'hasAdvancedReports' => $this->hasAdvancedReports(),
            'hasInventoryFeature' => $this->hasInventoryFeature(),
            'hasPremiumFeatures' => $this->hasPremiumFeatures(),
            'userLimit' => $this->getUserLimit(),
            'enabledOptions' => $this->getEnabledOptions(),
        ];
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