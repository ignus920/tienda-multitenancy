<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\User;
use App\Helpers\PermissionHelper;

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
        'phase',
        'phase_started_at',
        'qty',
        'price_unit',
        'total_value',
        'delivery_date',
        'suggested_delivery_date',
        'prod_observations',
        'completion_date',
        'lab_observations',
        'real_delivery_date',
        'close_observations',
        'materials_locked',
        'materials_locked_by',
        'materials_locked_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'suggested_delivery_date' => 'date',
        'completion_date' => 'date',
        'real_delivery_date' => 'date',
        'phase_started_at' => 'datetime',
        'qty' => 'integer',
        'price_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
        'materials_locked' => 'boolean',
        'materials_locked_at' => 'datetime',
    ];

    /**
     * Perfiles que ven TODOS los proyectos. El resto solo ve los proyectos
     * donde es participante (el creador y el "dirigido a" ya quedan
     * registrados como participante automáticamente al crear el proyecto).
     * 1 = Super Administrador, 2 = Administrador.
     */
    const FULL_ACCESS_PROFILES = [1, 2];

    /**
     * Nombre del permiso (catálogo usr_permissions) que da acceso a TODOS
     * los proyectos sin importar el perfil — excepción puntual por usuario
     * (ej. la coordinadora de proyectos), asignable desde Usuarios sin
     * tocar el perfil completo.
     */
    const VIEW_ALL_PERMISSION = 'Proyectos - Ver Todos';

    /**
     * ¿Este usuario tiene acceso total a proyectos (por perfil o por
     * excepción individual de permisos)?
     */
    protected static function hasFullProjectAccess($user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->profile_id, self::FULL_ACCESS_PROFILES)
            || PermissionHelper::userCan(self::VIEW_ALL_PERMISSION);
    }

    /**
     * Limita la consulta a los proyectos visibles para el usuario dado.
     * Los perfiles/usuarios con acceso total no se filtran.
     */
    public function scopeVisibleTo($query, $user)
    {
        if (!$user || self::hasFullProjectAccess($user)) {
            return $query;
        }

        // Nota: created_by/assigned_to quedan como respaldo por si algún
        // proyecto antiguo no tiene fila en inv_project_participants (la
        // auto-inscripción del creador/asignado se agregó después) — en la
        // práctica hoy ya son participantes siempre, así que esto no amplía
        // el acceso, solo evita ocultar proyectos viejos por datos previos.
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

        if (self::hasFullProjectAccess($user)) {
            return true;
        }

        return (int) $this->created_by === (int) $user->id
            || (int) $this->assigned_to === (int) $user->id
            || $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * ¿Este usuario puede reactivar el proyecto (una vez cerrado_entregado)?
     * Solo el creador o un perfil de acceso total — decisión explícita del
     * cliente: es una acción más sensible que ver o cerrar el proyecto.
     */
    public function canBeReactivatedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->profile_id, self::FULL_ACCESS_PROFILES)) {
            return true;
        }

        return (int) $this->created_by === (int) $user->id;
    }

    public function customer()
    {
        return $this->belongsTo(VntCompany::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function materialsLockedBy()
    {
        return $this->belongsTo(User::class, 'materials_locked_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class, 'project_id');
    }

    /**
     * Último mensaje del chat (una sola fila por proyecto, vía subconsulta
     * correlacionada) — usado para comparar contra ProjectParticipant::last_read_at
     * y así saber si el chat tiene algo nuevo sin abrir.
     */
    public function latestMessage()
    {
        return $this->hasOne(ProjectMessage::class, 'project_id')->latestOfMany();
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

    /**
     * Fases anteriores ya cerradas (snapshot permanente de cada cierre,
     * para auditoría, ya que las columnas de cierre en inv_projects se
     * reutilizan para la fase actual tras cada reactivación).
     */
    public function phaseHistory()
    {
        return $this->hasMany(ProjectPhaseHistory::class, 'project_id')->orderBy('phase_number', 'desc');
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

        $phaseStart = $this->phase_started_at ?? $this->created_at;
        $totalDays = max($phaseStart->diffInDays($this->delivery_date), 1);
        $remainingDays = $now->diffInDays($this->delivery_date, false);
        $remainingPct = $remainingDays / $totalDays;

        return $remainingPct <= 0.30 ? 'proximo_vencer' : null;
    }
}
