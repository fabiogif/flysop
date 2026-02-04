<?php

namespace Database\Factories;

use App\Models\TypeOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeOccurrenceFactory extends Factory
{
    protected $model = TypeOccurrence::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => "Alagamento",
            'created_at' => now(),
        ];
    }
}
