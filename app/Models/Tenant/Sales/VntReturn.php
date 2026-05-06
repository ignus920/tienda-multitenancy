<?php

namespace App\Models\Tenant\Sales;

use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntReturn extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_returns';

    protected $fillable = [
        'remission_id',
        'item_id',
        'user_id',
        'requested_at',
        'original_qty',
        'commercial_qty',
        'lab_qty',
        'status',
        'obs_commercial',
        'obs_lab',
        'obs_warehouse',
        'obs_accounting',
        'nc_number',
        'nc_file',
        'lab_processed_at',
        'accounting_processed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'lab_processed_at' => 'datetime',
        'accounting_processed_at' => 'datetime',
        'original_qty' => 'decimal:2',
        'commercial_qty' => 'decimal:2',
        'lab_qty' => 'decimal:2',
    ];

    // Relaciones
    public function remission()
    {
        return $this->belongsTo(InvRemissions::class, 'remission_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evidences()
    {
        return $this->hasMany(VntReturnEvidence::class, 'return_id');
    }

    // Accessors para el Badge de Estado
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            1 => 'Comercial',
            2 => 'Laboratorio',
            3 => 'Bodega',
            4 => 'Contabilidad',
            6 => 'Total',
            default => 'Desconocido',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            1 => 'bg-yellow-500',
            2 => 'bg-red-500',
            3 => 'bg-green-500',
            4 => 'bg-blue-500',
            6 => 'bg-gray-500',
            default => 'bg-gray-300',
        };
    }
}
