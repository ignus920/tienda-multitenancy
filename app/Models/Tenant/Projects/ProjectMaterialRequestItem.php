<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Items\Items;

class ProjectMaterialRequestItem extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_material_request_items';

    protected $fillable = [
        'request_id',
        'project_material_id',
        'item_id',
        'description',
        'quantity_requested',
        'is_removed',
    ];

    protected $casts = [
        'is_removed' => 'boolean',
        'quantity_requested' => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(ProjectMaterialRequest::class, 'request_id');
    }

    public function projectMaterial()
    {
        return $this->belongsTo(ProjectMaterial::class, 'project_material_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
