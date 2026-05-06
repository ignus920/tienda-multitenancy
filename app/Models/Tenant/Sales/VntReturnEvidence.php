<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntReturnEvidence extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_return_evidences';

    protected $fillable = [
        'return_id',
        'file_path',
    ];

    public function vntReturn()
    {
        return $this->belongsTo(VntReturn::class, 'return_id');
    }
}
