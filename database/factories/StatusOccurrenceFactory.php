<?php

namespace Database\Factories;

use App\Models\StatusOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusOccurrenceFactory extends Factory
{
    protected $model = StatusOccurrence::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => "Aberta",
            'created_at' => now(),
        ];
    }
}
