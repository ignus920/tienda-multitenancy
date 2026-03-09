<?php

namespace App\Models\Tenant\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpStatusHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imp_status_history';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'import_id',
        'previous_state',
        'new_state',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_id' => 'integer',
        'previous_state' => 'integer',
        'new_state' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con la importacion
     */
    public function import()
    {
        return $this->belongsTo(ImpImports::class, 'import_id', 'id');
    }

    /**
     * Relación con status => Estado anterior
     */
    public function previousStatus()
    {
        return $this->belongsTo(ImpStatus::class, 'previous_state', 'id');
    }

    /**
     * Relación con status => Nuevo estado
     */
    public function newStatus()
    {
        return $this->belongsTo(ImpStatus::class, 'new_state', 'id');
    }
}
