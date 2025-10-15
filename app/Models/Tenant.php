<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;

class Tenant extends Model implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasUuids, InvalidatesResolverCache;

    protected $table = 'tenants';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'address',
        'db_name',
        'db_user',
        'db_password',
        'db_host',
        'db_port',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'db_port' => 'integer',
    ];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'db_name',
            'db_user',
            'db_password',
            'db_host',
            'db_port',
            'is_active',
            'settings',
        ];
    }

    public function run(callable $callback)
    {
        $originalConnection = app(\Illuminate\Database\DatabaseManager::class)->getDefaultConnection();

        tenancy()->initialize($this);

        $result = $callback($this);

        tenancy()->end();
        app(\Illuminate\Database\DatabaseManager::class)->setDefaultConnection($originalConnection);

        return $result;
    }

    public function getInternal(string $key)
    {
        return $this->getAttribute($key);
    }

    public function setInternal(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Relación con usuarios que tienen acceso a este tenant.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tenants')
            ->withPivot('role', 'is_active', 'last_accessed_at')
            ->withTimestamps();
    }

    /**
     * Configuración de la base de datos del tenant.
     */
    public function getInternalDatabaseNameAttribute(): string
    {
        return $this->db_name;
    }

    /**
     * Scope para obtener solo tenants activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Sobrescribir performInsert para evitar el sistema de JSON de Stancl
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query)
    {
        // Asegurar que created_at y updated_at estén configurados
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Usar solo los atributos que están en $fillable
        $attributes = $this->getAttributesForInsert();

        if (empty($attributes)) {
            return true;
        }

        // Ejecutar el insert directamente sin pasar por el sistema de Stancl
        $query->insert($attributes);

        $this->exists = true;
        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }
}
