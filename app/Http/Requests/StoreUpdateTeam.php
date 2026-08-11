<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTeam extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->method() === 'PUT' ? $this->segment(3) : 0;

        return [
            'name' => "required|min:2|max:255|unique:teams,name,{$id},id",
            'department_id' => 'nullable|exists:departments,id',
            'type_occurrences_id' => 'nullable|exists:type_occurrences,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.unique' => 'Já existe uma equipe com esse nome.',
        ];
    }
}
