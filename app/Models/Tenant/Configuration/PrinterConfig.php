<?php

namespace App\Models\Tenant\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class PrinterConfig extends Model
{
    protected $connection = 'tenant';
    protected $table = 'vnt_printer_configurations';

    protected $fillable = [
        'context',
        'user_id',
        'printer_name',
        'proxy_url',
        'is_active',
    ];

    /**
     * Relación con el usuario (Central)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    /**
     * Obtener la configuración para un contexto y usuario específico
     */
    public static function getConfig($contexto, $userId)
    {
        return self::where('context', $contexto)
            ->where('user_id', $userId)
            ->first();
    }
}
