<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Estritamente campos públicos; nunca expõe phone, email, cpf, rg, password ou tokens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'website' => $this->website,
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
            'occurrences_count' => $this->relationLoaded('occurrences')
                ? $this->occurrences->count()
                : $this->occurrences()->count(),
        ];
    }
}
