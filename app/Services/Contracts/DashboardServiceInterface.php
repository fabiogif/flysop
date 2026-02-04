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
}
