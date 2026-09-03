<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntWarrantyEvidence extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_warranty_evidences';

    const UPDATED_AT = null;

    protected $fillable = [
        'warranty_item_id',
        'file_path',
        'file_type',
    ];

    public function warrantyItem()
    {
        return $this->belongsTo(VntWarrantyItem::class, 'warranty_item_id');
    }
}
