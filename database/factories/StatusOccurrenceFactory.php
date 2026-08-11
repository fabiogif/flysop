<?php

namespace Database\Factories;

use App\Models\StatusOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusOccurrenceFactory extends Factory
{
    protected $model = StatusOccurrence::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'is_terminal' => false,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
