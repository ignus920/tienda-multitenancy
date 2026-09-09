<?php

namespace App\Models\Tenant\Sales;

use App\Models\Tenant\Items\Items;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntWarrantyItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_warranty_items';

    protected $fillable = [
        'warranty_id',
        'item_id',
        'quantity',
        'failure_description',
        'client_request',
        'lab_concept',
        'imports_concept',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    // Relaciones
    public function warranty()
    {
        return $this->belongsTo(VntWarranty::class, 'warranty_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }

    public function evidences()
    {
        return $this->hasMany(VntWarrantyEvidence::class, 'warranty_item_id');
    }
}
