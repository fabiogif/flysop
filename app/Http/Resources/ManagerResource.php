<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Gestor de órgão autenticado no app. "role" diferencia do cidadão (ClientResource) no /auth/me.
 */
class ManagerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => 'manager',
            'issuing' => $this->issuing ? ['id' => $this->issuing->id, 'name' => $this->issuing->name] : null,
            'notify_new_occurrences' => $this->notify_new_occurrences,
        ];
    }
}
