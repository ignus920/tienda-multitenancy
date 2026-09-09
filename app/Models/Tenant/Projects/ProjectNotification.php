<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectNotification extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_notifications';

    protected $fillable = [
        'user_id',
        'project_id',
        'message_id',
        'sender_id',
        'type',      // 'mensaje' o 'mencion'
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
        return $this->belongsTo(User::class, 'sender_id');
    }
}
