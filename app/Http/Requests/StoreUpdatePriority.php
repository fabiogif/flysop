<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdatePriority extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->method() === 'PUT' ? $this->segment(3) : 0;

        return [
            'name' => "required|min:2|max:255|unique:priorities,name,{$id},id",
            'weight' => 'required|integer|min:0|max:65535',
            'color' => 'nullable|string|max:20',
            'default_sla_hours' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.unique' => 'Já existe uma prioridade com esse nome.',
            'weight.required' => 'O peso é obrigatório (usado para ordenar as prioridades).',
        ];
    }
}
