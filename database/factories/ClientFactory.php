<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            throw new \RuntimeException('Execute TenantsTableSeeder e AdminUserSeeder antes de ClientTableSeeder.');
        }
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('123456'),
            'tenant_id' => $tenant->id,
            'uuid' => Uuid::uuid4()->toString(),
        ];
    }
}
