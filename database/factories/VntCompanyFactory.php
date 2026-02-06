<?php

namespace Database\Factories;

use App\Models\Central\VntCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class VntCompanyFactory extends Factory
{
    protected $model = VntCompany::class;

    public function definition(): array
    {
        return [
            'businessName' => fake()->company(),
            'billingEmail' => fake()->companyEmail(),
            'firstName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'secondLastName' => fake()->lastName(),
            'secondName' => fake()->firstName(),
            'identification' => fake()->numerify('##########'),
            'checkDigit' => fake()->numberBetween(0, 9),
            'status' => 1,
            'typePerson' => 'J', // Juridica
            'typeIdentificationId' => 1,
            'regimeId' => 1,
            'code_ciiu' => fake()->numerify('####'),
            'fiscalResponsabilityId' => 1,
        ];
    }
}
