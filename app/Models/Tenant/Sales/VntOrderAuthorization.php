<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Remissions\InvRemissions;

class VntOrderAuthorization extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_order_authorizations';

    protected $fillable = [
        'remission_id',
        'auth_type',
        'status',
        'user_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el pedido (Remisión)
     */
    public function remission()
    {
        return $this->belongsTo(InvRemissions::class, 'remission_id');
    }

    /**
     * Relación con el usuario que autorizó (Base Central)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper para obtener el usuario desde la base central
     */
    public function getUser()
    {
        return User::on('central')->find($this->user_id);
    }
}
