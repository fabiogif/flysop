<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateUsers extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->method() == 'PUT') {
            $id = $this->segment(3);
        } else {
            $id = 0;
        }
        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'min:3', 'max:255', "unique:users,email,{$id},id"],
            'password' => ['required', 'string', 'min:3', 'max:16'],
        ];
        if ($this->method() == 'PUT') {
            $rules['password'] = ['nullable', 'string', 'min:3', 'max:16'];
        }

        return $rules;
    }
}
