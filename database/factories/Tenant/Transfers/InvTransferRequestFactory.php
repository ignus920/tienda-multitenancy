<?php

namespace Database\Factories\Tenant\Transfers;

use App\Models\Tenant\Transfers\InvTransferRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Transfers\InvTransferRequest>
 */
class InvTransferRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvTransferRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['REGISTRADO', 'EN PROGRESO', 'ENTREGADO']),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            'quoteId' => $this->faker->optional()->numberBetween(1, 1000),
            'warehouseId' => $this->faker->numberBetween(1, 10),
            'observations' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the transfer request is registered.
     */
    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'REGISTRADO',
        ]);
    }

    /**
     * Indicate that the transfer request is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'EN PROGRESO',
        ]);
    }

    /**
     * Indicate that the transfer request is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'ENTREGADO',
        ]);
    }
}
