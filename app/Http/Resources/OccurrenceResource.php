<?php

namespace App\Http\Resources;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OccurrenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Sem anexo devolve [] (antes era [""], que o app tentava renderizar como imagem)
        $anexo = collect((array) $this->anexo)
            ->filter()
            ->map(fn ($item) => 'https://sopanexos.s3.amazonaws.com/' . $item)
            ->values();

        // Anônima: só o próprio autor vê os dados de identificação; o órgão não
        $citizen = $request->user('sanctum');
        $hideIdentity = $this->is_anonymous
            && ! ($citizen instanceof Client && (int) $citizen->id === (int) $this->clients_id);

        return [
            'id' => $this->id,
            'protocol' => $this->protocol,
            'name' => $hideIdentity ? null : $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'cpf' => $hideIdentity ? null : $this->cpf,
            'rg' => $hideIdentity ? null : $this->rg,
            'email' => $hideIdentity ? null : $this->email,
            'is_anonymous' => (bool) $this->is_anonymous,
            'address' => $this->address,
            'issuings_id' => $this->issuings_id,
            'user_id' => $this->user_id,
            'type_occurrences' => $this->type_occurrences_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status_occurrences_id' => $this->status_occurrences_id,
            // nameType/nameStatus vêm do join da listagem; no detalhe, das relações
            'nameType' => $this->nameType ?: ($this->typeOccurrence?->name ?? ''),
            'nameStatus' => $this->nameStatus ?: ($this->statusOccurence?->name ?? ''),
            'anexo' => $anexo,
            'clients_id' => $hideIdentity ? null : $this->clients_id,
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y')
        ];
    }
}