<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'plan_id' => null,
            'uuid' => (string) Str::uuid(),
            'cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
            'name' => $name,
            'email' => $this->faker->unique()->companyEmail(),
            'url' => Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'active' => '1',
            'subscription_active' => false,
            'subscription_suspended' => false,
        ];
    }
}
