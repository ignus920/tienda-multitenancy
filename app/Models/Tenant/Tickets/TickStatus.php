<?php

namespace App\Models\Tenant\Tickets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TickStatus extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tick_statuses';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'order',
    ];

    /**
     * Solicitudes que están actualmente en este estado.
     */
    public function requests()
    {
        return $this->hasMany(TickRequest::class, 'status_id');
    }
}
