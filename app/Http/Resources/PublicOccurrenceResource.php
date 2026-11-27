<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ocorrência vista por um cidadão que não é o autor (mapa do app): só o problema e onde ele
 * está — sem nome, CPF, RG, e-mail, telefone, autor ou descrição livre (LGPD).
 */
class PublicOccurrenceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'protocol' => $this->protocol,
            'title' => $this->title,
            'type_occurrences' => $this->type_occurrences_id,
            'nameType' => $this->nameType ?? $this->typeOccurrence?->name ?? '',
            'status_occurrences_id' => $this->status_occurrences_id,
            'nameStatus' => $this->nameStatus ?? $this->statusOccurence?->name ?? '',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y'),
        ];
    }
}
