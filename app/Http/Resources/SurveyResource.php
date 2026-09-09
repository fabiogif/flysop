<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'public_token' => $this->public_token,
            'is_active' => $this->is_active,
            'questions' => SurveyQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
