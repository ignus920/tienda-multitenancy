<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectMention extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_mentions';

    protected $fillable = [
        'project_id',
        'message_id',
        'mentioned_by',
        'mentioned_to',
        'status' // pendiente, vista, respondida
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function message()
    {
        return $this->belongsTo(ProjectMessage::class, 'message_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'mentioned_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'mentioned_to');
    }
}
