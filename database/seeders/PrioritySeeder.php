<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

/**
 * Prioridades padrão por gravidade (Eixo 4): o peso ordena as filas e o SLA define o prazo
 * (due_at) da ocorrência. Idempotente por nome — não sobrescreve ajuste feito no painel.
 */
class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'Crítica', 'weight' => 100, 'color' => '#dc3545', 'default_sla_hours' => 4],
            ['name' => 'Alta', 'weight' => 75, 'color' => '#fd7e14', 'default_sla_hours' => 24],
            ['name' => 'Média', 'weight' => 50, 'color' => '#ffc107', 'default_sla_hours' => 72],
            ['name' => 'Baixa', 'weight' => 25, 'color' => '#28a745', 'default_sla_hours' => 168],
        ];

        foreach ($priorities as $priority) {
            Priority::firstOrCreate(['name' => $priority['name']], $priority);
        }
    }
}
