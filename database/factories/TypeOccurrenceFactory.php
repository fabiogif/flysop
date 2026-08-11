<?php

namespace Database\Factories;

use App\Models\TypeOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeOccurrenceFactory extends Factory
{
    protected $model = TypeOccurrence::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
        ];
    }
}
