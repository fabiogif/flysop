<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitizenRegister extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/'],
            'phone' => ['nullable', 'string', 'min:8', 'max:20'],
            'terms_accepted' => ['required', 'accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.regex' => 'A senha deve conter pelo menos uma letra e um número.',
            'terms_accepted.required' => 'É necessário aceitar os termos de uso e privacidade.',
            'terms_accepted.accepted' => 'É necessário aceitar os termos de uso e privacidade.',
        ];
    }
}
