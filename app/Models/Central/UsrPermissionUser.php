<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Excepción de permisos para un usuario puntual.
 *
 * Cada columna de acción (show / creater / editer / deleter) es tri-estado:
 *   null  -> hereda del perfil
 *   true  -> permitir
 *   false -> denegar
 */
class UsrPermissionUser extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'usr_permissions_users';

    protected $fillable = [
        'userId',
        'permissionId',
        'show',
        'creater',
        'editer',
        'deleter',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'userId' => 'integer',
            'permissionId' => 'integer',
            // show/creater/editer/deleter se dejan SIN cast: son tri-estado
            // (null = heredar). Un cast 'boolean' convertiría null en false.
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /** Devuelve el valor tri-estado de una acción: null | true | false */
    public function actionValue(string $column): ?bool
    {
        $v = $this->getAttribute($column);

        return is_null($v) ? null : (bool) $v;
    }

    public function permission()
    {
        return $this->belongsTo(UsrPermission::class, 'permissionId');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('userId', $userId);
    }
}
