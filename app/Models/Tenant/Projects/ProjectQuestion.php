<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectQuestion extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_questions';

    protected $fillable = [
        'project_id',
        'asked_by',
        'question',
        'answer',
        'answered_by',
        'status' // pendiente, respondida, cerrada
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function asker()
    {
        return $this->belongsTo(User::class, 'asked_by');
    }

    public function answerer()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
