<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SurveyQuestionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'prompt' => $this->prompt,
            'options' => $this->options,
            'required' => $this->required,
            'sort_order' => $this->sort_order,
        ];
    }
}
