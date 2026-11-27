<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'issuing' => $this->issuing ? ['id' => $this->issuing->id, 'name' => $this->issuing->name] : null,
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
