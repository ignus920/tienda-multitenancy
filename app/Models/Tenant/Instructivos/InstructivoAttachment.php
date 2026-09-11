<?php

namespace App\Models\Tenant\Instructivos;

use Illuminate\Database\Eloquent\Model;

class InstructivoAttachment extends Model
{
    protected $connection = 'tenant';

    protected $table = 'ins_instructivo_attachments';

    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
        'created_at',
    ];

    public function entry()
    {
        return $this->belongsTo(InstructivoEntry::class, 'entry_id');
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
