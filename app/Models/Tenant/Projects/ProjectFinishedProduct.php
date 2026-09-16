<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;

class ProjectFinishedProduct extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_project_finished_products';

    protected $fillable = [
        'project_id',
        'description',
        'price',
        'quantity',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
