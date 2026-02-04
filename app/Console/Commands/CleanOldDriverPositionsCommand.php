<?php

namespace App\Console\Commands;

use App\Models\DriverPosition;
use Illuminate\Console\Command;

class CleanOldDriverPositionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver-positions:clean
                            {--hours=24 : Remover posições mais antigas que N horas}
                            {--dry-run : Apenas exibir quantos registros seriam removidos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove posições de motoristas antigas (política de retenção). Padrão: 24 horas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $cutoff = now()->subHours($hours);
        $query = DriverPosition::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("Nenhuma posição com mais de {$hours}h encontrada.");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("[Dry-run] Seriam removidos {$count} registro(s) de posições com mais de {$hours}h.");
            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Removidos {$deleted} registro(s) de posições com mais de {$hours}h.");
        return self::SUCCESS;
    }
}
