<?php

namespace App\Policies;

use App\Models\Occurrences;
use App\Models\User;

/**
 * Autorização por ocorrência. Administrador tem acesso irrestrito via Gate::before
 * (app/Providers/AuthServiceProvider.php) e nunca passa por estas checagens.
 */
class OccurrencePolicy
{
    /**
     * Papéis com acesso ao módulo de ocorrências no admin (Secretaria, Operador, Atendente,
     * Supervisor, Motorista) — hoje todos compartilham a permissão 'occurrences'.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('occurrences') || $user->driver !== null || $user->isManager();
    }

    public function view(User $user, Occurrences $occurrence): bool
    {
        // Gestor de órgão: só as ocorrências do próprio órgão, mesmo tendo a permissão do módulo.
        if ($user->isManager()) {
            return $this->belongsToManagerIssuing($user, $occurrence);
        }

        if ($user->hasPermission('occurrences')) {
            return true;
        }

        return $user->driver && (int) $occurrence->driver_id === (int) $user->driver->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('occurrences');
    }

    public function update(User $user, Occurrences $occurrence): bool
    {
        if ($user->isManager()) {
            return $this->belongsToManagerIssuing($user, $occurrence);
        }

        return $user->hasPermission('occurrences');
    }

    private function belongsToManagerIssuing(User $user, Occurrences $occurrence): bool
    {
        return (int) $occurrence->issuings_id === (int) $user->issuing_id;
    }

    /**
     * Exclusão é destrutiva (sem histórico de recuperação) — restrita a quem coordena
     * o atendimento, não a todo mundo com acesso de leitura/edição ao módulo.
     */
    public function delete(User $user, Occurrences $occurrence): bool
    {
        return $user->hasRole('Supervisor');
    }

    /**
     * Reabrir ocorrência finalizada/cancelada — mesma régua da exclusão (Fase 3 vai
     * formalizar isso como transição de status; por ora é só a checagem de autorização).
     */
    public function reopen(User $user, Occurrences $occurrence): bool
    {
        return $user->hasRole('Supervisor');
    }

    /**
     * Direito ao esquecimento (LGPD, Fase 6) — irreversível, mesma régua da exclusão.
     */
    public function forget(User $user, Occurrences $occurrence): bool
    {
        return $user->hasRole('Supervisor');
    }
}
