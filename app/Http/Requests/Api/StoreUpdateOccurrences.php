<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateOccurrences extends FormRequest
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
        return [
            'title' => "required|max:255",
            'email' => "required|max:255",
            // type_occurrences_id/issuings_id são NOT NULL no banco e sempre foram exigidos
            // pelo formulário admin; a API pública nunca validava isso e toda submissão sem
            // esses campos derrubava com erro 500 (bug pré-existente, corrigido junto da LGPD).
            'type_occurrences_id' => 'required|exists:type_occurrences,id',
            'issuings_id' => 'required|exists:issuings,id',
            // Consentimento explícito (LGPD, Fase 6): o cliente que consome a API pública
            // precisa confirmar que o cidadão foi informado e concordou com o tratamento
            // dos dados pessoais enviados nesta ocorrência.
            'lgpd_consent' => 'required|accepted',
        ];
    }

    public function messages()
    {
        return [
            'lgpd_consent.required' => 'É necessário confirmar o consentimento (LGPD) para o tratamento dos dados pessoais.',
            'lgpd_consent.accepted' => 'É necessário confirmar o consentimento (LGPD) para o tratamento dos dados pessoais.',
        ];
    }
}
