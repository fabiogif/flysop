<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateProduct extends FormRequest
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
            'title' => ['required', 'string', 'min:3', 'max:255', "unique:products,title,{$id},id"],
            'description' => ['required', 'string', 'min:3', 'max:300'],
            'image' => ['required', 'image'],
            'flag' => ['required', 'string', 'min:3', 'max:255', "unique:products,flag,{$id},id"],
            'price' => "required|regex:/^\d+(\.\d{1,2})?$/",
        ];
        if ($this->method() == 'PUT') {
            $rules['image'] = ['nullable', 'image'];
        }

        return  $rules;
    }
}
