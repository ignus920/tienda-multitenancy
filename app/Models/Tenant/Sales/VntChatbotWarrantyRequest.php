<?php

namespace App\Models\Tenant\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VntChatbotWarrantyRequest extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'vnt_chatbot_warranty_requests';

    protected $fillable = [
        'company_name',
        'reference_number',
        'advisor_name',
        'product_details',
        'description',
        'media_urls',
        'status',
        'warranty_id',
    ];

    protected $casts = [
        'media_urls' => 'array',
    ];

    public function warranty()
    {
        return $this->belongsTo(VntWarranty::class, 'warranty_id');
    }
}
