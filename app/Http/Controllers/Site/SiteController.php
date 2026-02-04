<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class SiteController extends Controller
{
    /**
     * Página inicial do site (landing).
     * Planos foram removidos; foco em login/cadastro e informações do sistema.
     */
    public function index()
    {
        return view('site.pages.home.index');
    }
}
