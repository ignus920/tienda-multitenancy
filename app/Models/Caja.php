<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'saldo_inicial',
        'saldo_actual',
        'fecha_apertura',
        'fecha_cierre',
        'usuario_id',
        'estado',
        'observaciones',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'saldo_inicial' => 'decimal:2',
            'saldo_actual' => 'decimal:2',
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'usuario_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function movimientoCajas(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class);
    }
}
