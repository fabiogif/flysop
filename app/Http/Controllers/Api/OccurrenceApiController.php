<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUpdateOccurrences;
use App\Http\Resources\OccurrenceResource;
use App\Http\Resources\PublicOccurrenceResource;
use App\Mail\MailNotify;
use App\Models\Client;
use App\Models\Issuing;
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
     * Mapa do app: a ocorrência só aparece para quem criou (e para o gestor do órgão, pelas rotas
     * /manager). Cidadão logado vê as dele; sem cadastro (ou outro tipo de token), lista vazia.
     */
    public function index(Request $request)
    {
        $client = $request->user('sanctum');
        if (! $client instanceof Client) {
            return PublicOccurrenceResource::collection(collect());
        }

        $occurrences = $this->occurrences
            ->where('clients_id', $client->id)
            ->with(['typeOccurrence', 'statusOccurence'])
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('pre_page', 15));

        return PublicOccurrenceResource::collection($occurrences);
    }

    /**
     * Página inicial do app: totais abertas/em andamento/resolvidas, opcionalmente por órgão, e a
     * lista de órgãos para o filtro. Pública (app sem conta) porque são só números agregados.
     */
    public function stats(Request $request)
    {
        $issuingId = (int) $request->query('issuing_id') ?: null;

        return response()->json([
            'data' => $this->occurrenceService->publicStats($issuingId),
            'issuings' => Issuing::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Detalhe só para o autor; para os demais 404 (nem confirma que a ocorrência existe).
     */
    public function show(Request $request, $id)
    {
        $occurrence = $this->occurrenceService->getOccurrenceById($id);

        if (! $occurrence || ! $this->isAuthor($request, $occurrence)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $occurrence->anexo = $this->occurrenceImage->where('occurrence_id', $id)->pluck('url')->all();

        return new OccurrenceResource($occurrence);
    }


    /**
     * Acompanhamento por protocolo: exige ser o autor logado ou o código de acompanhamento
     * (header X-Tracking-Code) devolvido no cadastro sem conta — o protocolo sozinho é sequencial
     * e permitiria ver ocorrências de terceiros.
     */
    public function showByProtocol(Request $request, string $protocol)
    {
        $occurrence = $this->occurrences->where('protocol', $protocol)->with(['typeOccurrence', 'statusOccurence'])->first();

        $code = strtoupper(trim((string) $request->header('X-Tracking-Code')));
        $hasCode = $occurrence && $occurrence->tracking_code_hash && $code !== ''
            && hash_equals($occurrence->tracking_code_hash, hash('sha256', $code));

        if (! $occurrence || (! $hasCode && ! $this->isAuthor($request, $occurrence))) {
            return response()->json(['message' => 'Protocolo ou código de acompanhamento não encontrado.'], 404);
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
            'priority_id', 'due_at', 'possible_duplicate_of_id', 'tracking_code_hash',
        ]);

        // Autor = cidadão do token (mesmo anônimo: ele acompanha, o órgão não vê quem é).
        // Token de gestor (User) não vira autor: o id dele não é um clients_id.
        // Sem conta, o autor se prova pelo código de acompanhamento, devolvido só aqui.
        $trackingCode = null;
        $citizen = $request->user('sanctum');
        if ($citizen instanceof Client) {
            $data['clients_id'] = $citizen->id;
        } else {
            $trackingCode = $this->newTrackingCode();
            $data['tracking_code_hash'] = hash('sha256', $trackingCode);
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

        return (new OccurrenceResource($occurrence))
            ->additional($trackingCode ? ['tracking_code' => $trackingCode] : [])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 8 caracteres sem os ambíguos (0/O, 1/I/L), no formato XXXX-XXXX: fácil de anotar.
     */
    private function newTrackingCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return substr($code, 0, 4) . '-' . substr($code, 4);
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