<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsrProfile extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'usr_profiles';

    protected $fillable = [
        'name',
        'alias',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relación con usuarios
    public function users()
    {
        return $this->hasMany(\App\Models\Auth\User::class, 'profile_id');
    }

    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeBySuperAdmin($query)
    {
        return $query->where('alias', 'super_admin');
    }
}
