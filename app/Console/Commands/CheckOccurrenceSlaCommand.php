<?php

namespace App\Console\Commands;

use App\Models\Occurrences;
use App\Models\Role;
use App\Notifications\SlaAtRiskNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOccurrenceSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'occurrences:check-sla
                            {--hours=2 : Avisar quando o prazo (due_at) estiver a N horas de vencer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica Administrador/Supervisor sobre ocorrências com SLA próximo do vencimento.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $limit = now()->addHours($hours);

        $occurrences = Occurrences::query()
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $limit)
            ->where('due_at', '>=', now())
            ->whereHas('statusOccurence', function ($query) {
                $query->where('is_terminal', false);
            })
            ->get();

        if ($occurrences->isEmpty()) {
            $this->info('Nenhuma ocorrência com SLA próximo do vencimento.');

            return self::SUCCESS;
        }

        $notifiable = $this->staffToNotify();
        $notified = 0;

        foreach ($occurrences as $occurrence) {
            if ($this->alreadyNotified($occurrence)) {
                continue;
            }

            foreach ($notifiable as $user) {
                $user->notify(new SlaAtRiskNotification($occurrence));
            }

            $notified++;
        }

        $this->info("{$notified} ocorrência(s) notificada(s) para " . $notifiable->count() . ' usuário(s) (Administrador/Supervisor).');

        return self::SUCCESS;
    }

    /**
     * Evita notificar a mesma ocorrência repetidamente a cada execução do agendador
     * (roda de hora em hora — ver app/Console/Kernel.php): só notifica de novo se não
     * houver um SlaAtRiskNotification para essa ocorrência nas últimas 24h.
     */
    private function alreadyNotified(Occurrences $occurrence): bool
    {
        return DB::table('notifications')
            ->where('type', SlaAtRiskNotification::class)
            ->where('data', 'like', '%"occurrence_id":' . $occurrence->id . ',%')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }

    private function staffToNotify()
    {
        return Role::whereIn('name', ['Administrador', 'Supervisor'])
            ->with('users')
            ->get()
            ->pluck('users')
            ->flatten()
            ->unique('id');
    }
}
