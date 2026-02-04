<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occurrences;
use App\Models\OccurrencesImagens;
use Illuminate\Http\Request;

class OccurrencesImagensController extends Controller
{
    public function __construct(OccurrencesImagens $occurrencesImagens, Occurrences $occurrences)
    {
        $this->repository = $occurrencesImagens;
        $this->occurrences = $occurrences;
    //$this->middleware(['can:plans']);
    }

    public function store($request)
    {
        return $this->repository->create($request->all());
    }

    public function occurrenceImages()
    {
        return $this->hasMany(OccurrencesImagens::class);
    }

}
