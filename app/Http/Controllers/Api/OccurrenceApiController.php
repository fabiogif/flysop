<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUpdateOccurrences;
use App\Http\Resources\OccurrenceResource;
use App\Http\Resources\PublicOccurrenceResource;
use App\Mail\MailNotify;
use App\Models\Client;
use App\Services\OccurrenceService;
use Illuminate\Http\Request;
use App\Models\OccurrencesImagens;
use App\Models\Occurrences;
use Illuminate\Support\Facades\Mail;

class OccurrenceApiController extends Controller
{
    protected $occurrenceService, $occurrenceImage, $occurrences;

    public function __construct(OccurrenceService $occurrenceService, OccurrencesImagens $occurrencesImage, Occurrences $occurrences)
    {
        $this->occurrenceService = $occurrenceService;
        $this->occurrenceImage = $occurrencesImage;
        $this->occurrences = $occurrences;

    }

    /**
     * Mapa do app: ocorrências de todos, sem dados pessoais (LGPD) — ver PublicOccurrenceResource.
     */
    public function index(Request $request)
    {
        $pre_page = (int) $request->get('pre_page', 15);
        $occurrence = $this->occurrenceService->getAllOccurrences($pre_page);

        return PublicOccurrenceResource::collection($occurrence);
    }

    /**
     * Detalhe: completo só para o autor; para os demais, a versão pública.
     */
    public function show(Request $request, $id)
    {
        $occurrence = $this->occurrenceService->getOccurrenceById($id);

        if (!$occurrence) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (! $this->isAuthor($request, $occurrence)) {
            return new PublicOccurrenceResource($occurrence);
        }

        $occurrence->anexo = $this->occurrenceImage->where('occurrence_id', $id)->pluck('url')->all();

        return new OccurrenceResource($occurrence);
    }


    /**
     * Consulta pública por protocolo (quem registrou sem cadastro acompanha por aqui).
     */
    public function showByProtocol(string $protocol)
    {
        $occurrence = $this->occurrences->where('protocol', $protocol)->with(['typeOccurrence', 'statusOccurence'])->first();

        if (! $occurrence) {
            return response()->json(['message' => 'Protocolo não encontrado.'], 404);
        }

        return new PublicOccurrenceResource($occurrence);
    }

    public function getOccurrenceByClientId(Request $request, $clientId)
    {
        $client = $request->user();
        if (! $client instanceof Client || (int) $client->id !== (int) $clientId) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $occurrence = $this->occurrenceService->getOccurrenceByClientId($clientId);

        if (!$occurrence) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return OccurrenceResource::collection($occurrence);

    }

    public function store(Request $request)
    {
        $occurrence = $this->occurrenceService->createNewOccurrence($request->all());

        return new OccurrenceResource($occurrence);
    }

    public function createNewOccurrence(StoreUpdateOccurrences $request)
    {
        // Rota pública: campos de autoria, fluxo e atribuição nunca vêm do payload (antes era
        // possível abrir ocorrência em nome de outro cidadão ou já "Finalizada"/atribuída).
        $data = $request->except([
            'clients_id', 'users_id', 'protocol', 'status_occurrences_id', 'driver_id',
            'priority_id', 'due_at', 'possible_duplicate_of_id',
        ]);

        // Autor = cidadão do token (mesmo anônimo: ele acompanha, o órgão não vê quem é).
        // Token de gestor (User) não vira autor: o id dele não é um clients_id.
        $citizen = $request->user('sanctum');
        if ($citizen instanceof Client) {
            $data['clients_id'] = $citizen->id;
        }

        // Órgão: OccurrenceService::createNewOccurrence() resolve pelo tipo da ocorrência. As fotos
        // vão pelo ponto único de evidência do Service (OccurrenceService::storeEvidence), na mesma
        // transação: antes o Storage::put duplicado aqui falhava ("Is a directory") depois da
        // ocorrência já gravada, deixando ocorrência sem foto e devolvendo erro 500 ao app.
        $anexos = $request->hasFile('anexo') ? (array) $request->file('anexo') : [];
        $occurrence = $this->occurrenceService->createNewOccurrence($data, $anexos);

        if (!$occurrence) {
            return response()->json(['message' => 'Ocorrência não cadastrada'], 404);
        }

        $occurrence->anexo = $occurrence->imagens()->pluck('url')->all();

        return (new OccurrenceResource($occurrence))->response()->setStatusCode(201);
    }

    /**
     * Ocorrências do cidadão autenticado (app/site público).
     */
    public function myOccurrences(Request $request)
    {
        $client = $request->user();
        $pre_page = (int) $request->get('pre_page', 15);

        $occurrences = $this->occurrences
            ->where('clients_id', $client->id)
            ->with(['statusOccurence', 'typeOccurrence', 'imagens'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate($pre_page);

        return OccurrenceResource::collection($occurrences);
    }

    private function isAuthor(Request $request, Occurrences $occurrence): bool
    {
        // Rota pública: o token (se vier) é resolvido explicitamente pelo guard sanctum
        $client = $request->user('sanctum');

        return $client instanceof Client && (int) $occurrence->clients_id === (int) $client->id;
    }

    public function sendMail($occurrence)
    {

        $data = ['subject' => $occurrence->title, 'body' => $occurrence];
        $envioMail = new MailController();
        $envioMail->sendMail($data);
        //Mail::to($occurrence->email)->send(new MailNotify($data));


    }

}