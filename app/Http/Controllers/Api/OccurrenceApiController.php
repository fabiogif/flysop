<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUpdateOccurrences;
use App\Http\Resources\OccurrenceResource;
use App\Mail\MailNotify;
use App\Services\OccurrenceService;
use Illuminate\Http\Request;
use App\Models\OccurrencesImagens;
use App\Models\Occurrences;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OccurrenceApiController extends Controller
{
    protected $occurrenceService, $occurrenceImage, $occurrences;

    public function __construct(OccurrenceService $occurrenceService, OccurrencesImagens $occurrencesImage, Occurrences $occurrences)
    {
        $this->occurrenceService = $occurrenceService;
        $this->occurrenceImage = $occurrencesImage;
        $this->occurrences = $occurrences;

    }

    public function index(Request $request)
    {
        $pre_page = (int) $request->get('pre_page', 15);
        $occurrence = $this->occurrenceService->getAllOccurrences($pre_page);


        return OccurrenceResource::collection($occurrence);
    }

    public function show($id)
    {

        $occurrence = $this->occurrenceService->getOccurrenceById($id);
        $occurrencesImagens = $this->occurrenceImage->where('occurrence_id', $id)->get();

        $occurrence->anexo = $occurrencesImagens;

        if (!$occurrence) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return $occurrence;
    }


    public function getOccurrenceByClientId($clientId)
    {
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

        $data = $request->all();

        $occurrence = $this->occurrenceService->createNewOccurrence($data);

        if (!$occurrence) {
            return response()->json(['message', 'Ocorrência não cadastrada'], 404);
        }

        if ($request->hasFile('anexo')) {
            $imagem = array();

            foreach ($request->allFiles()['anexo'] as $anexo) {

                Storage::put('occurrence/occurrences', file_get_contents($anexo));

                $data['url'] = $anexo->store("occurrence/occurrences");
                $data['occurrence_id'] = $occurrence->id;

                $occurrence->imagens()->create($data);
                $imagem[] = $data['url'];
            }
            $occurrence->anexo = $imagem;
        }

        //$this->sendMail($occurrence);

        return new OccurrenceResource($occurrence);

    }

    public function sendMail($occurrence)
    {

        $data = ['subject' => $occurrence->title, 'body' => $occurrence];
        $envioMail = new MailController();
        $envioMail->sendMail($data);
        //Mail::to($occurrence->email)->send(new MailNotify($data));


    }

}