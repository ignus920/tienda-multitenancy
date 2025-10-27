<?php

namespace App\Services\Tenant;

use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TenantManager
{
    /**
     * Crea un nuevo tenant con su base de datos.
     */
    public function create(array $data, ?User $owner = null): Tenant
    {
        try {
            // Generar ID único si no se proporciona
            $tenantId = $data['id'] ?? Str::uuid()->toString();

            // Generar nombre de base de datos basado en el ID
            $dbName = $data['db_name'] ?? 'tenant_' . str_replace('-', '_', $tenantId);

            // Crear el tenant en la base central
            $tenant = Tenant::create([
                'id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'db_name' => $dbName,
                'db_user' => $data['db_user'] ?? config('database.connections.mysql.username'),
                'db_password' => $data['db_password'] ?? config('database.connections.mysql.password'),
                'db_host' => $data['db_host'] ?? config('database.connections.mysql.host'),
                'db_port' => $data['db_port'] ?? config('database.connections.mysql.port'),
                'is_active' => $data['is_active'] ?? true,
                'settings' => $data['settings'] ?? [],
            ]);

            // Crear la base de datos física
            $this->createDatabase($tenant);

            // Ejecutar migraciones del tenant
            $this->runMigrations($tenant);

            // Instalar Spatie Permission en el tenant
            $this->installSpatiePermission($tenant);

            // Si hay un propietario, asociarlo al tenant
            if ($owner) {
                $this->assignUser($tenant, $owner, 'admin');
            }

            return $tenant;
        } catch (\Exception $e) {
            // En caso de error, eliminar la base de datos si fue creada
            if (isset($tenant) && isset($dbName)) {
                try {
                    DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
                } catch (\Exception $dropException) {
                    // Silenciar error de DROP
                }
            }
            throw $e;
        }
    }

    /**
     * Crea la base de datos física del tenant.
     */
    protected function createDatabase(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;

        // Conexión a MySQL para crear la base de datos
        $connection = config('database.default');
        $charset = config("database.connections.{$connection}.charset", 'utf8mb4');
        $collation = config("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci');

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    /**
     * Ejecuta las migraciones del tenant.
     */
    protected function runMigrations(Tenant $tenant): void
    {
        // Guardar conexión actual
        $originalConnection = config('database.default');

        // Configurar conexión del tenant
        config([
            'database.connections.tenant_migrations' => [
                'driver' => 'mysql',
                'host' => $tenant->db_host,
                'port' => $tenant->db_port,
                'database' => $tenant->db_name,
                'username' => $tenant->db_user,
                'password' => $tenant->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ],
        ]);

        // Cambiar a conexión del tenant
        config(['database.default' => 'tenant_migrations']);
        DB::purge('tenant_migrations');
        DB::reconnect('tenant_migrations');

        try {
            // Ejecutar migraciones
            Artisan::call('migrate', [
                '--database' => 'tenant_migrations',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } finally {
            // Restaurar conexión original
            config(['database.default' => $originalConnection]);
            DB::purge('tenant_migrations');
            DB::reconnect($originalConnection);
        }
    }

    /**
     * Instala Spatie Permission en la base de datos del tenant.
     */
    protected function installSpatiePermission(Tenant $tenant): void
    {
        // Guardar conexión actual
        $originalConnection = config('database.default');

        // Configurar conexión del tenant
        config([
            'database.connections.tenant_migrations' => [
                'driver' => 'mysql',
                'host' => $tenant->db_host,
                'port' => $tenant->db_port,
                'database' => $tenant->db_name,
                'username' => $tenant->db_user,
                'password' => $tenant->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ],
        ]);

        // Cambiar a conexión del tenant
        config(['database.default' => 'tenant_migrations']);
        DB::purge('tenant_migrations');
        DB::reconnect('tenant_migrations');

        try {
            // Crear roles básicos
            $this->createDefaultRoles();
        } finally {
            // Restaurar conexión original
            config(['database.default' => $originalConnection]);
            DB::purge('tenant_migrations');
            DB::reconnect($originalConnection);
        }
    }

    /**
     * Crea los roles por defecto en el tenant.
     */
    protected function createDefaultRoles(): void
    {
        $roleClass = config('permission.models.role');

        $roleClass::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleClass::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $roleClass::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
    }

    /**
     * Asigna un usuario a un tenant.
     */
    public function assignUser(Tenant $tenant, User $user, string $role = 'user'): void
    {
        $tenant->users()->attach($user->id, [
            'role' => $role,
            'is_active' => true,
            'last_accessed_at' => now(),
        ]);
    }

    /**
     * Remueve un usuario de un tenant.
     */
    public function removeUser(Tenant $tenant, User $user): void
    {
        $tenant->users()->detach($user->id);
    }

    /**
     * Establece la conexión al tenant activo.
     */
    public function setConnection(Tenant $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_user,
            'password' => $tenant->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Elimina un tenant y su base de datos.
     */
    public function delete(Tenant $tenant, bool $deleteDatabase = false): void
    {
        DB::beginTransaction();

        try {
            if ($deleteDatabase) {
                $this->dropDatabase($tenant);
            }

            $tenant->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina la base de datos física del tenant.
     */
    protected function dropDatabase(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;
        DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
    }

    /**
     * Desactiva un tenant.
     */
    public function deactivate(Tenant $tenant): void
    {
        $tenant->update(['is_active' => false]);
    }

    /**
     * Activa un tenant.
     */
    public function activate(Tenant $tenant): void
    {
        $tenant->update(['is_active' => true]);
    }
}
