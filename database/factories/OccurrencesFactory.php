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
        $user = User::first();
        $client = Client::first();
        $issuing = Issuing::first();
        $typeOccurrence = TypeOccurrence::first();
        $statusOccurrence = StatusOccurrence::first();

        if (!$user || !$client || !$issuing || !$typeOccurrence || !$statusOccurrence) {
            throw new \RuntimeException('Execute seeders de User, Client, Issuing, TypeOccurrence e StatusOccurrence antes de OccurrenceTableSeeder.');
        }

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'name' => $this->faker->name(),
            'cpf' => $this->faker->numerify('###.###.###-##'),
            'rg' => $this->faker->numerify('##.###.###-#'),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'users_id' => $user->id,
            'clients_id' => $client->id,
            'issuings_id' => $issuing->id,
            'type_occurrences_id' => $typeOccurrence->id,
            'status_occurrences_id' => $statusOccurrence->id,
        ];
    }
}
