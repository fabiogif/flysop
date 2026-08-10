<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Occurrences;
use App\Services\OccurrenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverOccurrenceController extends Controller
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
        } else {
            if (! $driver) {
                abort(403, 'Usuário não vinculado a um motorista.');
            }
            $occurrences = $this->occurrenceService->getOccurrencesForDriver($driver->id, null, 15);
        }

        return view('driver.pages.occurrences.index', ['occurrences' => $occurrences]);
    }

    public function show(int $id): View|RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $driver = $user->driver;

        if (! $isAdmin && ! $driver) {
            abort(403, 'Usuário não vinculado a um motorista.');
        }

        $occurrence = $this->occurrenceService->findOrFail($id);

        if (! $isAdmin && (int) $occurrence->driver_id !== $driver->id) {
            abort(403, 'Esta ocorrência não está atribuída a você.');
        }

        $occurrence->load(['statusOccurence', 'typeOccurrence', 'imagens']);
        $formData = $this->occurrenceService->getFormData();

        return view('driver.pages.occurrences.show', [
            'occurrence' => $occurrence,
            'statusOccurrences' => $formData['statusOccurrences'],
            'isAdmin' => $isAdmin,
        ]);
    }

    public function accept(int $id): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $driver = $user->driver;

        if (! $isAdmin && ! $driver) {
            abort(403, 'Usuário não vinculado a um motorista.');
        }

        if ($isAdmin) {
            $occurrence = $this->occurrenceService->findOrFail($id);
            if (! $occurrence->driver_id) {
                return redirect()
                    ->route('driver.occurrences.show', $id)
                    ->with('messageDanger', 'Esta ocorrência não está atribuída a um motorista.');
            }
            $driverId = $occurrence->driver_id;
        } else {
            $driverId = $driver->id;
        }

        $this->occurrenceService->acceptOccurrence($id, $driverId);

        return redirect()
            ->route('driver.occurrences.show', $id)
            ->with('messageSuccess', 'Ocorrência aceita com sucesso.');
    }

    public function reject(int $id): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $driver = $user->driver;

        if (! $isAdmin && ! $driver) {
            abort(403, 'Usuário não vinculado a um motorista.');
        }

        if ($isAdmin) {
            $occurrence = $this->occurrenceService->findOrFail($id);
            if (! $occurrence->driver_id) {
                return redirect()
                    ->route('driver.occurrences.show', $id)
                    ->with('messageDanger', 'Esta ocorrência não está atribuída a um motorista.');
            }
            $driverId = $occurrence->driver_id;
        } else {
            $driverId = $driver->id;
        }

        $this->occurrenceService->rejectOccurrence($id, $driverId);

        return redirect()
            ->route('driver.dashboard')
            ->with('messageSuccess', 'Ocorrência recusada.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $driver = $user->driver;

        if (! $isAdmin && ! $driver) {
            abort(403, 'Usuário não vinculado a um motorista.');
        }

        $request->validate([
            'status_occurrences_id' => 'required|integer|exists:status_occurrences,id',
        ]);

        if ($isAdmin) {
            $this->occurrenceService->updateStatusByAdmin($id, (int) $request->status_occurrences_id);
        } else {
            $this->occurrenceService->updateStatusByDriver(
                $id,
                $driver->id,
                (int) $request->status_occurrences_id
            );
        }

        return redirect()
            ->route('driver.occurrences.show', $id)
            ->with('messageSuccess', 'Status atualizado.');
    }
}
