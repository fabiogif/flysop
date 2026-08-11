<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyResponse;
use App\Services\SurveyService;
use Illuminate\View\View;

class PublicSurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {
    }

    public function show(string $token): View
    {
        $survey = $this->surveyService->findByToken($token);

        if (!$survey) {
            abort(404);
        }

        if (!$survey->is_active) {
            return view('public.surveys.closed', compact('survey'));
        }

        return view('public.surveys.show', compact('survey'));
    }

    public function store(StoreSurveyResponse $request, string $token)
    {
        $survey = $request->survey();

        if (!$survey) {
            abort(404);
        }

        if (!$survey->is_active) {
            return redirect()
                ->route('public.surveys.show', $token)
                ->with('error', 'Esta pesquisa está encerrada.');
        }

        $this->surveyService->submitResponse(
            $survey,
            $request->input('answers', []),
            $request->ip()
        );

        return redirect()
            ->route('public.surveys.thanks', $token);
    }

    public function thanks(string $token): View
    {
        $survey = $this->surveyService->findByToken($token);

        if (!$survey) {
            abort(404);
        }

        return view('public.surveys.thanks', compact('survey'));
    }
}
