<?php

namespace App\Services\Contracts;

interface DashboardServiceInterface
{
    /**
     * Retorna estatísticas do dashboard do tenant autenticado.
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Dados agregados para os gráficos do dashboard (Fase 4): ocorrências por dia,
     * por status e por prioridade.
     */
    public function getChartsData(): array;
}
