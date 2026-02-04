<?php

namespace Database\Factories;

use App\Models\Issuing;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssuingFactory extends Factory
{
    protected $model = Issuing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'created_at' => now(),
        ];
    }
}
