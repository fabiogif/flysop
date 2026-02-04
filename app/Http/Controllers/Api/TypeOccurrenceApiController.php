<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeOccurrenceResource;
use App\Services\TypeOccurrenceService;
use Illuminate\Http\Request;

class TypeOccurrenceApiController extends Controller
{

    protected $typeOccurrenceService;

    public function __construct(TypeOccurrenceService $typeOccurrenceService)
    {
        $this->typeOccurrenceService = $typeOccurrenceService;
    }

    public function index(Request $request)
    {
        $pre_page = (int)$request->get('pre_page', 15);

        $typeOccurrence = $this->typeOccurrenceService->getAllTypeOccurrence($pre_page);

        return TypeOccurrenceResource::collection($typeOccurrence);
    }

}
