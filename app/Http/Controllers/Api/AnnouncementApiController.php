<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    /**
     * Informativos publicados para a página inicial do app (pública: o app também é usado sem
     * conta). Filtro opcional por órgão. Mesmo default de tenant único de SurveyApiController.
     */
    public function index(Request $request)
    {
        $tenantId = Tenant::query()->value('id') ?? 1;

        $announcements = Announcement::publishedFor($tenantId, (int) $request->query('issuing_id') ?: null)
            ->with('issuing')
            ->paginate(10);

        return AnnouncementResource::collection($announcements);
    }
}
