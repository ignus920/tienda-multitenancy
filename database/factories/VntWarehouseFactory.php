<?php

namespace Database\Factories;

use App\Models\Central\VntWarehouse;
use App\Models\Central\VntCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class VntWarehouseFactory extends Factory
{
    protected $model = VntWarehouse::class;

    public function definition(): array
    {
        return [
            'companyId' => VntCompany::factory(),
            'name' => fake()->company() . ' - ' . fake()->city(),
            'address' => fake()->address(),
            'postcode' => fake()->postcode(),
            'cityId' => 1,
            'billingFormat' => 1,
            'is_credit' => false,
            'termId' => 1,
            'creditLimit' => 0,
            'status' => true,
            'main' => 0,
        ];
    }
}
