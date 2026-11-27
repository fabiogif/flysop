<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Tenant;

class SiteController extends Controller
{
    /**
     * Página inicial do site (landing).
     * Planos foram removidos; foco em login/cadastro e informações do sistema.
     */
    public function index()
    {
        // Site público tem um tenant só (mesmo default de Api\SurveyApiController)
        $tenantId = Tenant::query()->value('id') ?? 1;

        return view('site.pages.home.index', [
            'announcements' => Announcement::publishedFor($tenantId)->with('issuing')->limit(6)->get(),
        ]);
    }
}
