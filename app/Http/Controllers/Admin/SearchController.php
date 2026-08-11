<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occurrences;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Busca global (Fase 4): full-text nativo do Postgres (tsvector/GIN) sobre
     * protocolo/título/nome/descrição de ocorrências.
     */
    public function index(Request $request): View
    {
        $term = trim((string) $request->get('q', ''));

        $results = $term !== ''
            ? Occurrences::with(['statusOccurence', 'typeOccurrence'])
                ->fullTextSearch($term)
                ->paginate(15)
                ->withQueryString()
            : null;

        return view('admin.pages.search.index', ['term' => $term, 'results' => $results]);
    }
}
