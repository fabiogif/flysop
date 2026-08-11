<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Limpa posições de motoristas com mais de 24h (política de retenção da rota em tempo real)
        $schedule->command('driver-positions:clean --hours=24')->daily()->at('03:00');

        // Notifica Administrador/Supervisor sobre ocorrências com SLA a até 2h do vencimento (Fase 3)
        $schedule->command('occurrences:check-sla --hours=2')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
