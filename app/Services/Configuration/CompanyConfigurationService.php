<?php

namespace App\Services\Configuration;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompanyConfigurationService
{
    /**
     * Tiempo de caché en minutos (1 hora por defecto)
     */
    protected const CACHE_TTL = 60;

    /**
     * Prefijo para las llaves de caché
     */
    protected const CACHE_PREFIX = 'company_config';

    /**
     * Obtiene la configuración completa de la empresa actual
     *
     * @param int $companyId
     * @param int $plainId Plan de la empresa (1=post, 2=institucional)
     * @return array
     */
    public function getCompanyConfiguration(int $companyId, int $plainId): array
    {
        $cacheKey = $this->getCacheKey('full', $companyId, $plainId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($companyId, $plainId) {
            return $this->fetchConfigurationFromDatabase($companyId, $plainId);
        });
    }

    /**
     * Obtiene configuración específica de un módulo
     *
     * @param int $companyId
     * @param int $plainId
     * @param string $modulName
     * @return array
     */
    public function getModuleConfiguration(int $companyId, int $plainId, string $modulName): array
    {
        $cacheKey = $this->getCacheKey('module', $companyId, $plainId, $modulName);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($companyId, $plainId, $modulName) {
            return $this->fetchModuleConfigurationFromDatabase($companyId, $plainId, $modulName);
        });
    }

    /**
     * Verifica si un campo debe mostrarse en el formulario
     *
     * @param int $companyId
     * @param int $plainId
     * @param string $modulName
     * @param string $optionName
     * @return bool
     */
    public function shouldShowField(int $companyId, int $plainId, string $modulName, string $optionName): bool
    {
        $moduleConfig = $this->getModuleConfiguration($companyId, $plainId, $modulName);

        foreach ($moduleConfig as $option) {
            if ($option->opcion === $optionName) {
                return (bool) $option->value;
            }
        }

        return false; // Por defecto no mostrar si no está configurado
    }

    /**
     * Obtiene valor de una configuración específica
     *
     * @param int $companyId
     * @param int $plainId
     * @param string $modulName
     * @param string $optionName
     * @param mixed $default
     * @return mixed
     */
    public function getConfigValue(int $companyId, int $plainId, string $modulName, string $optionName, $default = null)
    {
        $moduleConfig = $this->getModuleConfiguration($companyId, $plainId, $modulName);

        foreach ($moduleConfig as $option) {
            if ($option->opcion === $optionName) {
                return $option->value;
            }
        }

        return $default;
    }

    /**
     * Invalida el caché de configuración
     *
     * @param int|null $companyId Si es null, limpia toda la configuración
     * @param int|null $plainId
     * @return void
     */
    public function clearCache(int $companyId = null, int $plainId = null): void
    {
        if ($companyId && $plainId) {
            // Limpiar caché específico de una empresa
            $pattern = self::CACHE_PREFIX . ".company.{$companyId}.plain.{$plainId}.*";
            Cache::flush(); // En producción, usar tags para borrado selectivo
        } else {
            // Limpiar todo el caché de configuración
            Cache::flush();
        }
    }

    /**
     * Precarga configuraciones comunes para mejorar rendimiento
     *
     * @param int $companyId
     * @param int $plainId
     * @return void
     */
    public function preloadCommonConfigurations(int $companyId, int $plainId): void
    {
        // Módulos más utilizados
        $commonModules = ['usuarios', 'formularios', 'reportes', 'facturacion'];

        foreach ($commonModules as $module) {
            $this->getModuleConfiguration($companyId, $plainId, $module);
        }
    }

    /**
     * Genera la llave de caché
     */
    protected function getCacheKey(string $type, int $companyId, int $plainId, string $additional = ''): string
    {
        $key = self::CACHE_PREFIX . ".{$type}.company.{$companyId}.plain.{$plainId}";

        if ($additional) {
            $key .= ".{$additional}";
        }

        return $key;
    }

    /**
     * Obtiene configuración completa desde la base de datos
     */
    protected function fetchConfigurationFromDatabase(int $companyId, int $plainId): array
    {
        return DB::connection('central')->select("
            SELECT
                c.businessName,
                c.firstName,
                c.secondName,
                c.lastName,
                c.secondLastName,
                p.name as plain,
                m.name as modul,
                opp.name as opcion,
                co.value,
                co.company_id,
                co.option_id,
                pl.plain_id,
                opp.modul_id
            FROM rap.vnt_company_options co
            INNER JOIN rap.vnt_options_params opp ON opp.id = co.option_id
            INNER JOIN rap.vnt_options_plains pl ON pl.option_id = co.option_id
            INNER JOIN rap.vnt_moduls m ON m.id = opp.modul_id
            INNER JOIN rap.vnt_plains p ON p.id = pl.plain_id
            INNER JOIN rap.vnt_companies c ON c.id = co.company_id
            WHERE pl.plain_id = ? AND co.company_id = ? and value=1
        ", [$plainId, $companyId]);
    }

    /**
     * Obtiene configuración de un módulo específico desde la base de datos
     */
    protected function fetchModuleConfigurationFromDatabase(int $companyId, int $plainId, string $modulName): array
    {
        return DB::connection('central')->select("
            SELECT
                m.name as modul,
                opp.name as opcion,
                co.value
            FROM rap.vnt_company_options co
            INNER JOIN rap.vnt_options_params opp ON opp.id = co.option_id
            INNER JOIN rap.vnt_options_plains pl ON pl.option_id = co.option_id
            INNER JOIN rap.vnt_moduls m ON m.id = opp.modul_id
            WHERE pl.plain_id = ?
            AND co.company_id = ?
            AND m.name = ?
        ", [$plainId, $companyId, $modulName]);
    }
}