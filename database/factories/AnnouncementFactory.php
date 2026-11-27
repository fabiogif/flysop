<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Issuing;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'issuing_id' => Issuing::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'is_published' => true,
            'published_at' => now(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn () => ['is_published' => false, 'published_at' => null]);
    }
}
