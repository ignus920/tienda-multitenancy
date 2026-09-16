<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use App\Models\Tenant\Movements\InvInventoryAdjustment;

class ProjectMaterialRequest extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_material_requests';

    protected $fillable = [
        'project_id',
        'status',
        'requested_by',
        'inventory_adjustment_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(ProjectMaterialRequestItem::class, 'request_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function inventoryAdjustment()
    {
        return $this->belongsTo(InvInventoryAdjustment::class, 'inventory_adjustment_id');
    }
}
