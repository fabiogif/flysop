<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Issuing;
use App\Models\Occurrences;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OccurrencesFactory extends Factory
{
    protected $model = Occurrences::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(12),
            'name' => $this->faker->name(),
            'cpf' => $this->faker->numerify('###.###.###-##'),
            'rg' => $this->faker->numerify('##.###.###-#'),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->numerify('(##) #####-####'),
            'latitude' => $this->faker->latitude(-33, 5),
            'longitude' => $this->faker->longitude(-73, -34),
            'users_id' => User::factory(),
            'clients_id' => Client::factory(),
            'issuings_id' => Issuing::factory(),
            'type_occurrences_id' => TypeOccurrence::factory(),
            'status_occurrences_id' => StatusOccurrence::factory(),
        ];
    }
}
