<?php

namespace App\Models\Tenant\TaskPlanner;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class EmployeeUnavailability extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tsk_employee_unavailability';

    protected $fillable = [
        'user_id',
        'start_datetime',
        'end_datetime',
        'type',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    const TYPES = [
        'permiso_personal' => 'Permiso personal',
        'cita_medica' => 'Cita médica',
        'incapacidad' => 'Incapacidad',
        'vacaciones' => 'Vacaciones',
        'reunion' => 'Reunión',
        'trabajo_externo' => 'Trabajo externo',
        'otro' => 'Otro',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
