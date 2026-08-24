<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectFile extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_files';

    protected $fillable = [
        'project_id',
        'message_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function message()
    {
        return $this->belongsTo(ProjectMessage::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
