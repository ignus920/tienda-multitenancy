<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectEditHistory extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_edit_histories';

    protected $fillable = [
        'project_id',
        'user_id',
        'old_title',
        'new_title',
        'old_description',
        'new_description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
