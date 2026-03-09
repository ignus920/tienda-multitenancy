<?php

namespace App\Traits;

use App\Services\Configuration\CompanyConfigurationService;
use App\Services\Company\CompanyDataValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasCompanyConfiguration
{
    /**
     * Instancia del servicio de configuración (Compartido)
     */
    protected static ?CompanyConfigurationService $staticConfigService = null;

    /**
     * Datos de configuración cacheados (Compartido)
     */
    protected static array $staticCachedConfig = [];

    /**
     * ID de la empresa actual (Compartido)
     */
    protected static ?int $sharedCompanyId = null;

    /**
     * ID del plan actual (Compartido)
     */
    protected static ?int $sharedPlainId = null;

    /**
     * Flag para controlar si ya fue inicializado (Compartido)
     */
    private static bool $isStaticInitialized = false;

    /**
     * Propiedades de instancia (Compatibilidad legacy)
     */
    protected ?CompanyConfigurationService $configService = null;
    protected ?int $currentCompanyId = null;
    protected ?int $currentPlainId = null;

    /**
     * Inicializa la configuración de la empresa
     */
    protected function initializeCompanyConfiguration(): void
    {
        if (self::$isStaticInitialized && self::$staticConfigService && self::$sharedCompanyId) {
            $this->syncInstanceProperties();
            return;
        }

        self::$staticConfigService = app(CompanyConfigurationService::class);

        $user = Auth::user();

        if ($user) {
            $validator = app(CompanyDataValidator::class);
            $company = $validator->getUserCompany($user);

            if ($company) {
                self::$sharedCompanyId = $company->id;
                self::$sharedPlainId = $this->getUserPlainId($user);

                // Precargar configuraciones comunes
                self::$staticConfigService->preloadCommonConfigurations(
                    self::$sharedCompanyId,
                    self::$sharedPlainId
                );
            }
        }

        self::$isStaticInitialized = true;
        $this->syncInstanceProperties();
    }

    /**
     * Sincroniza las propiedades de la instancia con el estado estático compartido
     */
    private function syncInstanceProperties(): void
    {
        $this->configService = self::$staticConfigService;
        $this->currentCompanyId = self::$sharedCompanyId;
        $this->currentPlainId = self::$sharedPlainId;
    }

    /**
     * Verifica si un campo debe mostrarse según la configuración
     */
    protected function shouldShowField(string $modulName, string $optionName): bool
    {
        $this->ensureConfigurationInitialized();

        if (!self::$staticConfigService || !self::$sharedCompanyId || !self::$sharedPlainId) {
            return false;
        }

        return self::$staticConfigService->shouldShowField(
            self::$sharedCompanyId,
            self::$sharedPlainId,
            $modulName,
            $optionName
        );
    }

    /**
     * Verifica y asegura que la configuración esté inicializada
     */
    private function ensureConfigurationInitialized(): void
    {
        if (!self::$isStaticInitialized || !self::$staticConfigService || !self::$sharedCompanyId) {
            $this->initializeCompanyConfiguration();
        } else {
            $this->syncInstanceProperties();
        }
    }

    /**
     * Verifica si una opción específica está habilitada (método principal)
     */
    protected function isOptionEnabled(int $optionId): bool
    {
        $this->ensureConfigurationInitialized();

        if (!self::$staticConfigService || !self::$sharedCompanyId) {
            return false;
        }

        return self::$staticConfigService->isOptionEnabled(self::$sharedCompanyId, $optionId);
    }

    /**
     * Verifica múltiples opciones de una vez
     */
    protected function areOptionsEnabled(array $optionIds): array
    {
        if (!self::$staticConfigService || !self::$sharedCompanyId) {
            return array_fill_keys($optionIds, false);
        }

        return self::$staticConfigService->areOptionsEnabled(self::$sharedCompanyId, $optionIds);
    }

    /**
     * Obtiene todas las opciones habilitadas para la empresa actual
     */
    protected function getEnabledOptions(): array
    {
        if (!self::$staticConfigService || !self::$sharedCompanyId) {
            return [];
        }

        return self::$staticConfigService->getEnabledOptions(self::$sharedCompanyId);
    }

    /**
     * Obtiene el valor específico de una opción
     */
    protected function getOptionValue(int $optionId): ?int
    {
        $this->ensureConfigurationInitialized();

        if (!self::$staticConfigService || !self::$sharedCompanyId) {
            return null;
        }

        return self::$staticConfigService->getOptionValue(self::$sharedCompanyId, $optionId);
    }

    /**
     * Obtiene valor de configuración (método legacy mantenido por compatibilidad)
     */
    protected function getConfigValue(string $modulName, string $optionName, $default = null)
    {
        if (!self::$staticConfigService || !self::$sharedCompanyId || !self::$sharedPlainId) {
            return $default;
        }

        return self::$staticConfigService->getConfigValue(
            self::$sharedCompanyId,
            self::$sharedPlainId,
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
        if (!self::$staticConfigService || !self::$sharedCompanyId || !self::$sharedPlainId) {
            return [];
        }

        $cacheKey = "module_config_{$modulName}";

        if (!isset(self::$staticCachedConfig[$cacheKey])) {
            self::$staticCachedConfig[$cacheKey] = self::$staticConfigService->getModuleConfiguration(
                self::$sharedCompanyId,
                self::$sharedPlainId,
                $modulName
            );
        }

        return self::$staticCachedConfig[$cacheKey];
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
     * Genera array de configuración para frontend (JavaScript)
     * Método genérico que devuelve todas las opciones habilitadas
     */
    protected function getConfigForFrontend(): array
    {
        return [
            'enabledOptions' => $this->getEnabledOptions(),
        ];
    }

    /**
     * Limpia el caché de configuración
     */
    protected function clearConfigurationCache(): void
    {
        if (self::$staticConfigService && self::$sharedCompanyId && self::$sharedPlainId) {
            self::$staticConfigService->clearCache(self::$sharedCompanyId, self::$sharedPlainId);
        }

        self::$staticCachedConfig = [];
    }
}