<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitizenAuth extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Login leve do cidadão (app/site público): nome + celular obrigatórios, sem senha.
     * E-mail é opcional.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|max:60',
            'phone' => 'required|min:8|max:20',
            'email' => 'nullable|email|max:60',
        ];
    }
}
