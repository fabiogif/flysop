<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyResponse;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicSurveyController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {
    }

    // Cookie (criptografado pelo EncryptCookies) que identifica o navegador: uma resposta por pesquisa
    private const RESPONDENT_COOKIE = 'survey_respondent';

    public function show(Request $request, string $token): View
    {
        $survey = $this->surveyService->findByToken($token);

        if (!$survey) {
            abort(404);
        }

        if (!$survey->is_active) {
            return view('public.surveys.closed', compact('survey'));
        }

        if ($this->surveyService->hasResponded($survey, $this->respondentKey($request))) {
            return view('public.surveys.thanks', ['survey' => $survey, 'alreadyAnswered' => true]);
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

        $browserId = $request->cookie(self::RESPONDENT_COOKIE) ?: (string) Str::uuid();

        $this->surveyService->submitResponse($survey, $request->input('answers', []), [
            'ip' => $request->ip(),
            'justifications' => $request->input('justifications', []),
            'respondent_name' => $request->input('respondent_name'),
            'respondent_key' => $this->surveyService->respondentKey("web:{$browserId}"),
        ]);

        return redirect()
            ->route('public.surveys.thanks', $token)
            ->withCookie(Cookie::forever(self::RESPONDENT_COOKIE, $browserId));
    }

    public function thanks(string $token): View
    {
        $survey = $this->surveyService->findByToken($token);

        if (!$survey) {
            abort(404);
        }

        return view('public.surveys.thanks', compact('survey'));
    }

    private function respondentKey(Request $request): ?string
    {
        $browserId = $request->cookie(self::RESPONDENT_COOKIE);

        return $browserId ? $this->surveyService->respondentKey("web:{$browserId}") : null;
    }
}
