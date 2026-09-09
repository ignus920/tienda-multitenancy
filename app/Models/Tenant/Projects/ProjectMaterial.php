<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\User;

class ProjectMaterial extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'inv_project_materials';

    protected $fillable = [
        'project_id',
        'item_id',
        'origin',
        'description',
        'quantity',
        'unit_value',
        'line_cost',
        'observations',
        'created_by',
        'is_active',
        'deactivation_reason',
        'clear_reason'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'line_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
