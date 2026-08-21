<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class ProjectMessage extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_messages';

    protected $fillable = [
        'project_id',
        'user_id',
        'message',
        'reply_to_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repliedTo()
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    public function mentions()
    {
        return $this->hasMany(ProjectMention::class, 'message_id');
    }
}
