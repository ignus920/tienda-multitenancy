<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Projects\Project;

class ProjectTask extends Model
{
    protected $connection = 'tenant';
    
    protected $fillable = [
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'completed_at',
        'completion_note'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reassignments()
    {
        return $this->hasMany(ProjectTaskReassignment::class, 'task_id')->orderBy('created_at', 'desc');
    }
}
