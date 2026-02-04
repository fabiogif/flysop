<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'occurrence_id' => ['nullable', 'integer', 'exists:occurrences,id'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude é obrigatória.',
            'longitude.required' => 'Longitude é obrigatória.',
        ];
    }
}
