<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriorityFactory extends Factory
{
    protected $model = Priority::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Baixa', 'Média', 'Alta', 'Crítica']) . ' ' . $this->faker->unique()->numerify('##'),
            'weight' => $this->faker->numberBetween(1, 100),
            'color' => $this->faker->hexColor(),
            'default_sla_hours' => $this->faker->randomElement([4, 8, 24, 48]),
        ];
    }
}
