<?php

namespace App\Models\Tenant\Instructivos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class InstructivoEntry extends Model
{
    protected $connection = 'tenant';

    protected $table = 'ins_instructivo_entries';

    protected $fillable = [
        'instructivo_id',
        'title',
        'body',
        'status',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    public function instructivo()
    {
        return $this->belongsTo(Instructivo::class, 'instructivo_id');
    }

    public function attachments()
    {
        return $this->hasMany(InstructivoAttachment::class, 'entry_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
