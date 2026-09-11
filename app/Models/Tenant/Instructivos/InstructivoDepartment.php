<?php

namespace App\Models\Tenant\Instructivos;

use Illuminate\Database\Eloquent\Model;

class InstructivoDepartment extends Model
{
    protected $connection = 'tenant';

    protected $table = 'ins_departments';

    protected $fillable = [
        'name',
        'icon',
        'color',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    public function instructivos()
    {
        return $this->hasMany(Instructivo::class, 'department_id');
    }

    public function members()
    {
        return $this->hasMany(InstructivoDepartmentUser::class, 'department_id');
    }
}
