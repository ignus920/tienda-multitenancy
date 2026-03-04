<?php

namespace App\Models\Tenant\Tickets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class TickRequestHistory extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tick_request_history';

    // No usamos updated_at para el historial, solo creación
    const UPDATED_AT = null;

    protected $fillable = [
        'request_id',
        'user_id',
        'from_status_id',
        'to_status_id',
        'message',
    ];

    /**
     * Solicitud vinculada.
     */
    public function request()
    {
        return $this->belongsTo(TickRequest::class, 'request_id');
    }

    /**
     * Usuario que generó este registro en el historial.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias para el estado nuevo (para compatibilidad con carga ansiosa).
     */
    public function status()
    {
        return $this->toStatus();
    }

    /**
     * Estado anterior.
     */
    public function fromStatus()
    {
        return $this->belongsTo(TickStatus::class, 'from_status_id');
    }

    /**
     * Estado nuevo (después del cambio).
     */
    public function toStatus()
    {
        return $this->belongsTo(TickStatus::class, 'to_status_id');
    }
}
