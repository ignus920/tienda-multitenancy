<?php

namespace App\Helpers;

use App\Services\PermissionCatalogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use App\Models\Auth\Tenant;

class PermissionHelper
{
    private static ?PermissionCatalogService $permissionService = null;

    /** Caché de la configuración efectiva de permisos por id de usuario (dura lo que el request). */
    private static array $configCache = [];

    private static function getPermissionService(): PermissionCatalogService
    {
        if (self::$permissionService === null) {
            self::$permissionService = app(PermissionCatalogService::class);
        }

        return self::$permissionService;
    }

    /**
     * Configuración de permisos EFECTIVOS del usuario actual (perfil + excepciones),
     * cacheada durante el request para no consultar la BD central en cada userCan().
     */
    private static function currentUserConfig(): array
    {
        $user = Auth::user();

        if (!$user || !$user->profile_id) {
            return ['profile' => null, 'permissions' => []];
        }

        if (!array_key_exists($user->id, self::$configCache)) {
            $service = self::getPermissionService();
            try {
                self::$configCache[$user->id] = $service
                    ->getEffectiveUserPermissions((int) $user->id, (int) $user->profile_id);
            } catch (\Throwable $e) {
                // Fallback: si la resolución efectiva (perfil + excepciones) falla,
                // usar SOLO los permisos del perfil para no ocultar todo el menú.
                try {
                    self::$configCache[$user->id] = $service
                        ->getUserPermissionConfiguration((int) $user->profile_id);
                } catch (\Throwable $e2) {
                    self::$configCache[$user->id] = ['profile' => null, 'permissions' => []];
                }
            }
        }

        return self::$configCache[$user->id];
    }

    /** Limpia la caché de permisos (llamar tras guardar cambios en la pantalla de gestión). */
    public static function clearCache(): void
    {
        self::$configCache = [];
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
            $config = self::currentUserConfig();

            if (!isset($config['permissions'][$permission])) {
                return false;
            }

            $accessLevels = $config['permissions'][$permission]['access_levels'];

            return match ($action) {
                'create' => (bool) $accessLevels['create'],
                'edit' => (bool) $accessLevels['edit'],
                'delete', 'deactivate' => (bool) $accessLevels['delete'],
                'show' => (bool) $accessLevels['show'],
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
        return self::currentUserConfig()['permissions'] ?? [];
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
            $profile = self::currentUserConfig()['profile'] ?? null;

            return $profile
                && (($profile['alias'] ?? null) === 'Super Administrador'
                    || (int) ($profile['id'] ?? 0) === 1);
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
                'canDeactivate' => $permission['access_levels']['delete'], // alias de canDelete
                'canShow' => $permission['access_levels']['show']
            ];
        }

        return $viewPermissions;
    }

    public static function getMerchantType(): string
    {
        $tenantId = session('tenant_id');
        $tenant = Tenant::find($tenantId);
        return $tenant->merchant_type_id ?? 0;
        \Illuminate\Support\Facades\Log::info('🔍 getMerchantTyp', [
            'tenant_id' => $tenantId
        ]);
    }

    /**
     * Genera directivas de Blade para verificar permisos
     *
     * IMPORTANTE: Estas directivas deben ser registradas en un ServiceProvider
     * (generalmente AppServiceProvider) llamando a:
     * PermissionHelper::registerBladeDirectives();
     *
     * Una vez registradas, se pueden usar en las vistas Blade:
     *
     * @userCan('Usuarios', 'show')
     *     <!-- Contenido visible solo si puede VER usuarios -->
     * @enduserCan
     *
     * @userCan('Ventas', 'create')
     *     <!-- Contenido visible solo si puede CREAR ventas -->
     * @enduserCan
     *
     * @userCanAny(['Usuarios', 'Parametros'], 'show')
     *     <!-- Contenido visible si puede ver CUALQUIERA de estos módulos -->
     * @enduserCanAny
     *
     * @userCanAll(['Usuarios', 'Parametros'], 'edit')
     *     <!-- Contenido visible solo si puede editar TODOS estos módulos -->
     * @enduserCanAll
     *
     * @isSuperAdmin
     *     <!-- Contenido visible solo para super administradores -->
     * @endisSuperAdmin
     *
     * Acciones disponibles: 'show', 'create', 'edit', 'delete'
     * Nombres de permisos: según tabla usr_permissions (ej: 'Usuarios', 'Ventas', 'Inventario', etc.)
     */
    public static function registerBladeDirectives(): void
    {
        Blade::if('userCan', function ($permission, $action = 'show') {
            return self::userCan($permission, $action);
        });

        Blade::if('isSuperAdmin', function () {
            return self::isSuperAdmin();
        });

        Blade::if('userCanAny', function ($permissions, $action = 'show') {
            return self::userCanAny($permissions, $action);
        });

        Blade::if('userCanAll', function ($permissions, $action = 'show') {
            return self::userCanAll($permissions, $action);
        });
    }
}

/*
 * EJEMPLOS DE USO DEL PERMISSIONHELPER:
 *
 * 1. En controladores/componentes Livewire:
 *    if (PermissionHelper::userCan('Usuarios', 'create')) {
 *        // Lógica para crear usuarios
 *    }
 *
 * 2. En vistas Blade (método directo):
 *    @if(PermissionHelper::userCan('Ventas', 'show'))
 *        <!-- Mostrar módulo de ventas -->
 *    @endif
 *
 * 3. En vistas Blade (con directivas registradas):
 *    @userCan('Inventario', 'edit')
 *        <!-- Mostrar botón editar inventario -->
 *    @enduserCan
 *
 * 4. Para verificar múltiples permisos:
 *    @userCanAny(['Usuarios', 'Parametros'])
 *        <!-- Mostrar si tiene acceso a cualquiera -->
 *    @enduserCanAny
 *
 * PERMISOS DISPONIBLES (según base de datos):
 * - Parametros
 * - Usuarios
 * - Ventas
 * - Inventario
 * - Facturacion
 * - Administracion de Items
 * - Caja
 * - Compras
 * - Mercadeo
 * - Cartera
 * - Informes de ventas
 * - Informes de inventario
 * - Informes de Caja
 * - Informes de Cartera
 *
 * ACCIONES DISPONIBLES:
 * - 'show' (ver/consultar)
 * - 'create' (crear)
 * - 'edit' (editar)
 * - 'delete' (eliminar)
 */