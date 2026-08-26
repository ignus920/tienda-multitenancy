<?php

namespace App\Models\Tenant\Projects;

use Illuminate\Database\Eloquent\Model;

class ProjectOrder extends Model
{
    protected $connection = 'tenant';

    protected $table = 'inv_project_orders';

    protected $fillable = [
        'project_id',
        'qty',
        'price_unit',
        'total_value',
        'observations',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
