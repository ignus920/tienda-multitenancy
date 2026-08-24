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
