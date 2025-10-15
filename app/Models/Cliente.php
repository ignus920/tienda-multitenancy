<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;
use Nnjeim\World\Models\City;

class Cliente extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city_id',
        'state_id',
        'country_id',
        'tax_id',
        'type',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'city_id' => 'integer',
            'state_id' => 'integer',
            'country_id' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'customer_id');
    }

    // Relaciones geográficas con Laravel World
    public function country(): BelongsTo
    {
        return $this->setConnection('central')->belongsTo(Country::class, 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->setConnection('central')->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->setConnection('central')->belongsTo(City::class, 'city_id');
    }
}
