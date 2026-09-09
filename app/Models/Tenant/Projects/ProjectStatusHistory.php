<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectStatusHistory extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'inv_project_status_history';

    protected $fillable = [
        'project_id',
        'from_status',
        'to_status',
        'changed_by'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
