<?php

namespace App\Models\Tenant\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrdProcess extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'prd_process';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'previous_notes',
        'inventory_consumption',
        'documents_generates',
        'status',
    ];

    public function processItems()
    {
        return $this->hasMany(PrdProcessItem::class, 'processId', 'id');
    }
}
