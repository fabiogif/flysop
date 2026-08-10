<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateDepartment extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->method() === 'PUT' ? $this->segment(3) : 0;

        return [
            'name' => "required|min:2|max:255|unique:departments,name,{$id},id",
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.unique' => 'Já existe um departamento com esse nome.',
        ];
    }
}
