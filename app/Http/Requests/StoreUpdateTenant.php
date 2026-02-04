<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTenant extends FormRequest
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
            'name'  => ['required', 'string', 'min:3', 'max:255', "unique:tenants,name,{$id},id"],
            'email' => ['required', 'string', 'min:3', 'max:255', "unique:tenants,email,{$id},id"],
            'logo'  => ['required', 'image'],
            'active' => ['required', 'in:1,0'],
            'cnpj'  => ['required', "unique:tenants,cnpj,{$id},id"],

            'subscription' => ['nullable', 'date'],
            'expires_at' => ['required', 'date'],
            'subscription_id' => ['nullable', 'max:255'],
            'subscription_active' => ['nullable', 'boolean'],
            'subscription_suspended' => ['nullable', 'boolean'],
        ];

        if ($this->method() == 'PUT') {
            $rules['logo'] = ['nullable', 'image'];
        }

        return  $rules;
    }
}
