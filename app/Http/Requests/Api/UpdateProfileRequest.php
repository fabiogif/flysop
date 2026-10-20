<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->user()?->id;
        $tenantId = $this->user()?->tenant_id;

        return [
            // Mesmos campos editáveis do usuário no web (nome e e-mail)
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($clientId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
        ];
    }
}
