<?php

namespace App\Helpers;

use App\Services\PermissionCatalogService;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    private static ?PermissionCatalogService $permissionService = null;

    private static function getPermissionService(): PermissionCatalogService
    {
        if (self::$permissionService === null) {
            self::$permissionService = app(PermissionCatalogService::class);
        }

        return self::$permissionService;
    }

    /**
     * Verifica si el usuario actual tiene un permiso específico
     */
    public static function userCan(string $permission, string $action = 'show'): bool
    {
        $user = Auth::user();

        if (!$user || !$user->profile_id) {
            return false;
        }

        try {
            $service = self::getPermissionService();
            $config = $service->getUserPermissionConfiguration($user->profile_id);

            if (!isset($config['permissions'][$permission])) {
                return false;
            }

            $accessLevels = $config['permissions'][$permission]['access_levels'];

            return match ($action) {
                'create' => $accessLevels['create'],
                'edit' => $accessLevels['edit'],
                'delete' => $accessLevels['delete'],
                'show' => $accessLevels['show'],
                default => false
            };

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifica múltiples permisos de una vez
     */
    public static function userCanAny(array $permissions, string $action = 'show'): bool
    {
        foreach ($permissions as $permission) {
            if (self::userCan($permission, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica que el usuario tenga todos los permisos especificados
     */
    public static function userCanAll(array $permissions, string $action = 'show'): bool
    {
        foreach ($permissions as $permission) {
            if (!self::userCan($permission, $action)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene todos los permisos del usuario actual
     */
    public static function getUserPermissions(): array
    {
        $user = Auth::user();

        if (!$user || !$user->profile_id) {
            return [];
        }

        try {
            $service = self::getPermissionService();
            $config = $service->getUserPermissionConfiguration($user->profile_id);

            return $config['permissions'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Verifica si el usuario es super administrador
     */
    public static function isSuperAdmin(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->profile_id) {
            return false;
        }

        // Asumiendo que el perfil de super admin tiene alias 'super_admin' o id 1
        try {
            $service = self::getPermissionService();
            $config = $service->getUserPermissionConfiguration($user->profile_id);

            return $config['profile']['alias'] === 'Super Administrador'
                || $config['profile']['id'] === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Genera un array de permisos para usar en vistas
     */
    public static function getPermissionsForView(): array
    {
        $permissions = self::getUserPermissions();
        $viewPermissions = [];

        foreach ($permissions as $permissionName => $permission) {
            $viewPermissions[$permissionName] = [
                'canCreate' => $permission['access_levels']['create'],
                'canEdit' => $permission['access_levels']['edit'],
                'canDelete' => $permission['access_levels']['delete'],
                'canShow' => $permission['access_levels']['show']
            ];
        }

        return $viewPermissions;
    }

    /**
     * Genera directivas de Blade para verificar permisos
     */
    public static function registerBladeDirectives(): void
    {
        \Blade::if('userCan', function ($permission, $action = 'show') {
            return self::userCan($permission, $action);
        });

        \Blade::if('isSuperAdmin', function () {
            return self::isSuperAdmin();
        });

        \Blade::if('userCanAny', function ($permissions, $action = 'show') {
            return self::userCanAny($permissions, $action);
        });

        \Blade::if('userCanAll', function ($permissions, $action = 'show') {
            return self::userCanAll($permissions, $action);
        });
    }
}