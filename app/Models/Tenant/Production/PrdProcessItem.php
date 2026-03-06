<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrdProcessItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'prd_process_item';
    protected $primaryKey = 'id';

    protected $fillable = [
        'processId',
        'itemId',
        'process_route_order',
    ];

    public function process()
    {
        return $this->belongsTo(PrdProcess::class, 'processId', 'id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Tenant\Items\Items::class, 'itemId', 'id');
    }
}
