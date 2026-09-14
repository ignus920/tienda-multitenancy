<?php

namespace App\Models\Tenant\Instructivos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;

class InstructivoDepartmentUser extends Model
{
    protected $connection = 'tenant';

    protected $table = 'ins_department_user';

    public $timestamps = false;

    protected $fillable = [
        'department_id',
        'user_id',
    ];

    public function department()
    {
        return $this->belongsTo(InstructivoDepartment::class, 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
