<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'tax_id',
        'type',
        'active',
        'api_data_id',
        'business_name',
        'identification_number',
        'identification_type',
        'dv',
        'kind_of_person',
        'regime',
        'fiscal_responsibilities',
        'postcode',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'active' => 'boolean',
            'type' => 'string',
            'api_data_id' => 'string',
            'fiscal_responsibilities' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeBusiness($query)
    {
        return $query->where('type', 'business');
    }
}
