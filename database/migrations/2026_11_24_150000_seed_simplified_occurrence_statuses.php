<?php

use Database\Seeders\StatusOccurrenceDriverSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * O deploy (fly.toml release_command) só roda `migrate`, nunca `db:seed`: sem isto os status
 * novos (Aberta, Encaminhada, Em andamento, Pendente, Concluída) não chegariam à produção.
 * O seeder é idempotente (updateOrCreate por nome) — rodar de novo só alinha sort_order/is_terminal.
 */
class SeedSimplifiedOccurrenceStatuses extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        (new StatusOccurrenceDriverSeeder())->run();
    }

    public function down(): void
    {
        // Não apaga: ocorrências podem já estar nesses status (FK em occurrences/histórico).
    }
}
