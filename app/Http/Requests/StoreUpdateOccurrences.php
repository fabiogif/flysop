<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateOccurrences extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status_occurrences_id' => ['required', 'exists:status_occurrences,id'],
            'type_occurrences_id' => ['required', 'exists:type_occurrences,id'],
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'issuings_id' => ['required', 'exists:issuings,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'clients_id' => ['nullable', 'exists:clients,id'],
            'anexo' => ['nullable', 'array'],
            'anexo.*' => ['nullable', 'file', 'image', 'max:5120'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'name.required' => 'O nome completo é obrigatório.',
            'description.required' => 'A descrição é obrigatória.',
            'address.required' => 'O endereço é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'phone.required' => 'O telefone é obrigatório.',
            'status_occurrences_id.required' => 'Selecione o status da ocorrência.',
            'status_occurrences_id.exists' => 'Status inválido.',
            'type_occurrences_id.required' => 'Selecione o tipo de ocorrência.',
            'type_occurrences_id.exists' => 'Tipo inválido.',
            'issuings_id.required' => 'Selecione o órgão.',
            'issuings_id.exists' => 'Órgão inválido.',
            'anexo.*.image' => 'Os anexos devem ser imagens.',
            'anexo.*.max' => 'Cada anexo deve ter no máximo 5 MB.',
        ];
    }
}
