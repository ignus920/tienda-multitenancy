<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $uuid = (string) Str::uuid();
        return [
            'id'             => $uuid,
            'name'           => $this->faker->company(),
            'email'          => $this->faker->unique()->companyEmail(),
            'phone'          => $this->faker->phoneNumber(),
            'is_active'      => true,
            'database_setup' => false,
            'db_name'        => 'test_' . Str::random(8),
            'db_user'        => 'root',
            'db_password'    => '',
            'db_host'        => '127.0.0.1',
            'db_port'        => 3306,
        ];
    }
}
