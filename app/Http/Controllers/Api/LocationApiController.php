<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Support\Facades\Cache;

/**
 * Estados e municípios (IBGE) para os campos UF/cidade da ocorrência no web e no app.
 * Dado público de referência (sem PII), só leitura e imutável na prática: cache sem
 * expiração — o seeder é a única escrita, então limpe o cache se rodar o seed de novo.
 */
class LocationApiController extends Controller
{
    public function states()
    {
        return response()->json(['data' => Cache::rememberForever('locations.states', fn () => State::orderBy('name')->get(['uf', 'name']))]);
    }

    public function cities(string $uf)
    {
        $uf = strtoupper($uf);
        $cities = Cache::rememberForever("locations.cities.{$uf}", fn () => City::query()
            ->whereHas('state', fn ($q) => $q->where('uf', $uf))
            ->orderBy('name')
            ->get(['id', 'name']));

        return response()->json(['data' => $cities]);
    }
}
