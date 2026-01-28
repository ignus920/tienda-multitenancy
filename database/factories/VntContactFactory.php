<?php

namespace Database\Factories;

use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class VntContactFactory extends Factory
{
    protected $model = VntContact::class;

    public function definition(): array
    {
        return [
            'firstName' => fake()->firstName(),
            'secondName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'secondLastName' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_contact' => fake()->phoneNumber(),
            'contact' => fake()->phoneNumber(),
            'status' => true,
            'warehouseId' => VntWarehouse::factory(),
            'positionId' => null,
            'store' => null,
        ];
    }
}
