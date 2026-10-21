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
            // Opcional: quem registra sem conta não informa e-mail.
            'email' => "nullable|email|max:255",
            // type_occurrences_id é obrigatório. issuings_id é opcional na API pública (app/cidadão)
            // e recebe fallback para o órgão padrão no controller/service se omitido.
            'type_occurrences_id' => 'required|exists:type_occurrences,id',
            'issuings_id' => 'nullable|exists:issuings,id',
            // Geolocalização e endereço
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:5000',
            // Anexos de fotos
            'anexo' => 'nullable|array',
            'anexo.*' => 'nullable|image|max:5120',
            // Consentimento explícito (LGPD, Fase 6): o cliente que consome a API pública
            // precisa confirmar que o cidadão foi informado e concordou com o tratamento
            // dos dados pessoais enviados nesta ocorrência.
            'lgpd_consent' => 'required|accepted',
            // Ocorrência enviada como anônima (app/site público) — ver ClientService::anonymousClient().
            'is_anonymous' => 'nullable|boolean',
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
