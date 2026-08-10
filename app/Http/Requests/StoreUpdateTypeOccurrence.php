<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTypeOccurrence extends FormRequest
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
        return [
            'name' => "required|min:3|max:255|unique:type_occurrences,name,{$id},id",
            'sla_hours' => 'nullable|integer|min:1',
            'parent_id' => "nullable|integer|exists:type_occurrences,id|different:{$id}",
        ];
    }
}
