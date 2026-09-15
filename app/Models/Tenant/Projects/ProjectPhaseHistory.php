<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectPhaseHistory extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_phase_history';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'phase_number',
        'delivery_date',
        'completion_date',
        'lab_observations',
        'real_delivery_date',
        'close_observations',
        'closed_by',
        'reactivated_by',
        'reactivated_at',
        'created_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'completion_date' => 'date',
        'real_delivery_date' => 'date',
        'reactivated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reactivatedBy()
    {
        return $this->belongsTo(User::class, 'reactivated_by');
    }
}
