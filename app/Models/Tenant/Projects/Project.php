<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\User;

class Project extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_projects';

    protected $fillable = [
        'type',
        'title',
        'company_id',
        'description',
        'created_by',
        'assigned_to',
        'status',
        'qty',
        'price_unit',
        'total_value',
        'delivery_date',
        'suggested_delivery_date',
        'prod_observations',
        'completion_date',
        'lab_observations',
        'real_delivery_date',
        'close_observations'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'suggested_delivery_date' => 'date',
        'completion_date' => 'date',
        'real_delivery_date' => 'date',
        'qty' => 'integer',
        'price_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    /**
     * Perfiles que ven TODOS los proyectos. El resto solo ve los proyectos
     * donde son creador, "dirigido a" o participante del chat.
     * 1 = Super Administrador, 2 = Administrador, 15 = Gestión operativa
     */
    const FULL_ACCESS_PROFILES = [1, 2, 15];

    /**
     * Limita la consulta a los proyectos visibles para el usuario dado.
     * Los perfiles con acceso total no se filtran.
     */
    public function scopeVisibleTo($query, $user)
    {
        if (!$user || in_array($user->profile_id, self::FULL_ACCESS_PROFILES)) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('assigned_to', $user->id)
              ->orWhereHas('participants', fn($p) => $p->where('user_id', $user->id));
        });
    }

    /**
     * ¿Este usuario puede abrir el proyecto (listado o URL directa)?
     */
    public function canBeViewedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->profile_id, self::FULL_ACCESS_PROFILES)) {
            return true;
        }

        return (int) $this->created_by === (int) $user->id
            || (int) $this->assigned_to === (int) $user->id
            || $this->participants()->where('user_id', $user->id)->exists();
    }

    public function customer()
    {
        return $this->belongsTo(VntCompany::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class, 'project_id');
    }

    public function questions()
    {
        return $this->hasMany(ProjectQuestion::class, 'project_id');
    }

    public function mentions()
    {
        return $this->hasMany(ProjectMention::class, 'project_id');
    }

    public function advances()
    {
        return $this->hasMany(ProjectAdvance::class, 'project_id');
    }

    public function participants()
    {
        return $this->hasMany(ProjectParticipant::class, 'project_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }

    public function materials()
    {
        return $this->hasMany(ProjectMaterial::class, 'project_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ProjectStatusHistory::class, 'project_id')->orderBy('created_at', 'desc');
    }

    public function orders()
    {
        return $this->hasMany(ProjectOrder::class, 'project_id');
    }

    public function editHistories()
    {
        return $this->hasMany(ProjectEditHistory::class, 'project_id')->orderBy('created_at', 'desc');
    }

    public function getVencimientoStatusAttribute()
    {
        if ($this->type !== 'internal') {
            return null;
        }

        if (in_array($this->status, ['terminado', 'cerrado_entregado'])) {
            return null;
        }

        if (!$this->delivery_date) {
            return null;
        }

        $now = now();

        if ($now->greaterThan($this->delivery_date)) {
            return 'vencido';
        }

        $totalDays = max($this->created_at->diffInDays($this->delivery_date), 1);
        $remainingDays = $now->diffInDays($this->delivery_date, false);
        $remainingPct = $remainingDays / $totalDays;

        return $remainingPct <= 0.30 ? 'proximo_vencer' : null;
    }
}
