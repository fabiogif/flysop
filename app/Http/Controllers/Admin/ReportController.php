<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use App\Models\StatusOccurrence;
use App\Models\TypeOccurrence;
use App\Models\Priority;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = Report::where('user_id', auth()->id())->latest()->paginate(15);

        $filterOptions = [
            'statusOccurrences' => StatusOccurrence::orderBy('sort_order')->get(),
            'typeOccurrences' => TypeOccurrence::orderBy('name')->get(),
            'priorities' => Priority::orderBy('weight', 'desc')->get(),
            'drivers' => Driver::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(),
        ];

        return view('admin.pages.reports.index', [
            'reports' => $reports,
            'filterOptions' => $filterOptions,
            'typeLabels' => Report::typeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:' . Report::TYPE_OCCURRENCES . ',' . Report::TYPE_STATUS_DURATIONS,
        ]);

        $filters = $request->only([
            'status_occurrences_id', 'type_occurrences_id', 'priority_id', 'driver_id', 'date_from', 'date_to',
        ]);
        $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '');

        $report = Report::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'status' => Report::STATUS_PENDING,
            'filters' => $filters,
        ]);

        GenerateReportJob::dispatch($report->id);

        return redirect()->route('reports.index')
            ->with('messageSuccess', 'Relatório sendo gerado. Você será notificado quando estiver pronto.');
    }

    public function download(int $id): StreamedResponse|RedirectResponse
    {
        $report = Report::where('user_id', auth()->id())->find($id);

        if (! $report || $report->status !== Report::STATUS_READY || ! $report->file_path) {
            return redirect()->route('reports.index')->with('messageDanger', 'Relatório não encontrado ou ainda não está pronto.');
        }

        if (! Storage::disk('local')->exists($report->file_path)) {
            return redirect()->route('reports.index')->with('messageDanger', 'Arquivo do relatório não encontrado (pode ter expirado).');
        }

        return Storage::disk('local')->download($report->file_path);
    }
}
