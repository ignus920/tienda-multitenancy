<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'two_factor_enabled',
        'two_factor_type',
        'two_factor_secret',
        'two_factor_failed_attempts',
        'two_factor_locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_failed_attempts' => 'integer',
            'two_factor_locked_until' => 'datetime',
        ];
    }

    /**
     * Relación muchos a muchos con tenants.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenants')
            ->withPivot('role', 'is_active', 'last_accessed_at')
            ->withTimestamps();
    }

    /**
     * Relación con códigos de autenticación de dos factores.
     */
    public function twoFactorCodes(): HasMany
    {
        return $this->hasMany(TwoFactorCode::class);
    }

    /**
     * Verifica si el usuario tiene 2FA habilitado.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled;
    }

    /**
     * Verifica si el usuario está bloqueado por intentos fallidos de 2FA.
     */
    public function isTwoFactorLocked(): bool
    {
        if (!$this->two_factor_locked_until) {
            return false;
        }

        return Carbon::now()->lessThan($this->two_factor_locked_until);
    }

    /**
     * Resetea los intentos fallidos de 2FA.
     */
    public function resetTwoFactorAttempts(): void
    {
        $this->update([
            'two_factor_failed_attempts' => 0,
            'two_factor_locked_until' => null,
        ]);
    }

    /**
     * Incrementa los intentos fallidos de 2FA.
     */
    public function incrementTwoFactorAttempts(): void
    {
        $this->increment('two_factor_failed_attempts');

        // Bloquear después de 3 intentos fallidos por 15 minutos
        if ($this->two_factor_failed_attempts >= 3) {
            $this->update([
                'two_factor_locked_until' => Carbon::now()->addMinutes(15),
            ]);
        }
    }

    /**
     * Obtener tenants activos del usuario.
     */
    public function activeTenants()
    {
        return $this->tenants()->wherePivot('is_active', true);
    }

    /**
     * Verifica si el usuario tiene acceso a un tenant específico.
     */
    public function hasAccessToTenant(string $tenantId): bool
    {
        return $this->activeTenants()->where('tenants.id', $tenantId)->exists();
    }
}
