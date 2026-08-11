<?php

namespace Database\Seeders;

use App\Models\StatusOccurrence;
use Illuminate\Database\Seeder;

/**
 * Insere o fluxo completo de status de ocorrência (Fase 3 do plano de execução):
 * da triagem inicial até o encerramento, incluindo os status já usados pelo fluxo do
 * motorista (Aguardando aceitação/Aceita/Recusada/Em deslocamento — nomes não alterados,
 * OccurrenceService e DriverOccurrenceController referenciam esses nomes literalmente).
 *
 * is_terminal marca os status que só saem do lugar via reabertura (ver
 * OccurrenceService::recordStatusChange() e OccurrencePolicy::reopen()).
 */
class StatusOccurrenceDriverSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Recebida', 'sort_order' => 10],
            ['name' => 'Em triagem', 'sort_order' => 20],
            ['name' => 'Validada', 'sort_order' => 30],
            ['name' => 'Aguardando despacho', 'sort_order' => 40],
            ['name' => 'Aguardando aceitação', 'sort_order' => 50],
            ['name' => 'Aceita', 'sort_order' => 60],
            ['name' => 'Recusada', 'sort_order' => 61],
            ['name' => 'Em deslocamento', 'sort_order' => 70],
            ['name' => 'Equipe no local', 'sort_order' => 75],
            ['name' => 'Em atendimento', 'sort_order' => 80],
            ['name' => 'Aguardando material', 'sort_order' => 85],
            ['name' => 'Resolvida', 'sort_order' => 90],
            ['name' => 'Finalizada', 'sort_order' => 100, 'is_terminal' => true],
            ['name' => 'Cancelada', 'sort_order' => 101, 'is_terminal' => true],
            ['name' => 'Duplicada', 'sort_order' => 102, 'is_terminal' => true],
            ['name' => 'Reaberta', 'sort_order' => 103],
        ];

        foreach ($statuses as $status) {
            StatusOccurrence::updateOrCreate(
                ['name' => $status['name']],
                ['sort_order' => $status['sort_order'], 'is_terminal' => $status['is_terminal'] ?? false]
            );
        }
    }
}
