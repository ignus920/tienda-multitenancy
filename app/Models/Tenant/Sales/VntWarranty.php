<?php

namespace App\Models\Tenant\Sales;

use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntWarranty extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_warranties';

    protected $fillable = [
        'remission_id',
        'consecutive',
        'user_id',
        'status',
        'admin_concept',
        'admin_solution',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relaciones
    public function remission()
    {
        return $this->belongsTo(InvRemissions::class, 'remission_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(VntWarrantyItem::class, 'warranty_id');
    }

    // Accessors de Estado
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            1 => 'Creado/Pendiente Admin',
            2 => 'En Laboratorio',
            3 => 'En Importaciones',
            4 => 'Resuelto',
            default => 'Desconocido',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            1 => 'bg-yellow-500 text-white',
            2 => 'bg-red-500 text-white',
            3 => 'bg-indigo-500 text-white',
            4 => 'bg-green-500 text-white',
            default => 'bg-gray-300 text-gray-800',
        };
    }
}
