<?php

namespace App\Models\Tenant\Instructivos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class Instructivo extends Model
{
    protected $connection = 'tenant';

    protected $table = 'ins_instructivos';

    protected $fillable = [
        'department_id',
        'title',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(InstructivoDepartment::class, 'department_id');
    }

    public function entries()
    {
        return $this->hasMany(InstructivoEntry::class, 'instructivo_id')->orderByDesc('created_at');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
