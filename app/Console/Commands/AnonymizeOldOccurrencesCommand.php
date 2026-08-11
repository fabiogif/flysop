<?php

namespace App\Console\Commands;

use App\Models\Occurrences;
use Illuminate\Console\Command;

/**
 * Retenção/anonimização (LGPD, Fase 6): ocorrências finalizadas há muito tempo não
 * precisam mais guardar dado pessoal identificável. Título/descrição/protocolo e o
 * histórico de status continuam — é o conteúdo operacional do serviço público, não dado
 * pessoal por si só.
 */
class AnonymizeOldOccurrencesCommand extends Command
{
    protected $signature = 'occurrences:anonymize-old
                            {--days=730 : Anonimizar ocorrências finalizadas há mais de N dias (padrão: 2 anos)}
                            {--dry-run : Apenas exibir quantos registros seriam anonimizados}';

    protected $description = 'Remove dados pessoais (nome, CPF, RG, e-mail, telefone) de ocorrências finalizadas há muito tempo.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $query = Occurrences::whereHas('statusOccurence', fn ($q) => $q->where('is_terminal', true))
            ->where('updated_at', '<', $cutoff)
            ->where(function ($q) {
                $q->whereNotNull('cpf')->orWhereNotNull('rg')->orWhereNotNull('email')->orWhereNotNull('phone')
                    ->orWhere('name', '!=', '[Removido - LGPD]');
            });

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nenhuma ocorrência elegível para anonimização.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("[Dry-run] {$count} ocorrência(s) seriam anonimizadas (finalizadas há mais de {$days} dias).");

            return self::SUCCESS;
        }

        $query->chunkById(100, function ($occurrences) {
            foreach ($occurrences as $occurrence) {
                $occurrence->disableLogging();
                $occurrence->update([
                    'name' => '[Removido - LGPD]',
                    'cpf' => null,
                    'rg' => null,
                    'email' => null,
                    'phone' => null,
                ]);
                $occurrence->enableLogging();
            }
        });

        $this->info("{$count} ocorrência(s) anonimizada(s).");

        return self::SUCCESS;
    }
}
