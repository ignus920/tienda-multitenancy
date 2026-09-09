<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;

class ProjectNovelty extends Model
{
    protected $connection = 'tenant';
    protected $table = 'inv_project_novelties';

    protected $fillable = [
        'project_id',
        'user_id',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
