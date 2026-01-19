<?php

namespace App\Services\Facturacion;

use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

class TenantConfigManager
{
    /**
     * Configurar facturación para un tenant específico
     */
    public static function setFacturacionConfig(Tenant $tenant, array $config): bool
    {
        try {
            $currentSettings = $tenant->settings ?? [];

            // Estructura de configuración de facturación
            $facturacionConfig = [
                'base_url' => $config['base_url'] ?? 'http://127.0.0.1:8000/api',
                'token' => $config['token'],
                'username' => $config['username'] ?? '',
                'timeout' => $config['timeout'] ?? 30,
                'enabled' => $config['enabled'] ?? true,
                'updated_at' => now()->toISOString()
            ];

            // Validar que el token esté presente
            if (empty($facturacionConfig['token'])) {
                Log::warning('⚠️ Intento de configurar facturación sin token', ['tenant_id' => $tenant->id]);
                return false;
            }

            $currentSettings['facturacion'] = $facturacionConfig;

            $tenant->update(['settings' => $currentSettings]);

            Log::info('✅ Configuración de facturación actualizada', [
                'tenant_id' => $tenant->id,
                'base_url' => $facturacionConfig['base_url'],
                'has_token' => !empty($facturacionConfig['token']),
                'enabled' => $facturacionConfig['enabled']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('❌ Error configurando facturación para tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener configuración de facturación de un tenant
     */
    public static function getFacturacionConfig(Tenant $tenant): ?array
    {
        return $tenant->settings['facturacion'] ?? null;
    }

    /**
     * Verificar si un tenant tiene facturación configurada
     */
    public static function hasFacturacionConfig(Tenant $tenant): bool
    {
        $config = self::getFacturacionConfig($tenant);
        return $config && !empty($config['token']) && ($config['enabled'] ?? false);
    }

    /**
     * Deshabilitar facturación para un tenant
     */
    public static function disableFacturacion(Tenant $tenant): bool
    {
        try {
            $currentSettings = $tenant->settings ?? [];

            if (isset($currentSettings['facturacion'])) {
                $currentSettings['facturacion']['enabled'] = false;
                $currentSettings['facturacion']['disabled_at'] = now()->toISOString();

                $tenant->update(['settings' => $currentSettings]);

                Log::info('🚫 Facturación deshabilitada para tenant', ['tenant_id' => $tenant->id]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error deshabilitando facturación', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Habilitar facturación para un tenant
     */
    public static function enableFacturacion(Tenant $tenant): bool
    {
        try {
            $currentSettings = $tenant->settings ?? [];

            if (isset($currentSettings['facturacion'])) {
                $currentSettings['facturacion']['enabled'] = true;
                $currentSettings['facturacion']['enabled_at'] = now()->toISOString();

                $tenant->update(['settings' => $currentSettings]);

                Log::info('✅ Facturación habilitada para tenant', ['tenant_id' => $tenant->id]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error habilitando facturación', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Actualizar token de facturación para un tenant
     */
    public static function updateFacturacionToken(Tenant $tenant, string $token): bool
    {
        try {
            $currentSettings = $tenant->settings ?? [];

            if (!isset($currentSettings['facturacion'])) {
                // Si no existe configuración previa, crear una básica
                $currentSettings['facturacion'] = [
                    'base_url' => 'http://127.0.0.1:8000/api',
                    'username' => '',
                    'timeout' => 30,
                    'enabled' => true
                ];
            }

            $currentSettings['facturacion']['token'] = $token;
            $currentSettings['facturacion']['token_updated_at'] = now()->toISOString();

            $tenant->update(['settings' => $currentSettings]);

            Log::info('🔑 Token de facturación actualizado', ['tenant_id' => $tenant->id]);
            return true;

        } catch (\Exception $e) {
            Log::error('❌ Error actualizando token de facturación', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener configuración para múltiples tenants
     */
    public static function getBulkFacturacionConfig(array $tenantIds): array
    {
        $configs = [];

        $tenants = Tenant::whereIn('id', $tenantIds)->get();

        foreach ($tenants as $tenant) {
            $configs[$tenant->id] = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'config' => self::getFacturacionConfig($tenant),
                'enabled' => self::hasFacturacionConfig($tenant)
            ];
        }

        return $configs;
    }

    /**
     * Validar configuración de facturación
     */
    public static function validateFacturacionConfig(array $config): array
    {
        $errors = [];

        if (empty($config['base_url'])) {
            $errors[] = 'La URL base es requerida';
        } elseif (!filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'La URL base no es válida';
        }

        if (empty($config['token'])) {
            $errors[] = 'El token de autenticación es requerido';
        }

        if (isset($config['timeout']) && (!is_numeric($config['timeout']) || $config['timeout'] < 1)) {
            $errors[] = 'El timeout debe ser un número mayor a 0';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Limpiar configuración de facturación de un tenant
     */
    public static function clearFacturacionConfig(Tenant $tenant): bool
    {
        try {
            $currentSettings = $tenant->settings ?? [];

            if (isset($currentSettings['facturacion'])) {
                unset($currentSettings['facturacion']);
                $tenant->update(['settings' => $currentSettings]);

                Log::info('🗑️ Configuración de facturación eliminada', ['tenant_id' => $tenant->id]);
                return true;
            }

            return true; // No había configuración que limpiar

        } catch (\Exception $e) {
            Log::error('❌ Error limpiando configuración de facturación', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}