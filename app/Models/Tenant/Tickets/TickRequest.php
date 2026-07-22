<?php

namespace App\Models\Tenant\Tickets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Items\Items;

class TickRequest extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tick_requests';

    protected $fillable = [
        'department_id',
        'status_id',
        'product_id',
        'supplier_id',
        'is_reactivated',
        'created_by',
        'assigned_to',
        'detail',
        'image_path',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Departamento al que pertenece la solicitud.
     */
    public function department()
    {
        return $this->belongsTo(TickDepartment::class, 'department_id');
    }

    /**
     * Estado actual de la solicitud.
     */
    public function status()
    {
        return $this->belongsTo(TickStatus::class, 'status_id');
    }

    /**
     * Usuario que creó la solicitud (Base de datos central).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario asignado para procesar la solicitud (Base de datos central).
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Historial de eventos y mensajes de la solicitud.
     */
    public function history()
    {
        return $this->hasMany(TickRequestHistory::class, 'request_id')->orderBy('created_at', 'asc');
    }

    /**
     * Producto relacionado con la solicitud.
     */
    public function product()
    {
        return $this->belongsTo(Items::class, 'product_id');
    }

    /**
     * Proveedor asignado a la solicitud (Base de datos central).
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}
