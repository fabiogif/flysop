<?php

namespace Database\Seeders;

use App\Models\StatusOccurrence;
use Illuminate\Database\Seeder;

/**
 * Insere status de ocorrência usados no fluxo do motorista (aceitar/recusar/deslocamento).
 */
class StatusOccurrenceDriverSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'Aguardando aceitação',
            'Aceita',
            'Recusada',
            'Em deslocamento',
        ];

        foreach ($statuses as $name) {
            StatusOccurrence::firstOrCreate(['name' => $name]);
        }
    }
}
