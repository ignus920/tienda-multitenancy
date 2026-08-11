<?php

namespace App\Models\Tenant\Tickets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TickDepartment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tick_departments';

    protected $fillable = [
        'name',
        'description',
        'status',
        'order',
    ];

    /**
     * Usuarios vinculados a este departamento.
     */
    public function users()
    {
        $tenantDatabase = config('database.connections.tenant.database');
        
        return $this->belongsToMany(
            User::class,
            $tenantDatabase . '.tick_department_user',
            'department_id',
            'user_id'
        )->withPivot('status')->withTimestamps();
    }

    /**
     * Solicitudes dirigidas a este departamento.
     */
    public function requests()
    {
        return $this->hasMany(TickRequest::class, 'department_id');
    }
}
