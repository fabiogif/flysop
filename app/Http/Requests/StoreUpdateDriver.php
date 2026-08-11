<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateDriver extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('driver')?->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'status' => ['required', 'in:disponivel,em_deslocamento,em_atendimento'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do motorista é obrigatório.',
            'status.required' => 'Selecione o status.',
            'status.in' => 'Status inválido.',
        ];
    }
}
