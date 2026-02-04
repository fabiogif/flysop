<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Occurrences;
use App\Services\OccurrenceService;
use Illuminate\View\View;

class DriverDashboardController extends Controller
{
    public function __construct(
        protected OccurrenceService $occurrenceService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $driver = $user->driver;

        if ($isAdmin) {
            $occurrences = Occurrences::with(['statusOccurence', 'typeOccurrence', 'driver'])
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $aguardando = Occurrences::whereHas('statusOccurence', fn ($q) => $q->where('name', 'Aguardando aceitação'))
                ->count();

            $emAndamento = Occurrences::whereHas('statusOccurence', function ($q) {
                $q->whereIn('name', ['Aceita', 'Em deslocamento', 'Em atendimento']);
            })->count();
        } else {
            if (! $driver) {
                abort(403, 'Usuário não vinculado a um motorista.');
            }

            $occurrences = $this->occurrenceService->getOccurrencesForDriver(
                $driver->id,
                null,
                15
            );

            $aguardando = Occurrences::forDriver($driver->id)
                ->whereHas('statusOccurence', fn ($q) => $q->where('name', 'Aguardando aceitação'))
                ->count();

            $emAndamento = Occurrences::forDriver($driver->id)
                ->whereHas('statusOccurence', function ($q) {
                    $q->whereIn('name', ['Aceita', 'Em deslocamento', 'Em atendimento']);
                })
                ->count();
        }

        return view('driver.pages.dashboard.index', [
            'occurrences' => $occurrences,
            'aguardando' => $aguardando,
            'emAndamento' => $emAndamento,
        ]);
    }
}
