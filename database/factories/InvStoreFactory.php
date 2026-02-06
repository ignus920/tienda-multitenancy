<?php

namespace Database\Factories;

use App\Models\Tenant\Movements\InvStore;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvStoreFactory extends Factory
{
    protected $model = InvStore::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' - Bodega ' . fake()->numberBetween(1, 100),
            'warehouseId' => fake()->numberBetween(1, 10),
            'store_manager' => null,
            'status' => 1,
        ];
    }

    /**
     * Indicate that the store is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the store is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }
}
