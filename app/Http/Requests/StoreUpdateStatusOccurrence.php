<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateStatusOccurrence extends FormRequest
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
        }
        else {
            $id = 0;
        }
        return [
            'name' => "required|min:3|max:255|unique:profiles,name,{$id},id",
        ];
    }
}
