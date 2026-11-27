<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateAnnouncement extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }

    public function rules(): array
    {
        $user = $this->user();
        // Gestor (não admin) publica sempre para o próprio órgão: o controller fixa issuing_id
        $chooseIssuing = ! ($user && $user->isManager() && ! $user->isAdmin());

        return [
            'issuing_id' => [Rule::requiredIf($chooseIssuing), 'nullable', 'integer', 'exists:issuings,id'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:3', 'max:5000'],
            'is_published' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'issuing_id.required' => 'Selecione o órgão do informativo.',
            'issuing_id.exists' => 'Órgão inválido.',
            'title.required' => 'Informe o título.',
            'title.max' => 'O título pode ter no máximo 120 caracteres.',
            'body.required' => 'Informe o texto do informativo.',
            'body.max' => 'O texto pode ter no máximo 5000 caracteres.',
        ];
    }
}
