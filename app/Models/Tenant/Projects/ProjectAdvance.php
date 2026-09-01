<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectAdvance extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_advances';

    protected $fillable = [
        'project_id',
        'user_id',
        'description',
        'percentage'
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
