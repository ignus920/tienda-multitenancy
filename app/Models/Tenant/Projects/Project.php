<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\User;

class Project extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_projects';

    protected $fillable = [
        'type',
        'title',
        'company_id',
        'description',
        'created_by',
        'status',
        'qty',
        'price_unit',
        'total_value',
        'delivery_date',
        'prod_observations',
        'completion_date',
        'lab_observations',
        'real_delivery_date',
        'close_observations'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'completion_date' => 'date',
        'real_delivery_date' => 'date',
        'qty' => 'integer',
        'price_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(VntCompany::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class, 'project_id');
    }

    public function questions()
    {
        return $this->hasMany(ProjectQuestion::class, 'project_id');
    }

    public function advances()
    {
        return $this->hasMany(ProjectAdvance::class, 'project_id');
    }

    public function participants()
    {
        return $this->hasMany(ProjectParticipant::class, 'project_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }
}
